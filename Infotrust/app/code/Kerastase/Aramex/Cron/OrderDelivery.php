<?php

namespace Kerastase\Aramex\Cron;

use Exception;
use function GuzzleHttp\Promise\iter_for;

class OrderDelivery
{
    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;

    /**
     * @var \Kerastase\Aramex\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;

    private $soapOptions;

    const ORDER_STATUS_DELIVERED = 'delivered';
    const ORDER_STATUS_TO_BE_RETURNED = 'to_be_returned';
    const ORDER_STATUS_PROCESSING = 'processing';
    const ORDER_STATUS_SHIPPED ='shipped';
    const SHIPMENT_TRACKING_REQUEST = 'TrackShipments';
    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\OrderHistory
     */
    private $orderLog;

    public function __construct(
        \Kerastase\Aramex\Logger\Logger $logger,
        \Kerastase\Aramex\Helper\Data $helper,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Sales\Model\Order $order,
        \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog
    )
    {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->order = $order;
        $this->collectionFactory = $collectionFactory;
        $this->orderLog = $orderLog;
        $this->soapOptions = $this->getSoapOptions();

    }

    public function execute()
    {
        $this->logger->info('############################## START ORDER DELIVERY CRON ####################################');
        try {

            $this->callTrackShipments();

        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }
        $this->logger->info('############################## END ORDER DELIVERY CRON ####################################');
    }


    /**
     * @param $url
     */
    public function callTrackShipments()
    {
        $deliveredCodes = array('SH007', 'SH154', 'SH234', 'SH005', 'SH006', 'SH496');
        $toBeReturnedCodes = array('SH069', 'SH247', 'SH474', 'SH495');
        $shippedOrders = $this->getShippedOrder();
        $url = $this->helper->getShippingApiUrl();
        $api_username = $this->helper->getShippingApiUsername();
        $api_password = $this->helper->getShippingApiPassword();
        $api_account_number = $this->helper->getShippingApiAccountNumber();
        $api_account_pin = $this->helper->getShippingApiAccountPin();
        $api_account_entity = $this->helper->getShippingApiAccountEntity();

        //$response = false;
        try {

            $clientInfo = array("UserName" => $api_username,
                'Password' => $api_password,
                'Version' => 'V01',
                'AccountNumber' => $api_account_number,
                'AccountPin' => $api_account_pin,
                'AccountEntity' => $api_account_entity,
                'AccountCountryCode' => 'UA');

            $this->logger->info("ORDER DELIVERY CRON :: CALL SHIPMENT TRACKING API REQUEST", array("ClientInfo" => $clientInfo,'GetLastTrackingUpdateOnly'=>true));


            foreach ($shippedOrders as $order) {

                $order->save();
                $awb = $order->getData('shipment_awb');
                $this->logger->info("ORDER DELIVERY CRON :: SENT AWB TO SHIPMENT TRACKING API REQUEST", array($order->getData('shipment_awb')));
                if ($awb !== null) {

                    /* Call shipment Tracking API */
                    $response = $this->callShipmentService(
                        $url,
                        self::SHIPMENT_TRACKING_REQUEST,
                        array( "ShipmentTrackingRequest"  =>  array('ClientInfo' => $clientInfo,'Shipments'=>array($awb),'GetLastTrackingUpdateOnly'=>true))

                    );

                    $this->logger->info("ORDER DELIVERY CRON:: CALL SHIPMENT TRACKING API RESPONSE",array($response));

                    if($response != false)
                    {

                        $result = json_decode(json_encode($response), true);
                        if($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']){
                            $trackingResult = $result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'];
                            $this->logger->info("ORDER DELIVERY CRON::  CALL SHIPMENT TRACKING API RESPONSE",array($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']));
                            $this->logger->info("ORDER DELIVERY CRON::  Received Update Code ".$trackingResult["UpdateCode"]);

                        if (in_array($trackingResult["UpdateCode"], $deliveredCodes)) {

                            $order->setStatus(SELF::ORDER_STATUS_DELIVERED);

                        } else if (in_array($trackingResult["UpdateCode"], $toBeReturnedCodes)) {

                            // TO DO TO IMPLEMENT THIS AFTER RECEIVING DATA
                           $generatedSuccess = $this->helper->generateReturnXMLFiles($order);
                            if ($generatedSuccess) {
                                $order->setStatus(SELF::ORDER_STATUS_TO_BE_RETURNED);
                            }
                        }
                        $comment = $trackingResult['Comments'];
                        $order->addStatusHistoryComment($comment, false);
                        $order->save();
                        }
                    }else{
                        $comment = "Error occured when receiving response from Aramex shipment tracking Api";
                        $this->logger->info("ORDER DELIVERY CRON:: ERROR IN SHIPMENT TRACKING API RESPONSE",array($response));
                    }
                    $this->logger->info("ORDER DELIVERY CRON:: NEW ORDER STATUS".$order->getStatus());

                    $historyDate =    $historyDate = date('Y-m-d H:i:s');
                    $this->orderLog->addRecord($order->getData('entity_id'),$order->getData('status'),'callTrackShipments','order Delivery',$comment,$historyDate);

                }

            }
        } catch (\Exception $ex) {
            $this->logger->info($ex);
        }

    }

    public function callShipmentService($url, $function,$params){

        $response = false;
        try
        {
            $client = new \SoapClient($url,$this->soapOptions);
            $response = $client->__soapCall($function, $params);

        }
        catch
        (\SoapFault $fault)
        {
            $this->logger->info("SOAP Fault: (faultCode: {$fault->faultcode} and faultString: {$fault->faultstring})");
        }
        catch(\Exception $e)
        {
            $this->logger->error($e->getMessage());
        }
        return $response;
        }

    /**
     * @param $awb
     * @return array
     */
    public function getSoapOptions()
    {
        $context = stream_context_create(array(
            'ssl' => array(
                'verify_peer' => false,
                'allow_self_signed' => true
            )
        ));
        $soapclient_options = array();
        $soapclient_options['trace'] = true;
        $soapclient_options['exception'] = true;
        $soapclient_options['stream_context'] = $context;

        return $soapclient_options;

    }

    /**

    /**
     * @param $response
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generatedXMLFiles($response)
    {
        $XMLgenerated = false;
        $dir = $this->helper->getToBeSentFolderPath();
        $fileName = 'Order_' . $response["tracking"]["id"] . '_delivered.xml';
        $content = '<?xml version="1.0" encoding="UTF-8"?>
                    <ADVANCEDSHIPPINGNOTICE>
                    <CANCELLATION>false</CANCELLATION>
                    <SHIPPINGREF>' . $response["tracking"]["shipping_ref"] . '</SHIPPINGREF>
                    <MASTERREF>' . $response["tracking"]["master_ref"] . '</MASTERREF>
                    <TYPE>Return</TYPE>
                    <RMA>' . $response["tracking"]["rma"] . '</RMA>
                    <FORWARDER>' . $response["tracking"]["forwarder"] . '</FORWARDER>';
        $i = 1;
        foreach ($response["tracking"]["lineitems"] as $item) {
            $content .= '<LINEITEM>
                            <SKU>' . $item["sku"] . '</SKU>
                            <LINENUMBER>' . $i . '</LINENUMBER>
                            <QTYEXPECTED>' . $item["qty_expected"] . '</QTYEXPECTED>
                            <UNITCOST>' . $item["unit_cost"] . '</UNITCOST>
                            <LPO>' . $item["lpo"] . '</LPO>
                            </LINEITEM>';
            $i++;
        }
        $content .= '</ADVANCEDSHIPPINGNOTICE>';
        $myfile = fopen($dir . '/' . $fileName, "w") or die("Unable to open file!");

        try {
            fwrite($myfile, $content);
            fclose($myfile);
            $XMLgenerated = true;
            $this->logger->info("ORDER DELIVERY CRON:: GENERATE XML FILE FOR RETURN : SUCCESSFULLY SAVED FILE", array($XMLgenerated));
        } catch (\Exception $e) {
            $XMLgenerated = false;
            $this->logger->info("ORDER DELIVERY CRON:: GENERATE XML FILE FOR RETURN  : COULDN'T SAVE FILE", array($XMLgenerated));
        }
        return $XMLgenerated;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getShippedOrder()
    {
        $collection = $this->collectionFactory->create();
        $orders = $collection->addFieldToFilter('status', SELF::ORDER_STATUS_SHIPPED);

        return $orders;
    }
}
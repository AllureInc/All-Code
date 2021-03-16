<?php


namespace Kerastase\Aramex\Cron;


use Magento\Framework\Exception\NoSuchEntityException;

class OrderShipment
{

    const SUBMITTED_TO_WAREHOUSE ="submited_to_warehouse";
    const  ALLOCATED ="allocated";
    const ORDER_STATUS_SHIPPED = "shipped";
    const SHIPMENT_TRACKING_TITLE = "Aramex AWB";

    /*
     * Aramex order status
     */
    const ARAMEX_ORDER_STATUS_SHIPPED = 'shippedcomplete';

    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;
    /**
     * @var \Kerastase\Aramex\Helper\Data
     */
    private $helper;
    /**
     * @var Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\OrderHistory
     */
    private $orderLog;
    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    private $_convertOrder;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $trackFactory;

    public function __construct(\Kerastase\Aramex\Helper\Data $helper,
                                \Kerastase\Aramex\Logger\Logger $logger,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
                                \Magento\Sales\Model\Order $order,
                                \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog,
                                \Magento\Sales\Model\Convert\Order $convertOrder,
                                 \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory

    )
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->order = $order;
        $this->orderLog = $orderLog;
        $this->_convertOrder = $convertOrder;
        $this->trackFactory = $trackFactory;
    }

    public function execute()
    {

        try{
            $this->logger->info('############################## START ORDER SHIPMENT CRON ####################################');
            $pathToFiles = $this->helper->getOrderShipmentAramexFolder();
            $failedFilesPath = $this->helper->getFaildFolderPath();
            $successPathFile = $this->helper->getSuccessFolderPath();
            $files = $this->helper->getAllFiles($pathToFiles);
            $this->logger->info('### LIST OF FILES TO READ  ',array($files));
            foreach ($files as $file) {
                $this->logger->info('### READING DATA FROM  '.$pathToFiles.'/'.$file);

                /*************** Load Order Informations ***************************/
                $AramexOrder = $this->helper->readXMLFile($pathToFiles.'/'.$file, 'SHIPMENTORDERCONF');

                if($AramexOrder['INVOICENB']){
                try{
                    /************** Get Invoice data by invoice id in xml ********/
                    $orders  = $this->collectionFactory->create();
                    $order = $orders->addFieldToFilter('increment_id',$AramexOrder['INVOICENB'])
                        ->getFirstItem();

                   $orderedItems = $AramexOrder["LINEITEM"];

                    $this->logger->info('ARAMEX ORDERED ITEMS ',$orderedItems);

                    if($order->getData()!= null){
                        $this->logger->info('LOADED ORDER DATA',array($order->getData()));
                        $comment = '';
                        if($order->getStatus() == SELF::ALLOCATED  && $AramexOrder['STATUS'] == SELF::ARAMEX_ORDER_STATUS_SHIPPED){

                            $this->logger->info('LOADED ORDER ITEMS ORDERED',array($order->getItems()));
                            $qty_toCheck = 'QTYSHIPPED';
                            $difference = $this->helper->checkOrderedItems($order->getItems(),$orderedItems,$qty_toCheck);
                            $this->logger->info('COUNT DIFFERENCE BETWEEN PRODUCTS '.count($difference));
                            if(count($difference) == 0){
                                $this->createShipment($order,$AramexOrder['AWB']);
                                $order->setData('shipment_awb',$AramexOrder['AWB']);
                                $order->setData('status',SELF::ORDER_STATUS_SHIPPED);
                                $comment = '#Order shipped with  AWB number '.$AramexOrder['AWB'];

                                $this->logger->info('NEW ORDER STATUS ',array($order->getStatus()));
                                rename($pathToFiles.'/'.$file, $successPathFile.'/'.$file);
                            }else{

                                $this->logger->info('QUANTITIES DOES NOT MATCH  ');
                                $comment = '#Error occured when trying to update order status  # Quantities received from Aramex does not match';
                                $this->helper->sendNotmatchingQuantitiesToAdmin(array($difference))  ;                          }
                        }
                        else{
                            rename($pathToFiles.'/'.$file, $failedFilesPath.'/'.$file);
                            $comment = '#Error in Order shipment Cron Order status is '.$order->getData('status').' It does not match with Aramex received Status #'.$AramexOrder['STATUS'];
                            $this->logger->info('#Error in Order shipment Cron Order status is '.$order->getData('status').' It does not match with Aramex received Status #'.$AramexOrder['STATUS']);

                        }
                        $history = $order->addStatusHistoryComment($comment, false);
                        $history->setIsCustomerNotified(false);
                        $order->save();
                        /******** add Order history log ********/
                        $historyDate = date('Y-m-d H:i:s');
                        $this->orderLog->addRecord($order->getData('entity_id'),$order->getData('status'),'order Shipping','order Shipping',$comment,$historyDate);

                    }else{
                        $this->logger->error('ORDER DOES NOT EXIST WITH INCREMENT ID '.$AramexOrder['INVOICENB']);
                    }

                }catch (NoSuchEntityException $ex){  throw $ex;}
            }
            }
        }catch (\Exception $exception){
            $this->logger->err($exception->getMessage());
        }
        $this->logger->info('############################## END ORDER SHIPMENT CRON ####################################');


    }

    /**
     * @param $orderData
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function createShipment($orderData,$awb){

        $order =  $this->order->load($orderData->getData('entity_id'));
        // to check order can ship or not
        if (!$order->canShip()) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('You can not  create the Shipment of this order.') );
        }else{
            $orderShipment = $this->_convertOrder->toShipment($order);

            foreach ($order->getAllItems() as $orderItem) {
                // Check virtual item and item Quantity
                if (!$orderItem->getQtyToShip() || $orderItem->getIsVirtual()) {
                    continue;
                }
                $qty = $orderItem->getQtyToShip();
                $shipmentItem = $this->_convertOrder->itemToShipmentItem($orderItem)->setQty($qty);

                $orderShipment->addItem($shipmentItem);
            }
            $orderShipment->register();
            $orderShipment->getOrder()->setIsInProcess(true);

            $data = array(
                'carrier_code' => 'custom',
                'title' => SELF::SHIPMENT_TRACKING_TITLE,
                'number' => $awb, // Replace with your tracking number
            );

            $track = $this->trackFactory->create()->addData($data);
            $orderShipment->addTrack($track)->save();

            try {
                $orderShipment->save();
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(
                    __($e->getMessage())
                );
            }

        }


    }



}
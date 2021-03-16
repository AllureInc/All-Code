<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/8/19
 * Time: 11:37 PM
 */

namespace Kerastase\Aramex\Helper;

use Exception;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Zend\Mvc\Service\ServiceListenerFactory;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\Logger\Monolog\00\00\00\00\00\00\00\00\00\00\00\00\00\00\00\00\00
     */
    protected $_customLogger;


    const SHIPMENT_TRACKING_TITLE = "Aramex AWB";
    const STATUS_COMPLETED = 'complete';
    const STATUS_DELIVERED = 'processing_delivered';
    const STATUS_PENDING = 'pending';
    const STATUS_PENDING_APPROVAL = 'new_pending_for_approval';
    const STATUS_INVOICED = 'invoiced';
    const METHOD_NAME = 'cashondelivery';
    const ORDER_STATUS_SHIPPED = "processing_shipped";
    const ORDER_RETURN_XML_TYPE ='Return';
    const ORDER_STATUS_PROCESSING ='processing';
    const ORDER_RETURN_XML_FORWORDER ='Aramex';
    const SHIPMENT_TRACKING_REQUEST = 'TrackShipments';
    const PENDING_TO_SEND_FOLDER ='pool';
    const SO_FOLDER = 'so';
    const ASN_FOLDER = 'asn';
    
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderRepository;

    protected $salesOrderCollectionFactory;


    protected $date;

    protected $scopeConfig;

    /**
     * @var \Magento\Framework\Translate\Inline\StateInterface
     */
    protected $inlineTranslation;

    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    protected $_transportBuilder;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    protected $logger;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $moduleDirReader;
    /**
     * @var \Magento\Framework\Module\Dir\Reader
     */
    protected $reader;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    protected $directory_list;

    /**
     * @var \Magento\Framework\Xml\Parser
     */
    private $parser;

    protected $_serialize;
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $_localeDate;


    /** Variable to contain order items collection for RMA creating
     *
     * @var \Magento\Sales\Model\ResourceModel\Order\Item\Collection
     */
    protected $_orderItems = null;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $_invoiceService;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $orderSender;
    /**
     * @var \Magento\Sales\Model\Order\Pdf\Invoice
     */
    private $_pdfInvoiceModel;

    protected $_outputDirectory;
    private $_myPdfStorageSubDirectory = "generated_pdf_files";
    /**
     * @var \Magento\Framework\Filesystem\DirectoryList
     */
    private $directory;
    private $_transaction;
    /**
     * @var \Magento\Sales\Model\Convert\Order
     */
    private $_convertOrder;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;
    /**
     * @var \Magento\Sales\Model\Order\Shipment\TrackFactory
     */
    private $trackFactory;
    /**
     * @var array
     */
    private $soapOptions;

    /**
     * Orders constructor.
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Kerastase\Aramex\Logger\Logger $customLogger,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Xml\Parser $parser,
        \Magento\Framework\Module\Dir\Reader $reader,
        \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
        Json $serialize,
        \Magento\Framework\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Sales\Model\Service\InvoiceService $invoiceService,
        \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
        \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
        \Magento\Framework\DB\Transaction $transaction,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Sales\Model\Convert\Order $convertOrder,
        \Magento\Sales\Model\Order $order,
        \Magento\Sales\Model\Order\Shipment\TrackFactory $trackFactory

    )
    {
        parent::__construct($context);
        $this->orderRepository = $orderRepository;
        $this->salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->date = $date;
        $this->_storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->logger = $customLogger;
        $this->scopeConfig = $scopeConfig;
        $this->parser = $parser;
        $this->reader = $reader;
        $this->directory_list = $directory_list;
        $this->_serialize = $serialize;
        $this->_scopeConfig = $scopeConfig;
        $this->directory = $directoryList;
        $this->_localeDate = $localeDate;
        $this->_invoiceService = $invoiceService;
        $this->orderSender = $orderSender;
        $this->_pdfInvoiceModel = $pdfInvoiceModel;
        $this->_transaction = $transaction;
        $this->_outputDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB);
        $this->_convertOrder = $convertOrder;
        $this->order = $order;
        $this->trackFactory = $trackFactory;
        $this->soapOptions = $this->getSoapOptions();
    }



    /**
     * @param $qtyNotMatching
     * @param $emailFrom
     * @param $emailTo
     */
    public function sendNotmatchingQuantitiesToAdmin($qtyNotMatching)
    {
	return true;
        /* Here we prepare data for our email  */
        /* Receiver Detail  */
        $receiverInfo = $this->scopeConfig->getValue('trans_email/ident_support/email');

        /* Sender Detail  */
        $senderInfo = $this->scopeConfig->getValue('trans_email/ident_support/email');


        $subject = 'Checking Quantities for products before changing order status';
        $message = '';
        $message .= '<html><body><table><th>Error in Product </th><tbody>';

        foreach ($qtyNotMatching as $var) {
            $message .= '<tr><td>' . $var['sku'] . '</td></tr><tr>';
        }

        $message .= '</tbody></table></body></html>';

        $headers = 'From: ' . $senderInfo . '' . "\r\n" .
            'Reply-To: ' . $receiverInfo . '' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($receiverInfo, $subject, $message, $headers);
    }



    /**
     * Logging Utility
     *
     * @param $message
     */
    public function log($message,$useSeparator = false)
    {
        if($useSeparator){
            $this->logger->info(str_repeat('=', 100));
        }

        $this->logger->info($message);
    }
    /**
     * @param $data
     */
    public function debug_to_console($data)
    {
        echo "<script>console.log( 'XXXXXXXXXX: " . $data . "' );</script>";
    }

    /**
     * @param $path
     * @return mixed
     */
    public function getPath($path)
    {
        $this->logger->info('GET PATH STORE --> '.$this->_storeManager->getStore()->getId());

        return $this->scopeConfig->getValue($path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getId());
    }


    /**
     * @param $fileName
     * @param $arrayName
     * @return mixed
     */
    public function readXMLFile($filePath)
    {
        try {
            $parsedArray = [];
            $this->log('## XML FILE TO READ ## ' . $filePath);

            if(is_file($filePath)){
                /******************* Parse data from xml path *************/
                $parsedArray = $this->parser->load($filePath)->xmlToArray();
                $this->logger->info('PARSED DATA FROM XML ##', array($parsedArray));
                return $parsedArray;
            }else{
                $this->log('FILE DOES NOT EXIST ');
                return $parsedArray;
            }

        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }
    }
    /************************************ Start Auto approve order ********************************************/
    /**
     * Return list of orders with payment method cashondelivery
     * @return array
     */
    public function getCodOrders()
    {
        $orderResult = $this->salesOrderCollectionFactory->create();
        $orderResult->addFieldToFilter('status', self::STATUS_PENDING_APPROVAL);

        $ordersList = array();

        foreach ($orderResult as $order) {
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $methodCode = $method->getCode();

            if ($methodCode == self::METHOD_NAME) {

                array_push($ordersList, $order);
            }
        }
        return $ordersList;
    }

    /**
     * check if customer has completed or delivered orders
     * @param $customerId
     * @return bool
     */
    public function CustumerHascompletedOrder($customerId)
    {

        $this->log(__METHOD__,true);
        $orderResult = $this->salesOrderCollectionFactory->create($customerId);
        $orderResult->addFieldToFilter('status', array('in' => array(self::STATUS_DELIVERED)));

        $this->logger->info('CustumerHascompletedOrder result ', array($orderResult->getData()));

        return count($orderResult) > 0;
    }
    /**
     * @param $emailTemplateVariables
     * @param $emailFrom
     * @param $emailTo
     */
    public function SendMailToAdmin ($emailTemplateVariables, $emailFrom, $emailTo)
    {

        $subject = 'COD Orders status';
        $message = '';
        $message .= '<html><body><table><th>Order Increment Id</th><th>Status </th><tbody>';

        foreach ($emailTemplateVariables as $var) {
            $message .= '<tr><td>' . $var['id'] . '</td><td>' . $var['status'] . '</td></tr><tr>';
        }

        $message .= '</tbody></table></body></html>';

        $headers = 'From: ' . $emailFrom . '' . "\r\n" .
            'Reply-To: ' . $emailTo . '' . "\r\n" .
            'X-Mailer: PHP/' . phpversion();

        mail($emailTo, $subject, $message, $headers);
    }
    /************************************ End Auto approve order ********************************************/

    /************************************ Start Auto Invoicing order ****************************************/

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getAllProcessingOrders (){
        $collecion = $this->salesOrderCollectionFactory->create();
        $collecion->addFieldToFilter(
            'status', 
            array('in' => array(SELF::ORDER_STATUS_PROCESSING,SELF::STATUS_PENDING,SELF::STATUS_INVOICED))
        );
        return $collecion;
    }
    /**
     * @param $order
     * @return bool|\Magento\Sales\Model\Order\Invoice
     * @throws \Exception
     */
    public function invoiceOrder($order)
    {
        if(!$order->canInvoice())
        {
            return false;
        }
        try
        {
            $invoice  = $this->_invoiceService->prepareInvoice($order);
            $invoice->setRequestedCaptureCase(\Magento\Sales\Model\Order\Invoice::CAPTURE_OFFLINE);
            $invoice->setIsPaid(false);
            $invoice->register();
            $transactionSave = $this->_transaction->addObject(
                $invoice
            )->addObject(
                $invoice->getOrder()
            );
            $transactionSave->save();

            $this->orderSender->send($order);

            /********** Generate Pdf for Invoice*******/
            $this->logger->info('ORDER INVOICING CRON:: GENERATING INVOICE PDF FOR INVOICE  '.$invoice->getIncrementId());
            $this->generatePdf($invoice);

        }
        catch(\Exception $e)
        {
            throw $e;
        }

        return true;
    }

    /**
     * @param $order
     * @param $isCod
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generatedXMLFiles ($order, $isCod)
    {
        $XMLgenerated = false;

        $this->logger->info('ORDER INVOICING CRON:: GENERATING XML FILE  FOR ORDER  '.$order->getIncrementId());

        $dir = $this->getToBeSentFolderPath(self::SO_FOLDER).self::PENDING_TO_SEND_FOLDER;

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $orderAddress = $order->getShippingAddress();
        $this->logger->info('ORDER INVOICING CRON :: ORDER DELIVERY ADDRESS ',array($orderAddress->getData()));

        // foreach ($invoicedOrders as $order){
        $fileName = 'aramex-shipment-order-'.$order->getData('increment_id').'.xml';
        if($isCod ===true){
            $cod = 'Y';
        }else{
            $cod = 'N';
        }
        $content='';
        $content .= '<SHIPMENTORDER xmlns:xsd="http://www.w3.org/2001/XMLSchema" xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance">
                             <INVOICENB>'.$order->getIncrementId().'</INVOICENB>
                            <Type>Customer Order</Type>
                           <CURRENCY>'.$order->getData('order_currency_code').'</CURRENCY>
                           <FORWARDER>ARAMEX</FORWARDER>
                           <TRANSPORTMOD>Air</TRANSPORTMOD>
                           <DELIVERYADDRESS>
                           <CONTACT>'.$orderAddress->getData('firstname').' '.$orderAddress->getData('lastname').'</CONTACT>
                           <COMPANY/>
			   <ADDRESS>'.trim(preg_replace('/\s\s+/', ' ', $orderAddress->getData('street'))).' - '.$orderAddress->getData('city').'</ADDRESS>
                           <COUNTRY>'.$orderAddress->getData('country_id').'</COUNTRY>
                           <D_ZIP/>
                           <CITY>'.$orderAddress->getData('region').'</CITY>
                           <PHONE1>'.$orderAddress->getData('telephone').'</PHONE1>
                           <PHONE2>'.$orderAddress->getData('fax').'</PHONE2>
                         </DELIVERYADDRESS>
                         <CODSERVICE>
                         <COD>'.$cod.'</COD>
                         <CODVALUE>'.$order->getData('grand_total').'</CODVALUE>
                         </CODSERVICE>';
        $i = 1;
        foreach ($order->getItems() as $item){

            $unitPrice = ($item->getPrice()/2 >= 1) ? ($item->getPrice()/2) : 1;

            $content.='<LINEITEM>
                            <SKU>'.$item->getSku().'</SKU>
                            <LINENUMBER>'.$i.'</LINENUMBER>
                            <QTYORDERED>'.$item->getQtyOrdered().'</QTYORDERED>
                            <UNITPRICE>'.$unitPrice.'</UNITPRICE>
                            </LINEITEM>';
            $i++;
        }

        $content.='</SHIPMENTORDER>';
        $myfile = fopen($dir . '/' . $fileName, "w") or die("Unable to open file!");

        try {
            fwrite($myfile, $content);
            fclose($myfile);
            $XMLgenerated = true;

        } catch (Exception $e) {
            $this->log($e->getMessage());
            $XMLgenerated = false;
        }

        //}
        return $XMLgenerated;
    }

    /**
     * @param $invoice
     * @return \Kerastase\Aramex\Helper\Data
     */
    public function generatePdf($invoice){

        try{
            $pdfContent = $this->_pdfInvoiceModel->getPdf([$invoice])->render();

	    $dir = $this->getPdfSftpPath();
            $myfile = fopen($dir . "invoice_" . $invoice->getOrder()->getIncrementId() . ".pdf", "w") or die("Unable to open file!");

            fwrite($myfile, $pdfContent);
            fclose($myfile);

            //$this->_outputDirectory->writeFile($this->getPdfSftpPath(). "invoice_" . $invoice->getIncrementId() . ".pdf" ,$pdfContent);
        } catch (Exception $e){
            throw new $e;
        }
        return $this;

    }

    /************************************ End Auto Invoicing order ********************************************/

    /************************************ Start Auto Ship order *************************************************/

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

            $orderShipment->getExtensionAttributes()->setSourceCode('dxb_warehouse'); 

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

    /************************************** End Auto Ship order ********************************************/
    /************************************** Start Auto Delivery order *****************************************/

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getShippedOrder()
    {
        $collection = $this->salesOrderCollectionFactory->create();
        $orders = $collection->addFieldToFilter('status', SELF::ORDER_STATUS_SHIPPED);

        return $orders;
    }

    /**
     * @param $url
     * @param $function
     * @param $params
     * @return bool|mixed
     */
    public function callShipmentService($url, $function, $params){

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

    /************************************** End Auto Delivery order ********************************************/





    /**
     * @param $config_path
     * @param null $storeId
     * @return array|bool|float|int|mixed|string|null
     */
    public function getStoreConfig($config_path, $storeId = null)
    {
        return $this->_serialize->unserialize($this->scopeConfig->getValue(
            $config_path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        ));
    }

    /**
     * @param $orderItems
     * @param $aramexItems
     * @return bool
     */
    public function checkOrderedItems($orderItems, $aramexItems,$qtytoCheck){

        $this->logger->info( 'LIST OF ITEMS FROM ARAMEX',array($aramexItems));

        if (isset($aramexItems['SKU']) == true && strlen(json_encode($aramexItems['SKU'])) > 0) {
            $isArray = false;
        }else{
            $isArray = true;
        }
        $array1 = array();

        foreach ($orderItems as $item){
            $array1 = array('sku' => $item->getSku(), 'qty' =>intVal($item->getQtyOrdered()));
            $this->logger->info('CHECK MATCHING QUANTITIES ORDERED ITEMS $orderItems   #item ',array($item->getData()));

        }
        $array2 = array();
        if ($isArray === true){
            foreach ($aramexItems as $item){
                $array2 = array('sku' => strval($item['SKU']), 'qty' => intval($item[$qtytoCheck]));
                $this->logger->info(' CHECK MATCHING QUANTITIES ORDERED ITEMS  $aramexItems   #item ',array($item));
            }
        }else{
            $array2 = array('sku' => strval($aramexItems['SKU']), 'qty' => intval($aramexItems[$qtytoCheck]));
        }

        $this->logger->info('ITEMS ARRAY1',array($array1));
        $this->logger->info('ITEMS ARRAY2',array($array2));
        $this->logger->info('DIFFERENCE BETWEEN ARRAYS',array_diff_assoc($array2,$array1));

        return array_diff_assoc($array2,$array1);

    }

    /**
     * @return array
     */
    public function getAllFiles($path)
    {
        $files = array();
        try {

            $rootPath = $this->directory->getRoot();

            if(is_dir($path)){
                $folder  = $path;
            }else{
                $folder = $rootPath.'/'.$path;
            }
            $this->logger->info('##  FOLDER FULL PATH ###  '.$folder);

            $elements = scandir($folder,0);
            foreach ($elements as $element){
                if ($element != '.' && $element != '..'){
                    array_push($files,$element);
                }

            }
        } catch (FileSystemException $e) {
            $this->logger->error('ERROOOOOOOR',array($e));
        }
        return $files;
    }
    /**
     * Checks for ability to create RMA
     *
     * @param  int|\Magento\Sales\Model\Order $order
     * @param  bool $forceCreate - set yes when you don't need to check config setting (for admin side)
     * @return bool
     */

    public function canCreateRma($order)
    {
        $items = $order->getItems();
        //if ($items && $order->getStatus()=== SELF::ORDER_STATUS_SHIPPED ) {
        return true;
        //}

        // return false;
    }
    /**
     * @param $response
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generateReturnXMLFiles($order)
    {
        $XMLgenerated = false;
        $dir = $this->getToBeSentFolderPath(self::ASN_FOLDER).self::PENDING_TO_SEND_FOLDER;

        if (!file_exists($dir)) {
            mkdir($dir, 0777, true);
        }

        $fileName = 'aramex-return-order-'.$order->getData('increment_id').'.xml';
        $content = '<?xml version="1.0" encoding="UTF-8"?>
                    <ADVANCEDSHIPPINGNOTICE>
                    <CANCELLATION>false</CANCELLATION>
                    <SHIPPINGREF>' .$order->getData('shipment_awb'). '</SHIPPINGREF>
                    <MASTERREF>' .$order->getData('increment_id'). '</MASTERREF>
                    <TYPE>'.SELF::ORDER_RETURN_XML_TYPE.'</TYPE>
                    <RMA>' . $order->getData('shipment_awb'). '</RMA>
                    <FORWARDER>' .SELF::ORDER_RETURN_XML_FORWORDER. '</FORWARDER>';
        $i = 1;
        foreach ($order->getItems() as $item) {
            $content .= '<LINEITEM>
                            <SKU>' .$item->getSku(). '</SKU>
                            <LINENUMBER>' . $i . '</LINENUMBER>
                            <QTYEXPECTED>' . $item->getQtyOrdered() . '</QTYEXPECTED>
                            <UNITCOST>' . $item->getRowTotalInclTax() . '</UNITCOST>
                            <LPO></LPO>
                            </LINEITEM>';
            $i++;
        }
        $content .= '</ADVANCEDSHIPPINGNOTICE>';
        $myfile = fopen($dir . '/' . $fileName, "w") or die("Unable to open file!");

        try {
            fwrite($myfile, $content);
            fclose($myfile);
            $XMLgenerated = true;
            $this->logger->info("GENERATE XML FILE FOR RETURN : SUCCESSFULLY SAVED FILE", array($XMLgenerated));
        } catch (\Exception $e) {
            $XMLgenerated = false;
            $this->logger->info("GENERATE XML FILE FOR RETURN  : COULDN'T SAVE FILE", array($XMLgenerated));
        }
        return $XMLgenerated;
    }


    /**
     * @return mixed
     */
    public function getIsEnabled(){
        return $this->getPath('aramex_general/general/enable');
    }


    /**
     * @return mixed
     */
    public function getXmlSftpPath(){
        return $this->getPath('aramex_general/general/xml_path_config');
    }

    /**
     * @return mixed
     */
    public function getToBeSentFolderPath($type = ""){
        $type = $type == "" ? $type : '/'.$type.'/';
        return $this->getPath('aramex_general/general/to_be_sent_path_files').$type;
    }
    /**
     * @return mixed
     */
    public function getReceivedFolderPath(){
        return $this->getPath('aramex_general/general/received_files');
    }

    /**
     * @return mixed
     */
    public function getFaildFolderPath(){
        return $this->getPath('aramex_general/general/failed_path_files');
    }

    /**
     * @return mixed
     */
    public function getSuccessFolderPath(){
        return $this->getPath('aramex_general/general/success_path_files');
    }

    /**
     * @return mixed
     */
    public function getPdfSftpPath(){
        return $this->getPath('aramex_general/general/pdf_path_config');
    }

    /**
     * @return mixed
     */
    public function getDestinationFolder(){
        return $this->getPath('aramex/general/folder');
    }

    /**
     * @return mixed
     */
    public function getDestinationFolderSO(){
        return $this->getPath('aramex/general/folder_so');
    }

    /**
     * @return mixed
     */
    public function getDestinationFolderASN(){
        return $this->getPath('aramex/general/folder_asn');
    }

    /**
     * @return mixed
     */
    public function getStockAramexFolder(){
        return $this->getPath('aramex_general/general/stock_aramex_path_files');
    }
    /**
     * @return mixed
     */
    public function getOrderConfirmationAramexFolder(){
        return $this->getPath('aramex_general/general/confirmation_aramex_path_files');
    }
    /**
     * @return mixed
     */
    public function getOrderShipmentAramexFolder(){
        return $this->getPath('aramex_general/general/shipment_aramex_path_files');
    }
    /**
     * @return mixed
     */
    public function getOrderReturnAramexFolder(){
        return $this->getPath('aramex_general/general/return_aramex_path_files');
    }

    public function getShippingApiUrl(){
        return $this->getPath('shipment_tracking/general/api_url');
    }

    public function getShippingApiUsername(){
        return $this->getPath('shipment_tracking/general/api_username');
    }

    public function getShippingApiPassword(){
        return $this->getPath('shipment_tracking/general/api_password');
    }

    public function getShippingApiAccountNumber(){
        return $this->getPath('shipment_tracking/general/api_account_number');
    }

    public function getShippingApiAccountPin(){
        return $this->getPath('shipment_tracking/general/api_account_pin');
    }

    public function getShippingApiAccountEntity(){
        return $this->getPath('shipment_tracking/general/api_account_entity');
    }
    /**
     * Get order admin date
     *
     * @param int $createdAt
     * @return \DateTime
     */
    public function getOrderAdminDate($createdAt)
    {
        return $this->_localeDate->date(new \DateTime($createdAt));
    }


}

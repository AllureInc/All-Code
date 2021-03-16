<?php
/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/8/19
 * Time: 12:09 PM
 */

namespace Kerastase\Aramex\Cron;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Model\Order\Pdf\Invoice;
use Magento\Framework\App\Filesystem\DirectoryList;

class OrderInvoicing {

    const ORDER_STATUS_PROCESSING ='processing';
    const ORDER_STATUS_INVOICED ='invoiced';
    const ORDER_STATUS_SUBMITTED_TO_WAREHOUSE = 'submited_to_warehouse';
    const METHOD_NAME  = 'cashondelivery';
    const SO_FOLDER = 'so';
    const ASN_FOLDER = 'asn';

    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;
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
     * @var \Magento\CatalogInventory\Api\StockRepositoryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\Sales\Model\Service\InvoiceService
     */
    private $_invoiceService;
    /**
     * @var \Magento\Framework\DB\Transaction
     */
    private $_transaction;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\OrderSender
     */
    private $orderSender;
    /**
     * @var Invoice
     */
    private $pdfInvoice;
    /**
     * @var FileFactory
     */
    private $fileFactory;
    /**
     * @var \Magento\Framework\App\Filesystem\DirectoryList
     */
    private $directory_list;
    /**
     * @var \Magento\Framework\Filesystem
     */
    private $filesystem;

    protected $_pdfInvoiceModel;

    protected $_outputDirectory;
    private $_myPdfStorageSubDirectory = "generated_pdf_files";
    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\OrderHistory
     */
    private $orderLog;

    /**
     * OrderInvoicing constructor.
     * @param \Kerastase\Aramex\Helper\Data $helper
     * @param \Kerastase\Aramex\Logger\Logger $logger
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory
     * @param \Magento\Sales\Model\Order $order
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(\Kerastase\Aramex\Helper\Data $helper,
                                \Kerastase\Aramex\Logger\Logger $logger,
                                \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
                                \Magento\Sales\Model\Order $order,
                                \Magento\Catalog\Model\Product $product,
                                \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
                                \Magento\Sales\Model\Service\InvoiceService $invoiceService,
                                \Magento\Framework\DB\Transaction $transaction,
                                \Magento\Sales\Model\Order\Email\Sender\OrderSender $orderSender,
                                Invoice $pdfInvoice,
                                FileFactory $fileFactory,
                                DateTime $dateTime,
                                \Magento\Framework\App\Filesystem\DirectoryList $directory_list,
                                \Magento\Framework\Filesystem\Io\Ftp $ftp,
                                \Magento\Framework\Filesystem $filesystem,
                                \Magento\Sales\Model\Order\Pdf\Invoice $pdfInvoiceModel,
                                \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog
                                )
    {
        $this->helper = $helper;
        $this->logger = $logger;
        $this->collectionFactory = $collectionFactory;
        $this->order = $order;
        $this->product = $product;
        $this->stockRegistry = $stockRegistry;
        $this->_invoiceService = $invoiceService;
        $this->_transaction = $transaction;
        $this->orderSender = $orderSender;
        $this->pdfInvoice = $pdfInvoice;
        $this->fileFactory = $fileFactory;
        $this->dateTime = $dateTime;
        $this->directory_list = $directory_list;
        $this->ftp = $ftp;
        $this->filesystem = $filesystem;
        $this->_pdfInvoiceModel = $pdfInvoiceModel;
        $this->_outputDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::PUB);
        $this->orderLog = $orderLog;
    }

    public function execute()
    {
        try{
            $this->logger->info('############################## START ORDER INVOICING CRON ####################################');
            $processingOrders = $this->getAllProcessingOrders();
            $productsOutStock = [];
            $comment = '';

            foreach ($processingOrders as $order){

                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
                $methodCode = $method->getCode();

                $orderItems = $order->getItems();

                if (null !== $orderItems) {
                    /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
                    foreach ($orderItems as $orderItem) {
                        $productId = $orderItem->getProductId();
                        $product = $this->product->load($productId);
                        $this->logger->info('ORDER INVOICING CRON:: LOADING PRODUCT DATA', array($product->getData()));
                        /************* Check if product is in stock *****************/
                        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());

                        $this->logger->info($product->getName().'  Is in stock '. $stockItem->getData('is_in_stock'));

                        if ($stockItem->getData('qty') > 0 && $stockItem->getData('is_in_stock') == 1) {
                            $allProductsInStock = true;
                        } else {
                            $allProductsInStock = false;
                            array_push($productsOutStock, $product);
                        }
                        $this->logger->info('ORDER INVOICING CRON:: all products are in stock ?'.$allProductsInStock);

                        if ($allProductsInStock == true){
                            $comment = 'All Items in stock ';
                            $isCod= false;
                            $order->setStatus(SELF::ORDER_STATUS_INVOICED);

                            /********* Check if is a COD order ********/
                            $this->logger->info('ORDER INVOICING CRON:: ORDER METHOD CODE DATA '.$methodCode);
                            if ($methodCode ==self::METHOD_NAME){
                                /********** Create Invoice for order *******/
                                $isCod= true;
                                $this->invoiceOrder($order);
                            }
                            $generatedSuccess = $this->generatedXMLFiles($order,$isCod);
                            if($generatedSuccess){
                                $order->setStatus(SELF::ORDER_STATUS_SUBMITTED_TO_WAREHOUSE);
                            }
                        }else{
                            foreach ($productsOutStock as $productNotInStock){
                                $comment.=  $productNotInStock
                                        ->getName() . ' Product is missing  '.$orderItem->getQtyOrdered().' quantity <br/>';
                            }
                        }
                    }

                    $order->addStatusHistoryComment($comment, false);
                    $order->save();
                    $historyDate=date('Y-m-d H:i:s');
                    $this->orderLog->addRecord($order->getData('entity_id'),$order->getData('status'),'order Invoicing','order Invoicing',$comment,$historyDate);

                }
            }

        }catch (\Exception $exception){
            $this->logger->err($exception->getMessage());
        }
        $this->logger->info('############################## END ORDER INVOICING CRON ####################################');


    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getAllProcessingOrders (){
        $collecion = $this->collectionFactory->create();
        $collecion->addFieldToFilter('status',SELF::ORDER_STATUS_PROCESSING);
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
     * @param $invoice
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Zend_Pdf_Exception
     */
    public function generatePdf ($invoice){

        try{
            $pdfContent = $this->_pdfInvoiceModel->getPdf([$invoice])->render();
            //save to file wherever you want, in this example in var/pdfinvoices/[IncrementID].pdf
            $this->_outputDirectory->writeFile($this->_myPdfStorageSubDirectory. "/invoice_" . $invoice->getIncrementId() . ".pdf" ,$pdfContent);
        } catch (Exception $e){
            throw new $e;
        }
        return $this;

    }

    /**
     * @param $order
     * @param $isCod
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function generatedXMLFiles($order, $isCod)
    {
        $XMLgenerated = false;

          $this->logger->info('ORDER INVOICING CRON:: GENERATING XML FILE  FOR ORDER  '.$order->getIncrementId());

          $dir = $this->helper->getToBeSentFolderPath(self::SO_FOLDER);

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
                           <CURRENCY>'.$order->getData('store_currency_code').'</CURRENCY>
                           <FORWARDER>ARAMEX</FORWARDER>
                           <TRANSPORTMOD>Air</TRANSPORTMOD>
                           <DELIVERYADDRESS>
                           <CONTACT>'.$orderAddress->getData('firstname').' '.$orderAddress->getData('lastname').'</CONTACT>
                           <COMPANY/>
                           <ADDRESS>'.$orderAddress->getData('street').'</ADDRESS>
                           <COUNTRY>'.$orderAddress->getData('country_id').'</COUNTRY>
                           <D_ZIP/>
                           <CITY>'.$orderAddress->getData('city').'</CITY>
                           <PHONE1>'.$orderAddress->getData('telephone').'</PHONE1>
                           <PHONE2/>
                         </DELIVERYADDRESS>
                         <CODSERVICE>
                         <COD>'.$cod.'</COD>
                         <CODVALUE>'.$order->getData('grand_total').'</CODVALUE>
                         </CODSERVICE>';
            $i = 1;
            foreach ($order->getItems() as $item){

                $content.='<LINEITEM>
                            <SKU>'.$item->getSku().'</SKU>
                            <LINENUMBER>'.$i.'</LINENUMBER>
                            <QTYORDERED>'.$item->getQtyOrdered().'</QTYORDERED>
                            <UNITPRICE>'.$item->getRowTotalInclTax().'</UNITPRICE>
                            </LINEITEM>';
                $i++;
            }

            $content.='</SHIPMENTORDERCONF>';
            $myfile = fopen($dir . '/' . $fileName, "w") or die("Unable to open file!");

            try {
                fwrite($myfile, $content);
                fclose($myfile);
                $XMLgenerated = true;

            } catch (Exception $e) {
                $this->_logger($e->getMessage());
                $XMLgenerated = false;
            }

        //}
        return $XMLgenerated;
    }
}

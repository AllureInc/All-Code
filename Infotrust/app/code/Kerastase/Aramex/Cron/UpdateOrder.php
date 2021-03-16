<?php


namespace Kerastase\Aramex\Cron;


use Kerastase\Aramex\Helper\Data;
use Magento\Framework\App\Config\ScopeConfigInterface as ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\InventoryApi\Api\SourceItemsSaveInterface;
use Magento\InventoryApi\Api\Data\SourceItemInterfaceFactory;
use Magento\InventoryApi\Api\GetSourceItemsBySkuInterface;
use Magento\Framework\App\ResourceConnection;

class UpdateOrder
{


    const ORDER_STATUS_PENDING = 'pending';
    const ORDER_STATUS_PROCESSING = 'processing';
    const ORDER_STATE_PROCESSING = 'processing';
    const ORDER_STATUS_INVOICED = 'invoiced';
    const ORDER_STATUS_SUBMITTED_TO_WAREHOUSE = 'submited_to_warehouse';
    const ORDER_STATUS_ALLOCATED = 'processing_allocated';
    const ORDER_STATUS_PACKED = 'processing_packed';
    const ORDER_STATUS_DELIVERED = 'processing_delivered';
    const ORDER_STATUS_TO_BE_RETURNED = 'processing_to_be_returned';
    const ORDER_STATUS_RETURNED = 'canceled_returned';
    const ORDER_STATUS_SHIPPED = "processing_shipped";
    const ORDER_STATE_CANCELED = "canceled";

    const METHOD_NAME = 'cashondelivery';


    /***** Aramex order status  **/
    const ARAMEX_ORDER_STATUS_ALLOCATED = 'allocated';
    const ARAMEX_ORDER_STATUS_RELEASED = 'released';
    const ARAMEX_ORDER_STATUS_PACKED = 'packcomplete';
    const ARAMEX_ORDER_STATUS_SHIPPED = 'shippedcomplete';
    const RECEIVED_SUCCESS_FOLDER = 'success';
    const RECEIVED_FAILED_FOLDER = 'failed';

    const SHIPMENT_TRACKING_REQUEST = 'TrackShipments';

    const INBOX_RECEIVED = 'received/';
    const INBOX_SUCCESS = 'success/';
    const INBOX_FAILED = 'failed/';

    const STOCK_FOLDER_INBOX = BP.'/var/sc/m2/stock/%s/inbox/';
    const STOCK_FOLDER_SUCCESS = BP.'/var/sc/m2/stock/%s/success/';
    const STOCK_FOLDER_FAILED = BP.'/var/sc/m2/stock/%s/failed/';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;
    /**
     * @var \Aramex\Aramex\Model\ResourceModel\OrderHistory
     */
    private $orderLog;
    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    private $stockRegistry;
    /**
     * @var \Magento\Framework\Mail\Template\TransportBuilder
     */
    private $_transportBuilder;
    /**
     * @var Data
     */
    private $helper;
    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Catalog\Model\Product
     */
    private $product;
    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItem;
    /**
     * @var \Kerastase\Aramex\Model\ResourceModel\History
     */
    private $history;

    private $sourceRepo;
    private $searchCriteria;

    private $_getSourceItemsBySkuInterface;
    private $_sourceItemFactory;
    private $_sourceItemsSaveInterface;

    protected $resourceConnection;

    public function __construct(
        \Kerastase\Aramex\Logger\Logger $logger,
        Data $helper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        ScopeConfigInterface $scopeConfig,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Kerastase\Aramex\Model\ResourceModel\OrderHistory $orderLog,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $collectionFactory,
        \Magento\Catalog\Model\Product $product,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItem,
        \Kerastase\Aramex\Model\ResourceModel\History $history,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\InventoryApi\Api\SourceRepositoryInterface $sourceRepo,
        \Magento\Framework\Api\SearchCriteriaBuilderFactory $searchCriteria,
        SourceItemsSaveInterface $sourceItemsSaveInterface,
        SourceItemInterfaceFactory $sourceItemFactory,
        GetSourceItemsBySkuInterface $getSourceItemsBySkuInterface,
        ResourceConnection $resourceConnection
    )
    {
        $this->logger = $logger;
        $this->helper = $helper;
        $this->_transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->stockRegistry = $stockRegistry;
        $this->orderLog = $orderLog;
        $this->collectionFactory = $collectionFactory;
        $this->product = $product;
        $this->stockItem = $stockItem;
        $this->history = $history;
        $this->storeManager = $storeManager;

        $this->sourceRepo = $sourceRepo;
        $this->searchCriteria = $searchCriteria;

        $this->_sourceItemsSaveInterface = $sourceItemsSaveInterface;
        $this->_sourceItemFactory = $sourceItemFactory;
        $this->_getSourceItemsBySkuInterface = $getSourceItemsBySkuInterface;
        $this->resourceConnection = $resourceConnection;
    }

    public function reindexStock()
    {
        $connection  = $this->resourceConnection->getConnection();
        $query = 'update inventory_stock_2 set is_salable = 1 where quantity > 0;';
	$clearReservationQuery = 'TRUNCATE TABLE inventory_reservation;';
        $connection->query($query);
	$connection->query($clearReservationQuery);
    }

    public function execute()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website)
        {
            $storeId = $website->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            $this->logger->info('Store Info line 161',  $this->storeManager->getStore()->toArray());

            if($this->helper->getIsEnabled() == 1)
            {
                /***** Update stock ***/
                $this->updateStoreStock();
                /***** Autoaproval  Orders ***/
                $this->ApproveStoreOrders();
                /***** Autoaproval  Orders ***/
                $this->InvoiceStoreOrders();
                /**** Proceed orders for confirmation or shipment */
                $this->ProceedStoreOrder();
                /***** Deliver order after calling track shipment API *****/    
                $this->DeliveryStoreOrders();
                /**** Return Orders *********/
                //$this->returnOrder(); added to ProceedOrder
            }
        }
    }

    public function UpdateStock()
    {
        $searchCriteriaBuilder = $this->searchCriteria->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        $sources = $this->sourceRepo->getList($searchCriteria)->getItems();  

        foreach($sources as $source) {
            if($source['enabled'] == 1)
            {
                $this->updateSourceStock($source['source_code']);
            }
        }
	
	try {
		$this->reindexStock();
		$this->logger->info(__METHOD__." => Stock reindexed");

	} catch(Exception $e) {
		$this->logger->info(__METHOD__." => Error in reindexing Stock :".$e->getMessage());
	}
    }

    public function ApproveOrders()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website)
        {
            $storeId = $website->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            $this->logger->info('Store Info line 211',  $this->storeManager->getStore()->toArray());

            if($this->helper->getIsEnabled() == 1)
            {
                $this->ApproveStoreOrders();
            }
        }
    }

    public function InvoiceOrders()
    {
        $this->logger->info("--------- Invoice Order Function Called ---------");
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website)
        {
            $storeId = $website->getDefaultStore()->getId();
            $this->logger->info("--------- Store ID: ".$storeId." ---------");
            $this->storeManager->setCurrentStore($storeId);
            /*$this->logger->info('Store Info line 228',  $this->storeManager->getStore()->toArray());*/

            if($this->helper->getIsEnabled() == 1)
            {
                $this->logger->info("--------- Enabled ---------");
                $this->InvoiceStoreOrders();
            } else {
                $this->logger->info("--------- Not Enabled ---------");
            }
        }
    }

    public function ProceedOrder()
    {   
        $this->logger->info('inside proceedOrder');
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website)
        {
            $storeId = $website->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            $this->logger->info('Store Info line 246',  $this->storeManager->getStore()->toArray());

            if($this->helper->getIsEnabled() == 1)
            {
                $this->ProceedStoreOrder();
            }
        }
    }

    public function DeliveryOrders()
    {
        $websites = $this->storeManager->getWebsites();

        foreach ($websites as $website)
        {
            $storeId = $website->getDefaultStore()->getId();
            $this->storeManager->setCurrentStore($storeId);
            $this->logger->info('Store Info line 263',  $this->storeManager->getStore()->toArray());

            if($this->helper->getIsEnabled() == 1)
            {
                $this->DeliveryStoreOrders();
		break;
            }
        }
    }

    public function updateItemStock($sku, $qty, $sourceCode)
    {
        $historyDate = date('Y-m-d H:i:s');
        $productId = $this->product->getIdBySku($sku);
        if ($productId) {
            
            $stockStatus = $qty > 0 ? 1 : 0;
            $sourceItem = $this->_sourceItemFactory->create();
            $sourceItem->setSourceCode($sourceCode);
            $sourceItem->setSku($sku);
            $sourceItem->setQuantity($qty);
            $sourceItem->setStatus($stockStatus);

            $this->_sourceItemsSaveInterface->execute([$sourceItem]);
            
            $old_qty = 0;
            $sourceItemList = $this->_getSourceItemsBySkuInterface->execute($sku);
            foreach ($sourceItemList as $prodSource) {
                if($prodSource['source_code'] == $sourceCode)
                {
                    $old_qty = $prodSource['quantity'];
                }
            }
            
            $comment = "Product Quantity Successfully Updated";
            $this->history->addRecord($sku, $old_qty, $qty, $comment, $historyDate);
	    $this->logger->info("Product $sku - $old_qty - $qty");

        } else {
            $this->helper->log('##  Product with SKU ' . $sku . ' does not exist');
            $comment = "Product  does not exist";
            $this->history->addRecord($sku, 0, $qty, $comment, $historyDate);
        }
    }

    public function getPendingOrdersQuantities()
    {
        $processingOrders = $this->helper->getAllProcessingOrders();
        $products = array();

        foreach ($processingOrders as $order) {
            $orderItems = $order->getItems();

            foreach ($orderItems as $orderItem) {
                $productSku = $orderItem->getSku();
                $productQty = (int) $orderItem->getQtyOrdered();

                if(isset($products[$productSku]) == true) {
                    $products[$productSku] += $productQty;
                }
                else {
                    $products[$productSku] = $productQty;
                }
            }
        }
        return $products;
    }

    public function updateStockBeforeSendingOrders()
    {
        $searchCriteriaBuilder = $this->searchCriteria->create();
        $searchCriteria = $searchCriteriaBuilder->create();
        $sources = $this->sourceRepo->getList($searchCriteria)->getItems();

        foreach($sources as $source) {
            if($source['enabled'] == 1)
            {
                $this->updateSourceStock($source['source_code'], true);
            }
        }
    }

    public function updateSourceStock($sourceCode, $beforeSendingOrder = false)
    {
        $inboxFolder = sprintf(self::STOCK_FOLDER_INBOX, $sourceCode);
        $successFolder = sprintf(self::STOCK_FOLDER_SUCCESS, $sourceCode);
        $failedFolder = sprintf(self::STOCK_FOLDER_FAILED, $sourceCode);

        $quantitiesToReserve = $beforeSendingOrder == false ? $this->getPendingOrdersQuantities() : array();

        try {
            $files = $this->helper->getAllFiles($inboxFolder);

            foreach ($files as $file) {

                $data = $this->helper->readXMLFile($inboxFolder.$file);
                if (array_key_exists('STOCK', $data)) {
                    $lines = $data['STOCK']['LINEITEM'];
                    //$this->logger->info('$lines',array($data['STOCK']['LINEITEM']));
                    $counter = 0;
                    $success = false;

/*                    if (array_key_exists('SKU', $lines)) {
                        $sku = strval($lines['SKU']);
                        $this->updateItemStock(strval($lines['SKU']), $lines['QTYORDERABLE'], $sourceCode);
                    } else {
                        foreach ($lines as $line) {
                            $this->updateItemStock(strval($line['SKU']), $line['QTYORDERABLE'], $sourceCode);
                        }
                    }
*/

                    if (array_key_exists('SKU', $lines)) {
                        $sku = strval($lines['SKU']);
                        $reserve = isset($quantitiesToReserve[$sku]) == true ? $quantitiesToReserve[$sku] : 0; 

                        $this->updateItemStock($sku, $lines['QTYORDERABLE'] - $reserve, $sourceCode);
                    } else {
                        foreach ($lines as $line) {
                            $sku = strval($line['SKU']);
                            $reserve = isset($quantitiesToReserve[$sku]) == true ? $quantitiesToReserve[$sku] : 0; 

                            $this->updateItemStock($sku, $line['QTYORDERABLE'] - $reserve, $sourceCode);
                        }
                    }

		            $this->logger->info("Copy Stock files from $inboxFolder to $successFolder");
                    rename($inboxFolder.$file, $successFolder. 'STOCK_'.$file);
                }

            }
        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }
    }

    /* NOT USED */
    public function updateStoreStock()
    {
        $this->helper->log(__METHOD__, true);
        try {
            $pathToFiles = $this->helper->getReceivedFolderPath().self::INBOX_RECEIVED;;
            $files = $this->helper->getAllFiles($pathToFiles);

            foreach ($files as $file) {

                $data = $this->helper->readXMLFile($pathToFiles.$file);
                if (array_key_exists('STOCK', $data)) {
                    $lines = $data['STOCK']['LINEITEM'];
                    $this->logger->info('$lines',array($data['STOCK']['LINEITEM']));
                    $counter = 0;
                    $success = false;

                    $historyDate = date('Y-m-d H:i:s');
                    if (array_key_exists('SKU', $lines)) {
                        $isArray = false;
                    }else{
                        $isArray = true;
                    }
                    if ($isArray === false) {
                        $sku = strval($lines['SKU']);
                        $productId = $this->product->getIdBySku($sku);
                        if ($productId) {
                            $productStock = $this->stockItem->get($productId);
                            $old_qty = $productStock->getQty();
                            //if ($productStock->getQty() >= $lines['QTYORDERABLE']) {
                            $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                            $stockItem->setQty($lines['QTYORDERABLE']);
                            $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                            $comment = "Product Quantity Successfully Updated";
                            $this->history->addRecord($sku, $old_qty, $lines['QTYORDERABLE'], $comment, $historyDate);

                        } else {
                            $this->helper->log('##  Product with SKU ' . $sku . ' does not exist');
                            $comment = "Product  does not exist";
                            $this->history->addRecord($sku, 0, $lines['QTYORDERABLE'], $comment, $historyDate);
                        }
                    } else {
                        foreach ($lines as $line) {
                            $sku = strval($line['SKU']);
                            $productId = $this->product->getIdBySku($sku);
                            if ($productId) {
                                $productStock = $this->stockItem->get($productId);
                                $old_qty = $productStock->getQty();
                                // if ($productStock->getQty() >= $line['QTYORDERABLE']) {
                                $stockItem = $this->stockRegistry->getStockItemBySku($sku);
                                $stockItem->setQty($line['QTYORDERABLE']);
                                $this->stockRegistry->updateStockItemBySku($sku, $stockItem);
                                $comment = "Product Quantity Successfully Updated";
                                $this->history->addRecord($sku, $old_qty, $line['QTYORDERABLE'], $comment, $historyDate);

                            } else {
                                $this->helper->log('##  Product with SKU ' . $sku . ' does not exist');
                                $comment = "Product does not exist";
                                $this->history->addRecord($sku, 0, $line['QTYORDERABLE'], $comment, $historyDate);
                            }
                        }
                    }

                    rename($pathToFiles.$file, $pathToSuccessFiles. 'STOCK_'.$file);
                }

            }
        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }
    }

    public function ApproveStoreOrders()
    {

        $this->helper->log(__METHOD__, true);

        $orders = $this->helper->getCodOrders();

        $orderStatus = array();

        foreach ($orders as $order) {
            /* Check if customer has already an completed or delivered orders and he has no orders last 24 hours*/
            if (($this->helper->CustumerHascompletedOrder($order->getCustomerId()) == true)) {

                $orderData = $order->load($order->getId());
                $orderData->setStatus(self::ORDER_STATUS_PENDING);
                $orderData->save();

                $this->helper->log('AUTOAPPROVAL CRON:: Order Status changed to processing');

                array_push($orderStatus, array("id" => $order->getIncrementId(), "status" => 'Approved'));

            } else {

                array_push($orderStatus, array("id" => $order->getIncrementId(), "status" => 'Not Approved'));

                $this->helper->log(__METHOD__ . ':: An error has occured status not changed');
            }
        }
        /* Here we prepare data for our email  */
        /* Receiver Detail  */
        $receiverInfo = $this->scopeConfig->getValue('trans_email/ident_support/email');
        /* Sender Detail  */
        $senderInfo = $this->scopeConfig->getValue('trans_email/ident_support/email');
        $emailTempVariables = $orderStatus;
        /* We write send mail function in helper because if we want to
           use same in other action then we can call it directly from helper */
        //$this->helper->SendMailToAdmin($emailTempVariables, $senderInfo, $receiverInfo);
    }

    public function getStoreInvoiceableQuantities()
    {
        $invoiceableQuantities = array();

        $processingOrders = $this->helper->getAllProcessingOrders();
        foreach ($processingOrders as $order) {
            $orderItems = $order->getItems();

            if (null !== $orderItems) {
                foreach ($orderItems as $orderItem) {
                    $productId = $orderItem->getProductId();

                    if(isset($invoiceableQuantities[$productId])) {
                        $invoiceableQuantities[$productId] += $orderItem->getQtyOrdered();
                    } else {
                        $invoiceableQuantities[$productId] = $orderItem->getQtyOrdered();
                    }
                }
            }
        }

        return $invoiceableQuantities;
    }

    public function InvoiceStoreOrders()
    {
        $this->logger->info("--------- In ".__METHOD__." ---------");

        $this->helper->log(__METHOD__, true);

        //try {
            $processingOrders = $this->helper->getAllProcessingOrders();

            $this->helper->log("########################### ORDERS LIST TO BE INVOICED ###########################");
            $this->logger->info("########################### ORDERS LIST TO BE INVOICED ###########################");
            foreach ($processingOrders as $order)
            {
                $this->helper->log($order->getIncrementId());
                $this->logger->info($order->getIncrementId());
            }
            $this->helper->log("########################### END ORDERS LIST TO BE INVOICED ###########################");
            $this->logger->info("########################### END ORDERS LIST TO BE INVOICED ###########################");
            
            foreach ($processingOrders as $order) {

    	        try {

    		        $productsOutStock = [];
                    $comment = '';

                    $payment = $order->getPayment();
                    $method = $payment->getMethodInstance();
                    $methodCode = $method->getCode();

                    $orderItems = $order->getItems();

                    if (null !== $orderItems) {
                        /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
                        foreach ($orderItems as $orderItem) {
                            $productId = $orderItem->getProductId();
                            $product = $this->product->load($productId);
                            $this->logger->info("---- Line 576 | Product ID: ".$productId." ---- ");

                            /************* Check if product is in stock *****************/

                            $itemStock = 0;
                            $itemSalable = 0;
                            $sourceItemList = $this->_getSourceItemsBySkuInterface->execute($product->getSku());
                            foreach ($sourceItemList as $prodSource) {
                                if($prodSource['source_code'] == 'dxb_warehouse')
                                {
                                    $itemStock = $prodSource['quantity'];
                                    $itemSalable = $prodSource['status'];
                                    $this->logger->info("---- Line 588 | Product ID: ".$productId." | product source is dxb_warehouse ---- ");
                                } else {
                                    $this->logger->info("---- Line 589 | Product ID: ".$productId." | product source is not 'dxb_warehouse' ---- ");
                                }
                            }
                            
                            //$this->helper->log($product->getName() . '  Is in stock ' . $stockItem->getData('is_in_stock'));

                            if ($itemStock - $orderItem->getQtyOrdered() >= 0 && $itemSalable == 1) {
                                $allProductsInStock = true;
                            } else {
    			                $this->helper->log($order->getIncrementId()." ## ITEM MISSING ## ".$product->getSku()." S $itemStock VS R ".$orderItem->getQtyOrdered()." & $itemSalable");
                                $this->logger->info($order->getIncrementId()." ## ITEM MISSING ## ".$product->getSku()." S $itemStock VS R ".$orderItem->getQtyOrdered()." & $itemSalable");

                                $allProductsInStock = false;
                                array_push($productsOutStock, $product);
                            }
                        }

                        $this->helper->log($order->getIncrementId().' ORDER INVOICING CRON:: all products are in stock ?' . $allProductsInStock);
                        $this->logger->info($order->getIncrementId().' ORDER INVOICING CRON:: all products are in stock ?' . $allProductsInStock);


                        if ($allProductsInStock == true) {
                            $comment = 'All Items in stock ';
                            $this->logger->info("===== Line 613 | All items in stock =====");
                        } else {
                            foreach ($productsOutStock as $productNotInStock) {
                                $comment .= $productNotInStock
                                        ->getName() . ' Product is missing  ' . $orderItem->getQtyOrdered() . ' quantity <br/>';
                            }
                            $this->logger->info("===== Line 619 | ".$comment." =====");
                        }

                        $isCod = false;
                        $order->setState(SELF::ORDER_STATE_PROCESSING);
                        $order->setStatus(SELF::ORDER_STATUS_INVOICED);

                        /********* Check if is a COD order ********/
                        //$this->helper->log('ORDER INVOICING CRON:: ORDER METHOD CODE DATA ' . $methodCode);

                        if ($methodCode == self::METHOD_NAME && $order->canInvoice()) {
                            /********** Create Invoice for order *******/
                            $isCod = true;
                            $this->helper->invoiceOrder($order);
                            $this->logger->info("===== Line 633 | Created Invoice for order =====");
                        } else {
                            $this->logger->info("===== Line 635 | Method Code : ".$methodCode." | Can Invoice : ".$order->canInvoice()." =====");
                        }

                        $generatedSuccess = $this->helper->generatedXMLFiles($order, $isCod);
                        if ($generatedSuccess) {
                            $order->setStatus(SELF::ORDER_STATUS_SUBMITTED_TO_WAREHOUSE);
                            $this->logger->info("===== Line 641 | XML generated =====");
                        } else {
                            $this->logger->info("===== Line 643 | XML not generated =====");
                        }

                        $order->addStatusHistoryComment($comment, false);
                        $order->save();
                        $historyDate = date('Y-m-d H:i:s');
                        $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Invoicing', 'order Invoicing', $comment, $historyDate);
                        $this->helper->log($order->getIncrementId().' - '.$order->getData('status').' - '.$comment);
                        $this->logger->info('===== Line 651'.$order->getIncrementId().' - '.$order->getData('status').' - '.$comment);
                    } else {
                        $this->logger->info(" ##### Order Items is null #### ");
                    }

             	} catch (\Exception $exception) {
                	    $this->logger->err("CANNOT INVOICE AND SEND ORDER : ".$order->getIncrementId(). " --> ". $exception->getMessage());
            	}
            }

     //   } catch (\Exception $exception) {
     //       $this->logger->err($exception->getMessage());
     //   }


    }

    public function OLDInvoiceStoreOrders()
    {

        $this->helper->log(__METHOD__, true);

        try {
            $processingOrders = $this->helper->getAllProcessingOrders();
            $productsOutStock = [];
            $comment = '';
            
            foreach ($processingOrders as $order) {

                $payment = $order->getPayment();
                $method = $payment->getMethodInstance();
                $methodCode = $method->getCode();

                $orderItems = $order->getItems();

                if (null !== $orderItems) {
                    /** @var \Magento\Sales\Api\Data\OrderItemInterface $orderItem */
                    foreach ($orderItems as $orderItem) {
                        $productId = $orderItem->getProductId();
                        $product = $this->product->load($productId);

                        /************* Check if product is in stock *****************/
                        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());

                        //$this->helper->log($product->getName() . '  Is in stock ' . $stockItem->getData('is_in_stock'));

                        if ($stockItem->getData('qty') - $orderItem->getQtyOrdered() >= 0 && $stockItem->getData('is_in_stock') == 1) {
                            $allProductsInStock = true;
                        } else {
                            $allProductsInStock = false;
                            array_push($productsOutStock, $product);
                        }
                        $this->helper->log('ORDER INVOICING CRON:: all products are in stock ?' . $allProductsInStock);

                        if ($allProductsInStock == true) {
                            $comment = 'All Items in stock ';
                            $isCod = false;
                            $order->setState(SELF::ORDER_STATE_PROCESSING);
                            $order->setStatus(SELF::ORDER_STATUS_INVOICED);

                            /********* Check if is a COD order ********/
                            $this->helper->log('ORDER INVOICING CRON:: ORDER METHOD CODE DATA ' . $methodCode);

                            if ($methodCode == self::METHOD_NAME) {
                                /********** Create Invoice for order *******/
                                $isCod = true;
                                $this->helper->invoiceOrder($order);
                            }
                            $generatedSuccess = $this->helper->generatedXMLFiles($order, $isCod);
                            if ($generatedSuccess) {
                                $order->setStatus(SELF::ORDER_STATUS_SUBMITTED_TO_WAREHOUSE);
                            }
                        } else {
                            foreach ($productsOutStock as $productNotInStock) {
                                $comment .= $productNotInStock
                                        ->getName() . ' Product is missing  ' . $orderItem->getQtyOrdered() . ' quantity <br/>';
                            }
                        }
                    }
                    $order->addStatusHistoryComment($comment, false);
                    $order->save();
                    $historyDate = date('Y-m-d H:i:s');
                    $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Invoicing', 'order Invoicing', $comment, $historyDate);

                }
            }

        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }


    }

    public function ProceedStoreOrder()
    {

        $this->helper->log(__METHOD__, true);

        try {
            $pathToFiles = $this->helper->getReceivedFolderPath().self::INBOX_RECEIVED;
            $files = $this->helper->getAllFiles($pathToFiles);
            
            foreach ($files as $file) {
                $this->helper->log('------------ File Start ----------------');
                $AramexOrder = $this->helper->readXMLFile($pathToFiles. $file);

                if (array_key_exists('SHIPMENTORDERCONF', $AramexOrder)) {
                    if ($AramexOrder['SHIPMENTORDERCONF']['INVOICENB'] ) {
                        $this->helper->log('ORDER TO CONFIRM WITH INCREMENT ID ' . $AramexOrder['SHIPMENTORDERCONF']['INVOICENB'] );
                        $this->confirmOrder($AramexOrder['SHIPMENTORDERCONF'], $file);
                    }
                } else if(array_key_exists('ADVANCEDSHIPPINGNOTICECONF', $AramexOrder)) {
                    if ($AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF']) {

                        $this->helper->log('ORDER TO RETURN WITH INCREMENT ID ' . $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF']);
                        $orders = $this->collectionFactory->create();
                        $order = $orders->addFieldToFilter('increment_id', $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF'])->getFirstItem();
                        $orderedItems = $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']["LINEITEM"];
                        $this->logger->info('ORDER RETURN - ARAMEX ORDERED ITEMS ', $orderedItems);
                        
                        $this->returnOrder($order, $AramexOrder, $file);
                    }
                } else if(array_key_exists('infoLinkDocument', $AramexOrder)) {
                    $this->helper->log('------- Reading infolink file -------');
                    /* ----- Delivery & Return Codes Array ----- */
                    $deliveredCodes = array('SH007', 'SH154', 'SH234', 'SH005', 'SH006', 'SH496');
                    $toBeReturnedCodes = array('SH069', 'SH247', 'SH474', 'SH495');
                    /* ----- Delivery & Return Codes Array | Ends ----- */
                    $statusDelivered = false;
                    $statusToBeReturned = false;
                    if ($AramexOrder['infoLinkDocument']['HAWBUpdate']) {
                        $this->helper->log('--------- HAWBUpdate Available ---------');
                        $pinNumber = $AramexOrder['infoLinkDocument']['HAWBUpdate']['PINumber'];
                        $hawbNumberOfXml =  $AramexOrder['infoLinkDocument']['HAWBUpdate']['HAWBNumber'];
                        $this->helper->log('--------- pinNumber Available: '.$pinNumber.' ---------');
                        
                        /* ----- Checking pincode availability in the delivery & return array ----- */
                        if(in_array($pinNumber, $deliveredCodes)){
                            $statusDelivered = true;
                        } elseif (in_array($pinNumber, $toBeReturnedCodes)) {
                            $statusToBeReturned = true;
                        }
                        $this->helper->log('---- Result of $statusDelivered : '.$statusDelivered.' | Result of $statusToBeReturned: '.$statusToBeReturned.' ----');
                        /* ----- Checking pincode availability in the delivery & return array | Ends ----- */
                        
                        $shippedOrders = $this->helper->getShippedOrder();
                        foreach ($shippedOrders as $order) {
                            $awb = $order->getData('shipment_awb');
                            if ($awb !== null && $hawbNumberOfXml == $awb) {
                                $this->helper->log('---- Foreach : shippedOrders ----');
                                if($statusDelivered == true){
                                    $this->helper->log('---- Order Status Set To: '.SELF::ORDER_STATUS_DELIVERED.'  ----');
                                    $order->setStatus(SELF::ORDER_STATUS_DELIVERED);
                                    $success = true;
                                } else if($statusToBeReturned == true){
                                    $this->helper->log('---- Order Status Set To: '.SELF::ORDER_STATUS_TO_BE_RETURNED.'  ----');
                                    $order->setStatus(SELF::ORDER_STATUS_TO_BE_RETURNED);
                                    $success = true;
                                }
                                $order->save();
                                $this->helper->log('---- order has been saved----');
                                $this->helper->log('---- Value of success ----');
                                $this->helper->log($success);

                                if($success == true) {
                                   $hawbNumber = $AramexOrder['infoLinkDocument']['HAWBUpdate']['HAWBNumber'];
                                   rename($pathToFiles.$file, $hawbNumber);
                                   // $oldFile = $pathToFiles.$file;
                                   // $renamedFile = $pathToFiles.$hawbNumber.'.XML';
                                   // if(copy($oldFile, $renamedFile)){
                                   //  unlink($oldFile);
                                   //  $this->helper->log('---- File copied ----');
                                   // } else {
                                   //  $this->helper->log('---- File not copied ----');
                                   // } 
                                }
                                $this->helper->log('---- File has been renamed ----');
                            }
                        }
                    } else {
                        $this->helper->log('---- "HAWBUpdate" not found in the "infoLinkDocument" node. ----');
                    }
                } else {
                    $this->logger->err('ARAMEX INBOUND FORMAT FILE NOT MATCHING');
                }
                $this->helper->log('------------ File Ends ----------------');
                $this->helper->log(' ');
            }
        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }
    }

    public function allocateOrder($order, $AramexOrder)
    {
        $success = false;
        $comment = '';
        
        try {
            if($order->getStatus() == SELF::ORDER_STATUS_SUBMITTED_TO_WAREHOUSE) {
                $orderedItems = $AramexOrder["LINEITEM"];
                $qty_toCheck = 'QTYALLOCATED';
                $difference = $this->helper->checkOrderedItems($order->getItems(), $orderedItems, $qty_toCheck);
        
                if (count($difference) == 0) {
                    $comment = 'Order Updated to'.SELF::ORDER_STATUS_ALLOCATED;
                } else {
                    $comment = 'Order Updated to'.SELF::ORDER_STATUS_ALLOCATED.' but quantities received from Aramex are not matching.';
                    $this->helper->sendNotmatchingQuantitiesToAdmin(array($difference));
                }
                $order->setStatus(SELF::ORDER_STATUS_ALLOCATED);
                $success = true;
            }
            else if($order->getStatus() == SELF::ORDER_STATUS_ALLOCATED) {
                $comment = 'Warning: Order Allocation Message received again.';
            }
            else {
                $comment = 'Warning: Order Allocation message received although the order status is :'.$order->getStatus();
            }
            
            $order->addStatusHistoryComment($comment, false);
            $order->save();
            $historyDate = date('Y-m-d H:i:s');
            $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Confirmation', 'order Confirmation', $comment, $historyDate);
            
        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }

        return $success;
    }

    public function packOrder($order, $AramexOrder)
    {
        $success = false;
        $comment = '';
        
        try {
            if($order->getStatus() == SELF::ORDER_STATUS_SUBMITTED_TO_WAREHOUSE || $order->getStatus() == SELF::ORDER_STATUS_ALLOCATED) {
                $orderedItems = $AramexOrder["LINEITEM"];
                $qty_toCheck = 'QTYPICKED';
                $difference = $this->helper->checkOrderedItems($order->getItems(), $orderedItems, $qty_toCheck);
        
                if (count($difference) == 0) {
                    $comment = 'Order Updated to'.SELF::ORDER_STATUS_PACKED;
                } else {
                    $comment = 'Order Updated to'.SELF::ORDER_STATUS_PACKED.' but quantities received from Aramex are not matching.';
                    $this->helper->sendNotmatchingQuantitiesToAdmin(array($difference));
                }
                $order->setStatus(SELF::ORDER_STATUS_PACKED);
                $success = true;
            }
            else if($order->getStatus() == SELF::ORDER_STATUS_PACKED) {
                $comment = 'Warning: Order Packed Message received again.';
            }
            else {
                $comment = 'Warning: Order Packed message received although the order status is :'.$order->getStatus();
            }
            
            $order->addStatusHistoryComment($comment, false);
            $order->save();
            $historyDate = date('Y-m-d H:i:s');
            $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Confirmation', 'order Confirmation', $comment, $historyDate);
            
        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }

        return $success;
    }

    public function shippedOrder($order, $AramexOrder)
    {
        $success = false;
        $comment = '';

        try {
            if($order->getStatus() == SELF::ORDER_STATUS_SUBMITTED_TO_WAREHOUSE || $order->getStatus() == SELF::ORDER_STATUS_ALLOCATED || $order->getStatus() == SELF::ORDER_STATUS_PACKED) {
                $this->logger->info('LOADED ORDER ITEMS ORDERED', array($order->getItems()));
                $qty_toCheck = 'QTYSHIPPED';
                $difference = $this->helper->checkOrderedItems($order->getItems(), $AramexOrder["LINEITEM"], $qty_toCheck);
                
                $this->helper->log('COUNT DIFFERENCE BETWEEN PRODUCTS ' . count($difference));
                if (count($difference) == 0) {
                    $comment = 'Order shipped with AWB number ' . $AramexOrder['AWB'];                    
                } else {
                    $comment = 'Order shipped with AWB number ' . $AramexOrder['AWB']. ' but quanties are not matching';
                    $this->helper->sendNotmatchingQuantitiesToAdmin(array($difference));
                }

                $this->helper->createShipment($order, $AramexOrder['AWB']);
                $order->setData('shipment_awb', $AramexOrder['AWB']);
                $order->setData('status', SELF::ORDER_STATUS_SHIPPED);  
                $success = true;
            }
            else if($order->getStatus() == SELF::ORDER_STATUS_SHIPPED) {
                $comment = 'Warning: Order Shipment Message received again.';
            }
            else {
                $comment = 'Warning: Order Shipment message received although the order status is :'.$order->getStatus();
            }
        
            $history = $order->addStatusHistoryComment($comment, false);
            $history->setIsCustomerNotified(false);
            $order->save();

            $historyDate = date('Y-m-d H:i:s');
            $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Shipping', 'order Shipping', $comment, $historyDate);

        } catch (\Exception $exception) {
            $this->logger->err($exception->getMessage());
        }

        return $success;
    }

    public function confirmOrder($AramexOrder, $file)
    {
        $this->helper->log(__METHOD__, true);

        $pathToFiles = $this->helper->getReceivedFolderPath().self::INBOX_RECEIVED;
        $pathToSuccessFiles = $this->helper->getReceivedFolderPath().self::INBOX_SUCCESS;
        $pathToFailedFiles = $this->helper->getReceivedFolderPath().self::INBOX_FAILED;

        try {
            $success = false;
            
            $orders = $this->collectionFactory->create();
            $order = $orders->addFieldToFilter('increment_id', $AramexOrder['INVOICENB'])->getFirstItem();

            if ($order->getData()) {
                $comment = '';
                $this->logger->info('ORDER CONFIRMATION CRON :: LOADED ORDER DATA', array($order->getData()));

                switch ($AramexOrder['STATUS']) {
                    case SELF::ARAMEX_ORDER_STATUS_ALLOCATED:
                    case SELF::ARAMEX_ORDER_STATUS_RELEASED:
                        $success = $this->allocateOrder($order, $AramexOrder);
                    break;
                    case SELF::ARAMEX_ORDER_STATUS_PACKED:
                        $success = $this->packOrder($order, $AramexOrder);
		    break;
                    case SELF::ARAMEX_ORDER_STATUS_SHIPPED:
                        $success = $this->shippedOrder($order, $AramexOrder);
                      break;
                    default:
                        $this->logger->error('ORDER CONFIRMATION CRON:: Invalid Status received from Aramex => '.$AramexOrder['STATUS']);
                    }
                    
                if($success == true) {
                    rename($pathToFiles.$file, $pathToSuccessFiles. 'CONFIRMATION_'.$file);
                }
                else {
                    rename($pathToFiles.$file, $pathToFailedFiles. 'CONFIRMATION_'.$file);
                }                
            } else {
                $this->logger->error('ORDER CONFIRMATION CRON:: ORDER DOES NOT EXIST WITH INCREMENT ID ' . $AramexOrder['INVOICENB']);
            }

        } catch (NoSuchEntityException $ex) {
            throw $ex;
        }

    }

    /**
     *
     */
    public function DeliveryStoreOrders()
    {

        $this->helper->log(__METHOD__, true);

        $deliveredCodes = array('SH007', 'SH154', 'SH234', 'SH005', 'SH006', 'SH496');
        $toBeReturnedCodes = array('SH069', 'SH247', 'SH474', 'SH495');
        $shippedOrders = $this->helper->getShippedOrder();
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

            $this->logger->info("ORDER DELIVERY CRON :: CALL SHIPMENT TRACKING API REQUEST", array("ClientInfo" => $clientInfo, 'GetLastTrackingUpdateOnly' => true));


            foreach ($shippedOrders as $order) {

                $order->save();
                $awb = $order->getData('shipment_awb');
		$comment = "";

                $this->logger->info("ORDER DELIVERY CRON :: SENT AWB TO SHIPMENT TRACKING API REQUEST", array($order->getData('shipment_awb')));
                if ($awb !== null) {

                    /* Call shipment Tracking API */
                    $response = $this->helper->callShipmentService(
                        $url,
                        self::SHIPMENT_TRACKING_REQUEST,
                        array("ShipmentTrackingRequest" => array('ClientInfo' => $clientInfo, 'Shipments' => array($awb), 'GetLastTrackingUpdateOnly' => true))

                    );

                    $this->logger->info("ORDER DELIVERY CRON:: CALL SHIPMENT TRACKING API RESPONSE", array($response));
                    if ($response != false) {

                        $result = json_decode(json_encode($response), true);
                     //   if ($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']) {
			if (
                            isset($result['TrackingResults']) == true &&
                            isset($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']) == true &&
                            isset($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']) == true &&
                            isset($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']) == true
                        ) {
                            $trackingResult = $result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult'];
                            $this->logger->info("ORDER DELIVERY CRON::  CALL SHIPMENT TRACKING API RESPONSE", array($result['TrackingResults']['KeyValueOfstringArrayOfTrackingResultmFAkxlpY']['Value']['TrackingResult']));
                            $this->logger->info("ORDER DELIVERY CRON::  Received Update Code " . $trackingResult["UpdateCode"]);

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
                    } else {
                        $comment = "Error occured when receiving response from Aramex shipment tracking Api";
                        $this->logger->info("ORDER DELIVERY CRON:: ERROR IN SHIPMENT TRACKING API RESPONSE", array($response));
                    }
                    $this->logger->info("ORDER DELIVERY CRON:: NEW ORDER STATUS" . $order->getStatus());

                    $historyDate = $historyDate = date('Y-m-d H:i:s');
                    $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'callTrackShipments', 'order Delivery', $comment, $historyDate);
                }
            }
        } catch (\Exception $ex) {
            $this->helper->log($ex);
        }
    }


    public function returnOrder($order, $AramexOrder, $file)
    {

        $this->helper->log(__METHOD__, true);
        $pathToFiles = $this->helper->getReceivedFolderPath().self::INBOX_RECEIVED;
        $pathToSuccessFiles = $this->helper->getReceivedFolderPath().self::INBOX_SUCCESS;
        $pathToFailedFiles = $this->helper->getReceivedFolderPath().self::INBOX_FAILED;

        $success = false;

        if ($order->getData() != null) {
            $this->logger->info('LOADED ORDER DATA', array($order->getData()));
            $comment = '';

            if($order->getStatus() == SELF::ORDER_STATUS_TO_BE_RETURNED)
            {
                $this->logger->info('LOADED ORDER ITEMS ORDERED', array($order->getItems()));
                $qty_toCheck = 'QTYEXPECTED';
                $difference = $this->helper->checkOrderedItems($order->getItems(), $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']["LINEITEM"], $qty_toCheck);

                if (count($difference) == 0) {
                    $comment = '#Order returned from ' . $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['ARAMEXREF'];
                    $this->logger->info('NEW ORDER STATUS ', array($order->getStatus()));
                } else {
                    $this->helper->log('QUANTITIES DOES NOT MATCH  ');
                    $comment = '#Error occured when trying to update order status  # Quantities received from Aramex does not match';
                    $this->helper->sendNotmatchingQuantitiesToAdmin($difference);
                }
                

                $order->setData('state', self::ORDER_STATE_CANCELED);
                $order->setData('status', SELF::ORDER_STATUS_RETURNED);
                $success = true;
            }
            else if ($order->getStatus() == SELF::ORDER_STATUS_RETURNED) {
                $comment = 'Another Order Return confirmation is received';
            }
            else {
                $comment = 'Warning: Order ASN message received although the order status is :'.$order->getStatus();
            }
            $history = $order->addStatusHistoryComment($comment, false);
            $history->setIsCustomerNotified(false);
            $order->save();

            $historyDate = date('Y-m-d H:i:s');
            $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Return', 'order Return', $comment, $historyDate);

            if($success == true) {
                rename($pathToFiles.$file, $pathToSuccessFiles. 'ASNCONF_'.$file);
            }
            else {
                rename($pathToFiles.$file, $pathToFailedFiles. 'ASNCONF_'.$file);
            }

        } else {
            $this->logger->error('ORDER DOES NOT EXIST WITH INCREMENT ID ' . $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF']);
        }
    }

    /*  NOT USED */
    public function returnOrderOLD()
    {
        $this->helper->log(__METHOD__, true);
        $pathToFiles = $this->helper->getReceivedFolderPath().self::INBOX_RECEIVED;
        $pathToSuccessFiles = $this->helper->getReceivedFolderPath().self::INBOX_SUCCESS;
        $pathToFailedFiles = $this->helper->getReceivedFolderPath().self::INBOX_FAILED;
        $success = false;

        try {
            $files = $this->helper->getAllFiles($pathToFiles);
            foreach ($files as $file) {

                $AramexOrder = $this->helper->readXMLFile($pathToFiles . $file);
                $success = false;

                if (array_key_exists('ADVANCEDSHIPPINGNOTICECONF', $AramexOrder)) {

                    if ($AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF']) {
                        $this->helper->log('ORDER TO RETURN WITH INCREMENT ID ' . $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF']);

                        /************** Get Invoice data by invoice id in xml ********/
                        $orders = $this->collectionFactory->create();
                        $order = $orders->addFieldToFilter('increment_id', $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF'])
                            ->getFirstItem();

                        $orderedItems = $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']["LINEITEM"];

                        $this->logger->info('ORDER RETURN - ARAMEX ORDERED ITEMS ', $orderedItems);

                        if ($order->getData() != null) {
                            $this->logger->info('LOADED ORDER DATA', array($order->getData()));
                            $comment = '';

                            if($order->getStatus() == SELF::ORDER_STATUS_TO_BE_RETURNED)
                            {
                                $this->logger->info('LOADED ORDER ITEMS ORDERED', array($order->getItems()));
                                $qty_toCheck = 'QTYEXPECTED';
                                $difference = $this->helper->checkOrderedItems($order->getItems(), $orderedItems, $qty_toCheck);

                                if (count($difference) == 0) {
                                    $comment = '#Order returned from ' . $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['ARAMEXREF'];
                                    $this->logger->info('NEW ORDER STATUS ', array($order->getStatus()));
                                } else {
                                    $this->helper->log('QUANTITIES DOES NOT MATCH  ');
                                    $comment = '#Error occured when trying to update order status  # Quantities received from Aramex does not match';
                                    $this->helper->sendNotmatchingQuantitiesToAdmin($difference);
                                }
                                $success = true;
                            }
                            else if ($order->getStatus() == SELF::ORDER_STATUS_RETURNED) {
                                $comment = 'Another Order Return confirmation is received';
                            }

                            $order->setData('state', self::ORDER_STATE_CANCELED);
                            $order->setData('status', SELF::ORDER_STATUS_RETURNED);
                            $history = $order->addStatusHistoryComment($comment, false);
                            $history->setIsCustomerNotified(false);
                            $order->save();
                            rename($pathToFiles.$file, $pathToSuccessFiles. $file);

                            $historyDate = date('Y-m-d H:i:s');
                            $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Return', 'order Return', $comment, $historyDate);

                            if($success == true) {
                                rename($pathToFiles.$file, $pathToSuccessFiles. $file);
                            }
                            else {
                                rename($pathToFiles.$file, $pathToFailedFiles. $file);
                            }

                        } else {
                            $this->logger->error('ORDER DOES NOT EXIST WITH INCREMENT ID ' . $AramexOrder['ADVANCEDSHIPPINGNOTICECONF']['MASTERREF']);
                        }
                    }
                }
            }

        } catch (NoSuchEntityException $ex) {
            throw $ex;
        }
    }


    /** NOT USED **/
    public function ShipOrder($AramexOrder, $file)
    {
        $this->helper->log(__METHOD__, true);

        $pathToFiles = $this->helper->getReceivedFolderPath().self::INBOX_RECEIVED;
        $pathToSuccessFiles = $this->helper->getReceivedFolderPath().self::INBOX_SUCCESS;
        $pathToFailedFiles = $this->helper->getReceivedFolderPath().self::INBOX_FAILED;
        $success = false;

        try {
            /************** Get Invoice data by invoice id in xml ********/
            $orders = $this->collectionFactory->create();
            $order = $orders->addFieldToFilter('increment_id', $AramexOrder['INVOICENB'])
                ->getFirstItem();

            $orderedItems = $AramexOrder["LINEITEM"];

            $this->logger->info('ARAMEX ORDERED ITEMS ', $orderedItems);

            if ($order->getData() != null) {
                $this->logger->info('LOADED ORDER DATA', array($order->getData()));
                $comment = '';
                if ($order->getStatus() == SELF::ORDER_STATUS_ALLOCATED && $AramexOrder['STATUS'] == SELF::ARAMEX_ORDER_STATUS_SHIPPED) {

                    $this->logger->info('LOADED ORDER ITEMS ORDERED', array($order->getItems()));
                    $qty_toCheck = 'QTYSHIPPED';
                    $difference = $this->helper->checkOrderedItems($order->getItems(), $orderedItems, $qty_toCheck);
                    $this->helper->log('COUNT DIFFERENCE BETWEEN PRODUCTS ' . count($difference));
                    if (count($difference) == 0) {
                        $comment = '#Order shipped with  AWB number ' . $AramexOrder['AWB'];
                        $this->logger->info('NEW ORDER STATUS ', array($order->getStatus()));
                        
                    } else {
                        $this->logger->info('QUANTITIES DOES NOT MATCH  ');
                        $comment = '#Error occured when trying to update order status  # Quantities received from Aramex does not match';
                        $this->helper->sendNotmatchingQuantitiesToAdmin(array($difference));
                    }

                    $this->helper->createShipment($order, $AramexOrder['AWB']);
                    $order->setData('shipment_awb', $AramexOrder['AWB']);
                    $order->setData('status', SELF::ORDER_STATUS_SHIPPED);
                    $success = true;
                }
                else if ($order->getStatus() == SELF::ORDER_STATUS_SHIPPED) {
                    $comment = 'Another Order Shipped Complete confirmation is received';
                }
                 else {
                    $comment = '#Error in Order shipment Cron Order status is ' . $order->getData('status') . ' It does not match with Aramex received Status #' . $AramexOrder['STATUS'];
                    $this->helper->log('#Error in Order shipment Cron Order status is ' . $order->getData('status') . ' It does not match with Aramex received Status #' . $AramexOrder['STATUS']);
                }
                $history = $order->addStatusHistoryComment($comment, false);
                $history->setIsCustomerNotified(false);
                $order->save();
                /******** add Order history log ********/
                $historyDate = date('Y-m-d H:i:s');
                $this->orderLog->addRecord($order->getData('entity_id'), $order->getData('status'), 'order Shipping', 'order Shipping', $comment, $historyDate);

                if($success == true) {
                    rename($pathToFiles.$file, $pathToSuccessFiles. $file);
                }
                else {
                    rename($pathToFiles.$file, $pathToFailedFiles. $file);
                }

            } else {
                $this->logger->error('ORDER DOES NOT EXIST WITH INCREMENT ID ' . $AramexOrder['INVOICENB']);
            }

        } catch (NoSuchEntityException $ex) {
            throw $ex;
        }

    }
}


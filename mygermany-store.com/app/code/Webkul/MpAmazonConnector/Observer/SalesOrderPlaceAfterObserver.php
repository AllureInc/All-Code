<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    private $amzClient;

    /**
     * @var \Webkul\MpAmazonConnector\Model\Productmap
     */
    private $productMapRecord;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Data
     */
    private $helper;

    /**
     * @var \Webkul\MpAmazonConnector\Logger\Logger
     */
    private $amzLogger;

    /**
     * @param \Webkul\MpAmazonConnector\Logger\Logger $amzLogger,
     * @param \Webkul\MpAmazonConnector\Model\Productmap $productMapRecord,
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
     */
    public function __construct(
        \Webkul\MpAmazonConnector\Logger\Logger $amzLogger,
        \Webkul\MpAmazonConnector\Model\ProductMap $productMapRecord,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Webkul\MpAmazonConnector\Helper\ProductOnAmazon $productOnAmazon,
        \Magento\Catalog\Model\Product $product,
        SessionManager $coreSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->amzLogger = $amzLogger;
        $this->productMapRecord = $productMapRecord;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        $this->product = $product;
        $this->coreSession = $coreSession;
        $this->objectManager = $objectManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {

        try {
            $wholeItemData = [];
            if ($this->helper->validateSessionVals()) {
                $order = $observer->getOrder();
                $orderIncrementedId = $order->getIncrementId();
                $orderItems = $order->getAllItems();
                foreach ($orderItems as $item) {
                    $productId = $item->getProductId();
                    $this->amzLogger->info('observer SalesOrderPlaceAfterObserver 
                                    : Order quatity with product id - '.$productId);
                    $amzMappedProduct = $this->productMapRecord
                                        ->getCollection()
                                        ->addFieldToFilter('magento_pro_id', ['eq'=>$productId]);
                    if ($id = $this->helper->getById($amzMappedProduct)) {
                        $this->amzClient = $this->helper->getAmzClient($id);
                        $product = $this->product->load($productId);
                        $updateQtyData = $this->productOnAmazon->updateQtyData($product);
                        $wholeItemData = $wholeItemData + $updateQtyData;
                    }
                }
                if (!empty($wholeItemData)) {
                    $stockApiResponse = $this->amzClient->updateStock($wholeItemData);
                    $this->amzLogger->info('== Observer SalesOrderPlaceAfterObserver stockApiResponse ==');
                    $this->amzLogger->info(json_encode($stockApiResponse));
                }
            }
        } catch (\Exception $e) {
            $this->amzLogger->info('observer SalesOrderPlaceAfterObserver : '.$e->getMessage());
        }
    }
}

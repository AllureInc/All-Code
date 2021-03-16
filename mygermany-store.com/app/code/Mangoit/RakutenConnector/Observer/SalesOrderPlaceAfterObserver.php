<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;

class SalesOrderPlaceAfterObserver implements ObserverInterface
{
    private $rktnClient;

    /**
     * @var \Mangoit\RakutenConnector\Model\Productmap
     */
    private $productMapRecord;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Data
     */
    private $helper;

    /**
     * @var \Mangoit\RakutenConnector\Logger\Logger
     */
    private $amzLogger;

    /**
     * @param \Mangoit\RakutenConnector\Logger\Logger $amzLogger,
     * @param \Mangoit\RakutenConnector\Model\Productmap $productMapRecord,
     * @param \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
     */
    public function __construct(
        \Mangoit\RakutenConnector\Logger\Logger $amzLogger,
        \Mangoit\RakutenConnector\Model\ProductMap $productMapRecord,
        \Magento\CatalogInventory\Model\Stock\StockItemRepository $stockItemRepository,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Mangoit\RakutenConnector\Helper\ProductOnRakuten $productOnRakuten,
        \Magento\Catalog\Model\Product $product,
        SessionManager $coreSession,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->amzLogger = $amzLogger;
        $this->productMapRecord = $productMapRecord;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->productOnRakuten = $productOnRakuten;
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
                        $this->rktnClient = $this->helper->getRktnClient($id);
                        $product = $this->product->load($productId);
                        $updateQtyData = $this->productOnRakuten->updateQtyData($product);
                        $wholeItemData = $wholeItemData + $updateQtyData;
                    }
                }
                if (!empty($wholeItemData)) {
                    $stockApiResponse = $this->rktnClient->updateStock($wholeItemData);
                    $this->amzLogger->info('== Observer SalesOrderPlaceAfterObserver stockApiResponse ==');
                    $this->amzLogger->info(json_encode($stockApiResponse));
                }
            }
        } catch (\Exception $e) {
            $this->amzLogger->info('observer SalesOrderPlaceAfterObserver : '.$e->getMessage());
        }
    }
}

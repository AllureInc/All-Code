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
use Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

class CatalogProductSaveAfter implements ObserverInterface
{
    private $amzClient;

    /**
     * @var \Webkul\MpAmazonConnector\Model\Productmap
     */
    private $productMap;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * \Webkul\MpAmazonConnector\Helper\ProductOnAmazon
     */
    private $productOnAmazon;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     *
     * @param \Webkul\MpAmazonConnector\Logger\Logger $amzLogger
     * @param \Webkul\MpAmazonConnector\Model\ProductMap $productMap
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
     * @param \Webkul\MpAmazonConnector\Helper\Data $helper
     * @param \Webkul\MpAmazonConnector\Helper\ProductOnAmazon $productOnAmazon
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        \Webkul\MpAmazonConnector\Logger\Logger $amzLogger,
        \Webkul\MpAmazonConnector\Model\ProductMap $productMap,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Webkul\MpAmazonConnector\Helper\ProductOnAmazon $productOnAmazon,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession
    ) {
        $this->logger = $amzLogger;
        $this->productMap = $productMap;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->productOnAmazon = $productOnAmazon;
        $this->product = $product;
        $this->objectManager = $objectManager;
        $this->coreSession = $coreSession;
    }
    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
            if ($this->helper->validateSessionVals()) {
                $product = $observer->getProduct();
                $amzMappedProduct = $this->productMap->getCollection()
                                ->addFieldToFilter('magento_pro_id', ['eq'=>$product->getId()]);
                
                if ($id = $this->helper->getById($amzMappedProduct)) {
                    $product = $this->product->load($product->getId());
                    $this->amzClient = $this->helper->getAmzClient($id);
                    if ($this->helper->config['revise_item']) {
                        //Update qty of amazon product
                        $updateQtyData = $this->productOnAmazon->updateQtyData($product);
        
                        //Update price of amazon product
                        $updatePriceData = $this->productOnAmazon->updatePriceData($product);
        
                        if (!empty($updateQtyData) && !empty($updatePriceData)) {
                            $this->_upateProductData($updateQtyData, $updatePriceData);
                        }
                    }
                }
            }
        } catch (\Execption $e) {
            $this->logger->info('Observer CatalogProductSaveAfter execute : '.$e->getMessage());
        }
    }

    /**
     * update amazon  product
     * @param  array $updateQtyData
     * @param  array $updatePriceData
     * @param  object $product
     */
    private function _upateProductData($updateQtyData, $updatePriceData)
    {
        try {
            $priceApiResponse = $this->amzClient->updatePrice($updatePriceData);
            $this->logger->info('== Observer CatalogProductSaveAfter priceApiResponse ==');
            $this->logger->info(json_encode($priceApiResponse));

            $stockApiResponse = $this->amzClient->updateStock($updateQtyData);
            $this->logger->info('== Observer CatalogProductSaveAfter stockApiResponse ==');
            $this->logger->info(json_encode($stockApiResponse));
        } catch (\Execption $e) {
            $this->logger->info('Observer CatalogProductSaveAfter _upateProductData : '.$e->getMessage());
        }
    }
}

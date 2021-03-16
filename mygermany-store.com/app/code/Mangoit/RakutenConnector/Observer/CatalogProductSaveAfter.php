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
use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

class CatalogProductSaveAfter implements ObserverInterface
{
    private $rktnClient;

    /**
     * @var \Mangoit\RakutenConnector\Model\Productmap
     */
    private $productMap;

    /**
     * @var \Magento\CatalogInventory\Model\Stock\StockItemRepository
     */
    private $stockItemRepository;

    /**
     * \Mangoit\RakutenConnector\Helper\ProductOnRakuten
     */
    private $productOnRakuten;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     *
     * @param \Mangoit\RakutenConnector\Logger\Logger $amzLogger
     * @param \Mangoit\RakutenConnector\Model\ProductMap $productMap
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository
     * @param \Mangoit\RakutenConnector\Helper\Data $helper
     * @param \Mangoit\RakutenConnector\Helper\ProductOnRakuten $productOnRakuten
     * @param \Magento\Catalog\Model\Product $product
     */
    public function __construct(
        \Mangoit\RakutenConnector\Logger\Logger $amzLogger,
        \Mangoit\RakutenConnector\Model\ProductMap $productMap,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockItemRepository,
        \Mangoit\RakutenConnector\Helper\Data $helper,
        \Mangoit\RakutenConnector\Helper\ProductOnRakuten $productOnRakuten,
        \Magento\Catalog\Model\Product $product,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        SessionManager $coreSession
    ) {
        $this->logger = $amzLogger;
        $this->productMap = $productMap;
        $this->stockItemRepository = $stockItemRepository;
        $this->helper = $helper;
        $this->productOnRakuten = $productOnRakuten;
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
                    $this->rktnClient = $this->helper->getRktnClient($id);
                    if ($this->helper->config['revise_item']) {
                        //Update qty of amazon product
                        $updateQtyData = $this->productOnRakuten->updateQtyData($product);
        
                        //Update price of amazon product
                        $updatePriceData = $this->productOnRakuten->updatePriceData($product);
        
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
            $priceApiResponse = $this->rktnClient->updatePrice($updatePriceData);
            $this->logger->info('== Observer CatalogProductSaveAfter priceApiResponse ==');
            $this->logger->info(json_encode($priceApiResponse));

            $stockApiResponse = $this->rktnClient->updateStock($updateQtyData);
            $this->logger->info('== Observer CatalogProductSaveAfter stockApiResponse ==');
            $this->logger->info(json_encode($stockApiResponse));
        } catch (\Execption $e) {
            $this->logger->info('Observer CatalogProductSaveAfter _upateProductData : '.$e->getMessage());
        }
    }
}

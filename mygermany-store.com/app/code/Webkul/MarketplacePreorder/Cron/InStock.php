<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MarketplacePreorder\Cron;

use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Set Products in IN Stock.
 */
class InStock
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @var ItemFactory
     */
    protected $stockItemFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;

    /**
     *
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var PreorderItemsCollection
     */
    protected $_preorderItemCollection;

    /**
     * @var Items
     */
    protected $_itemsRepository;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var PreorderItemsInterfaceFactory  */

    protected $_preorderItemsFactory;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Email
     */
    protected $_emailHelper;

    /**
     *
     * @param ItemFactory                                                    $stockItemFactory
     * @param \Webkul\MarketplacePreorder\Helper\Data                        $preorderHelper
     * @param \Magento\Config\Model\ResourceModel\Config                     $resourceConfig
     * @param \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param \Magento\Framework\Stdlib\DateTime\DateTime                    $date
     * @param \Magento\Framework\App\ResourceConnection                      $resource
     * @param \Magento\Catalog\Api\ProductRepositoryInterface                $productRepository
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface           $stockRegistry
     * @param PreorderItemsCollection                                        $preorderItemCollection
     * @param ItemsRepository                                                $itemsRepository
     * @param PreorderItemsInterfaceFactory                                  $preorderItemsFactory
     * @param DataObjectHelper                                               $dataObjectHelper
     * @param \Webkul\MarketplacePreorder\Helper\Email                       $emailHelper
     * @param \Psr\Log\LoggerInterface                                       $logger
     */
    public function __construct(
        ItemFactory $stockItemFactory,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        PreorderItemsCollection $preorderItemCollection,
        ItemsRepository $itemsRepository,
        PreorderItemsInterfaceFactory $preorderItemsFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\MarketplacePreorder\Helper\Email $emailHelper,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->stockItemFactory = $stockItemFactory;
        $this->_preorderHelper = $preorderHelper;
        $this->_resourceConfig = $resourceConfig;
        $this->_productCollectionFactory = $productCollection;
        $this->_date = $date;
        $this->_resource = $resource;
        $this->_productRepository = $productRepository;
        $this->_stockRegistry = $stockRegistry;
        $this->logger = $logger;
        $this->_preorderItemCollection = $preorderItemCollection;
        $this->_itemsRepository = $itemsRepository;
        $this->_preorderItemsFactory = $preorderItemsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_emailHelper = $emailHelper;
    }

    /**
     *
     * update product stock and notify preorder buyers.
     */
    public function execute()
    {
        $emailArray = [];
        $notifyArray = [];
        try {
            $helper = $this->_preorderHelper;
            $stockDetails = $this->getStockDetails();
            $model = $this->_productCollectionFactory->create()
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $stockDetails['product_id']]
                )->addFieldToFilter('wk_marketplace_availability', ['nin' => ['', null]]);
            foreach ($model as $value) {
                $_product = $helper->getProduct($value->getId());
                $availabilityDate = $_product->getWkMarketplaceAvailability();
                $preorderDate = strtotime($availabilityDate);
                $date = $this->_date->gmtDate();
                $currentDate = strtotime($date);
                $sellerId = $helper->getSellerIdByProductId($_product->getId());
                /*if ($preorderDate <= $currentDate) {*/
                    $this->logger->debug($_product->getSku());
                    $this->logger->debug('cron is running');
                    $sku = $_product->getSku();
                    $stockItem = $this->_stockRegistry->getStockItem($_product->getId());
                    $stockItem->setData('is_in_stock', 1);

                    $collection = $this->_preorderItemCollection->create()
                        ->addFieldToFilter('status', ['eq' => 0])
                        ->addFieldToFilter('notify', ['eq' => 0])
                        ->addFieldToFilter('product_id', ['eq' => $_product->getId()]);

                    foreach ($collection as $item) {
                        $emailArray[] = $item->getCustomerEmail();
                        $notifyArray[] = $item->getId();
                        
                        if ($helper->getSellerPreorderSpecification($item->getSellerId())) {
                            $helper->setProductDisabled($_product->getId(), $_product->getStoreId());
                        } else {
                            $helper->setProductEnabled($_product->getId(), $_product->getStoreId());
                        }
                    }

                    $this->_stockRegistry->updateStockItemBySku($sku, $stockItem);

                    $emailArray = array_unique($emailArray);
                    $notifyArray = array_unique($notifyArray);
                    if ($this->_emailHelper->isAutoEmail($sellerId)) {
                        $this->_emailHelper->sendNotifyEmail($emailArray, $_product->getName());
                        foreach ($notifyArray as $temId) {
                            $updateData = [
                                    'notify' => 1
                                ];
                            $this->updatePreorderItem($temId, $updateData);
                        }
                    }
                /*}*/
            }
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Update Preorder Items to notify buyers
     * @param  int $itemId
     * @param  array $updatedData
     */
    public function updatePreorderItem($itemId, $updatedData)
    {
        $itemData = $this->_itemsRepository->getById($itemId);
        $savedData = (array) $itemData->getData();

        $itemsDataObject = $this->_preorderItemsFactory->create();

        $mergeData = array_merge(
            $savedData,
            $updatedData
        );
        
        $mergeData['id'] = $itemId;
        $this->dataObjectHelper->populateWithArray(
            $itemsDataObject,
            $mergeData,
            '\Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
        );
        try {
            $this->_itemsRepository->save($itemsDataObject);
        } catch (\Exception $e) {
            $this->logger->debug($e->getMessage());
        }
    }

    /**
     * Get Stock Details of Product.
     *
     * @param int $productId
     *
     * @return array
     */
    public function getStockDetails()
    {
        $connection = $this->_resource->getConnection();
        $stockDetails = ['is_in_stock' => 0, 'qty' => 0];
        $collection = $this->_productCollectionFactory
                            ->create()
                            ->addAttributeToSelect('name');
        $table = $connection->getTableName('cataloginventory_stock_item');
        $bind = 'product_id = entity_id';
        $cond = '{{table}}.stock_id = 1';
        $type = 'left';
        $alias = 'is_in_stock';
        $field = 'is_in_stock';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $alias = 'qty';
        $field = 'qty';
        $collection->joinField($alias, $table, $field, $bind, $cond, $type);
        $collection->addFieldToFilter('is_in_stock', 0);
        foreach ($collection as $value) {
            $stockDetails['name'][] = $value->getName();
            $stockDetails['product_id'][] = $value->getEntityId();
        }
        return $stockDetails;
    }
}

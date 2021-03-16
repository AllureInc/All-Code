<?php
namespace Mangoit\Marketplace\Model;

use Magento\CatalogInventory\Model\Stock\ItemFactory;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Set Products in IN Stock.
 */
class InStock extends \Webkul\MarketplacePreorder\Cron\InStock
{
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
        \Psr\Log\LoggerInterface $logger,
        \Mangoit\Marketplace\Helper\Email $misEmailHelper
    ) {

        parent::__construct($stockItemFactory,
            $preorderHelper,
            $resourceConfig,
            $productCollection,
            $date,
            $resource,
            $productRepository,
            $stockRegistry,
            $preorderItemCollection,
            $itemsRepository,
            $preorderItemsFactory,
            $dataObjectHelper,
            $emailHelper,
            $logger
        );
        $this->_emailHelper = $misEmailHelper;
    }
}
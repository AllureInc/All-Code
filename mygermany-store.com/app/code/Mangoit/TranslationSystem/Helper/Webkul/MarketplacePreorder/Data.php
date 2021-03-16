<?php

namespace Mangoit\TranslationSystem\Helper\Webkul\MarketplacePreorder;

use Magento\Framework\App\Action\Action;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;
use Webkul\MarketplacePreorder\Api\PreorderItemsRepositoryInterface;
use Webkul\MarketplacePreorder\Api\PreorderSellerRepositoryInterface;
use Webkul\MarketplacePreorder\Api\PreorderCompleteRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Area;

// use Magento\Store\Model\StoreManagerInterface;

class Data extends \Webkul\MarketplacePreorder\Helper\Data
{
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Catalog\Model\ProductFactory $product,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\Currency $currency,
        \Magento\Framework\Locale\CurrencyInterface $localeCurrency,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection,
        \Magento\Catalog\Model\Product\OptionFactory $option,
        \Magento\Framework\Stdlib\DateTime\Timezone $localeResolver,
        \Magento\Sales\Model\OrderFactory $order,
        \Webkul\Marketplace\Model\ProductFactory $marketplaceProduct,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\MarketplacePreorder\Model\Source\PreorderType $preorderType,
        \Webkul\MarketplacePreorder\Model\Source\PreorderAction $preorderAction,
        \Webkul\MarketplacePreorder\Model\Source\PreorderEamil $preorderEmail,
        \Webkul\MarketplacePreorder\Model\Source\PreorderQty $preorderQty,
        \Webkul\MarketplacePreorder\Model\Source\PreorderSpecification $preorderSpecification,
        Configurable $configurable,
        PreorderItemsCollection $preorderItemsCollectionFactory,
        PreorderSellerCollection $preorderSellerCollectionFactory,
        PreorderSellerRepositoryInterface $sellerRepository,
        PreorderItemsRepositoryInterface $itemsRepository,
        PreorderCompleteRepositoryInterface $completeRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        Attribute $eavEntity
    ) {
        $this->storeManager = $storeManager;

        parent::__construct(
                $context,
                $objectManager,
                $customerSession,
                $product,
                $storeManager,
                $currency,
                $localeCurrency,
                $filesystem,
                $resource,
                $productCollection,
                $option,
                $localeResolver,
                $order,
                $marketplaceProduct,
                $marketplaceHelper,
                $preorderType,
                $preorderAction,
                $preorderEmail,
                $preorderQty,
                $preorderSpecification,
                $configurable,
                $preorderItemsCollectionFactory,
                $preorderSellerCollectionFactory,
                $sellerRepository,
                $itemsRepository,
                $completeRepository,
                $searchCriteriaBuilder,
                $eavEntity
            );
    }
    /**
     * [getSellerConfiguration] get seller preorder configuration.
     * @param [integer] $sellerId [contains logged in seller Id]
     * @return [object] [returns configuration collection for that seller]
     */
    public function getSellerConfiguration($sellerId = 0)
    {
        if (!$sellerId) {
            $sellerId =  $this->getCustomerId();
        }
        $returnArr = $this->_sellerCollectionFactory->create()
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->getFirstItem();
        $custom_message = '';
        if($returnArr->getCustomMessage()) {
            $customMsgs = unserialize($returnArr->getCustomMessage());
            $storeCode = $this->storeManager->getStore()->getCode();
            $custom_message = isset($customMsgs[$storeCode]) ? $customMsgs[$storeCode] : $customMsgs[0];
        }
        $returnArr->setCustomMessage($custom_message);

        return $returnArr;
    }
}

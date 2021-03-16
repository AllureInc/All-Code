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
namespace Webkul\MarketplacePreorder\Helper;

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

/**
 * MarketplacePreorder Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
        /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var null|array
     */
    protected $_options;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PreorderItemCollection
     */
    protected $_itemsCollectionFactory;

    /**
     * @var PreorderSellerCollection
     */
    protected $_sellerCollectionFactory;

    /**
     * @var Webkul\MarketplacePreorder\Model\Sourcer\PreorderAction
     */
    protected $_preorderType;

    /**
     * @var Webkul\MarketplacePreorder\Model\Sourcer\PreorderType
     */
    protected $_preorderAction;

    /**
     * @var Webkul\MarketplacePreorder\Model\Sourcer\preorderEmail
     */
    protected $_preorderEmail;

    /**
     * @var Webkul\MarketplacePreorder\Model\Sourcer\PreorderQty
    */
    protected $_preorderQty;

    /**
     * @var Webkul\MarketplacePreorder\Model\Sourcer\PreorderSpecification
    */
    protected $_preorderSpecification;

    /**
     * @var AttributeFactory
     */
    protected $_eavEntity;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var \Webkul\Marketplace\Model\ProductFactory
     */
    protected $_marketplaceProductFactory;

    /**
     * @var \Magento\Framework\App\Helper\Context
     */
    protected $_request;

    /**
     * @var Configurable
     */
    protected $_configurable;

    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    /**
     * @var PreorderSellerRepositoryInterface
     */
    protected $_sellerRepository;

    /**
     * @var PreorderItemsRepositoryInterface
     */
    protected $_itemsRepository;
    /**
     * @var PreorderCompleteRepositoryInterface
     */
    protected $_completeRepository;

    /**
     * @var \Magento\Framework\Filesystem
     */
    protected $_filesystem;

    /**
     * @var \Magento\Catalog\Model\Product\OptionFactory
     */
    protected $_option;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\Timezone
     */
    protected $_locale;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_order;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;

    /**
     * @param Magento\Framework\App\Helper\Context                          $context
     * @param Magento\Directory\Model\Currency                              $currency
     * @param Magento\Customer\Model\Session                                $customerSession
     * @param Magento\Catalog\Model\ResourceModel\Product                   $product
     * @param Magento\Store\Model\StoreManagerInterface                     $storeManager
     * @param Magento\Directory\Model\Currency                              $currency
     * @param Magento\Framework\Locale\CurrencyInterface                    $localeCurrency
     * @param Magento\Framework\Filesystem                                  $filesystem
     * @param Magento\Framework\App\ResourceConnection                      $resource,
     * @param Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollection
     * @param Magento\Catalog\Model\Product\OptionFactory                   $option
     * @param Magento\Framework\Stdlib\DateTime\Timezone                    $localeResolver
     * @param Magento\Sales\Model\OrderFactory                              $order
     * @param Webkul\Marketplace\Model\ProductFactory $marketplaceProduct,
     * @param Webkul\Marketplace\Helper\Data $marketplaceHelper,
     * @param Webkul\MarketplacePreorder\Model\Source\PreorderType $preorderType,
     * @param Webkul\MarketplacePreorder\Model\Source\PreorderAction $preorderAction,
     * @param Webkul\MarketplacePreorder\Model\Source\PreorderEamil $preorderEmail,
     * @param Webkul\MarketplacePreorder\Model\Source\PreorderQty $preorderQty,
     * @param Webkul\MarketplacePreorder\Model\Source\PreorderSpecification $preorderSpecification,
     * @param Configurable $configurable,
     * @param PreorderItemsCollection $preorderItemsCollectionFactory,
     * @param PreorderSellerCollection $preorderSellerCollectionFactory,
     * @param PreorderSellerRepositoryInterface $sellerRepository,
     * @param PreorderItemsRepositoryInterface $itemsRepository,
     * @param PreorderCompleteRepositoryInterface $completeRepository,
     * @param SearchCriteriaBuilder $searchCriteriaBuilder,
     * @param Attribute $eavEntity
     */
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
        $this->_request = $context->getRequest();
        $this->_resource = $resource;
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_itemsCollectionFactory = $preorderItemsCollectionFactory;
        $this->_sellerCollectionFactory = $preorderSellerCollectionFactory;
        $this->_sellerRepository = $sellerRepository;
        $this->_itemsRepository = $itemsRepository;
        $this->_completeRepository = $completeRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($context);
        $this->_currency = $currency;
        $this->_localeCurrency = $localeCurrency;
        $this->_storeManager = $storeManager;
        $this->_preorderType = $preorderType;
        $this->_preorderAction = $preorderAction;
        $this->_preorderEmail = $preorderEmail;
        $this->_preorderQty = $preorderQty;
        $this->_preorderSpecification = $preorderSpecification;
        $this->_eavEntity = $eavEntity;
        $this->_productCollectionFactory = $productCollection;
        $this->_productFactory = $product;
        $this->_marketplaceProductFactory = $marketplaceProduct;
        $this->_configurable = $configurable;
        $this->_filesystem = $filesystem;
        $this->_option = $option;
        $this->_locale = $localeResolver;
        $this->_order = $order;
        $this->_marketplaceHelper = $marketplaceHelper;
    }

    /**
     * Retrieve information from carrier configuration.
     *
     * @param string $field
     *
     * @return void|false|string
     */
    public function getConfigData($field)
    {
        $path = 'mppreorder/general_setting/'.$field;

        return $this->_scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

     /**
     * Get Order by Id.
     *
     * @param int $orderId
     *
     * @return object
     */
    public function getOrder($orderId)
    {
        return $this->_order->create()->load($orderId);
    }

    /**
     * Load Preorder Seller Model Data.
     * @param  string $field
     * @param  string $fieldValue
     * @param  string $condition
     * @return bool|array
     */
    public function getPreorderSellerData($field, $fieldValue, $condition)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            $field,
            $fieldValue,
            $condition
        )->create();
        $items = $this->_sellerRepository->getList($searchCriteria);
        foreach ($items->getItems() as $value) {
            return $value;
        }
        return false;
    }
    /**
     * Load Preorder Item Model Data.
     * @param  string $fields
     * @param  string $fieldValues
     * @param  string $condition
     * @return bool|array
     */
    public function getPreorderItemData($fields, $fieldValues, $condition)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            $fields,
            $fieldValues,
            $condition
        )->create();

        $items = $this->_itemsRepository->getList($searchCriteria);
        foreach ($items->getItems() as $value) {
            return $value;
        }
        return false;
    }

    /**
     * Load Preorder Complete Model Data.
     * @param  string $fields
     * @param  string $fieldValues
     * @param  string $condition
     * @return bool|array
     */
    public function getPreorderCompleteData($fields, $fieldValues, $condition)
    {
        $searchCriteria = $this->searchCriteriaBuilder->addFilter(
            $fields,
            $fieldValues,
            $condition
        )->create();
        $items = $this->_completeRepository->getList($searchCriteria);
        foreach ($items->getItems() as $value) {
            return $value;
        }
        return false;
    }

    /**
    * This function is used to get the seller id of seller
    * @return integer
    **/
    public function getCustomerId()
    {
        return $this->_marketplaceHelper->getCustomerId();
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
        return $this->_sellerCollectionFactory->create()
                    ->addFieldToFilter('seller_id', $sellerId)
                    ->getFirstItem();
    }

    /**
     * Get Url To Check Configurable Product is Preorder or Not.
     *
     * @return string
     */
    public function getCheckConfigUrl()
    {
        return $this->_urlBuilder->getUrl('mppreorder/preorder/check/');
    }

    /**
     *
     * @return array
     */
    public function getPreorderType()
    {
        return $this->_preorderType->toOptionArray();
    }

    /**
     *
     * @return array
     */
    public function getPreorderAction()
    {
        return $this->_preorderAction->toOptionArray();
    }

    /**
     *
     * @return array
     */
    public function getPreorderEmailTypes()
    {
        return $this->_preorderEmail->toOptionArray();
    }

    /**
     *
     * @return array
     */
    public function getPreorderQuantityStatus()
    {
        return $this->_preorderQty->toOptionArray();
    }

    /**
     * Get Product by Id.
     *
     * @param int $productId
     *
     * @return object
     */
    public function getProduct($productId)
    {
        return $this->_productFactory->create()->load($productId);
    }

        /**
     * Get Html Block of Preorder Info Block.
     *
     * @param int $productId
     *
     * @return html
     */
    public function getPreOrderInfoBlock($productId)
    {
        $html = '';
        if ($this->isPreorder($productId)) {
            $flag = 0;
            $today = date('m/d/y');
            $product = $this->getProduct($productId);
            $availability = $product->getWkMarketplaceAvailability();
            if ($availability != '') {
                $date = date_create($availability);
                $dispDate = date_format($date, 'l jS F Y');
                $date = date_format($date, 'm/d/y');
                if ($date > $today) {
                    $flag = 1;
                }
            }
            $msg = $this->getPreorderCustomMessage($productId);
            $msg = str_replace("\n", '<br>', $msg);
            if ($msg != '') {
                $html .= "<div class='wk-msg-box wk-info'>";
                $html .= $msg;
                $html .= '</div>';
            }
            if ($flag == 1) {
                $html .= "<div class='wk-msg-box wk-info wk-availability-block'>";
                $html .= "<span class='wk-date-title'>";
                $html .= __('Available On');
                $html .= ' :</span>';
                $html .= "<span class='wk-date'>".$dispDate.'</span>';
                $html .= '</div>';
            }
        }

        return $html;
    }

    /**
     *
     * @return array
     */
    public function getBuyerPreorderSpecification()
    {
        return $this->_preorderSpecification->toOptionArray();
    }

    /**
     * @param  string $productType
     * @return array
     */
    public function getPreorderAttribute($productType)
    {
        $attributeOptions = [];

        if ($productType != '' && $productType != null) {
            $attributeId = $this->_eavEntity->getIdByCode('catalog_product', 'wk_marketplace_preorder');
            if ($attributeId !== '' && $attributeId !== null && $attributeId !== 0) {
                $attribute = $this->_objectManager->create(
                    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
                )->load($attributeId);
                $attributeOptions = $attribute->getSource()->getAllOptions();
            }
        }
        return $attributeOptions;
    }

    /**
     * get product preorder attribute data
     * used when seller edit product.
     * @param  int $productId
     * @return array
     */
    public function getEditPreorderAttribute($productId)
    {
        if ($productId) {
            $attributeOptions = [];
            $magentoProductModel = $this->_objectManager->create(
                'Magento\Catalog\Model\Product'
            )->load($productId);

            $preorderStatus = $magentoProductModel->getWkMarketplacePreorder();
            $preorderAvailability = $magentoProductModel->getWkMarketplaceAvailability();
            $preorderQty = $magentoProductModel->getWkMppreorderQty();
            $attributeId = $this->_eavEntity->getIdByCode('catalog_product', 'wk_marketplace_preorder');

            if ($attributeId !== '' && $attributeId !== null && $attributeId !== 0) {
                $attribute = $this->_objectManager->create(
                    'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
                )->load($attributeId);
                $attributeOptions = $attribute->getSource()->getAllOptions();
            }

            return [
                'attributeoptions' => $attributeOptions,
                'preorderstatus' => $preorderStatus,
                'preorderAvailability' => $preorderAvailability,
                'mppreorderqty' => $preorderQty
            ];
        }
    }

    /**
     *
     * @return bool
     */
    public function getPreorderQtyEnable()
    {
        $configuration = $this->getSellerConfiguration();
        if (is_array($configuration)) {
            if (count($configuration) > 0) {
                if ($configuration['mppreorder_qty']==1) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Is Product is set to Preorder|Not
     * @param  string  $productId
     * @return boolean
     */
    public function isPreorder($productId = '')
    {
        if ($productId == '' || $productId == 0) {
            return false;
        }

        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);
        $collection->addAttributeToSelect('*');
        foreach ($collection as $item) {
            $product = $item;
            $isProduct = true;
            break;
        }
        if (!$isProduct) {
            return false;
        }
        $productType = $product->getTypeId();
        if (in_array($productType, ['bundle', 'grouped', 'configurable'])) {
            return false;
        }
        $status = 0;
        $stockStatus = $this->getProductStock($product);

        $sellerId = $this->getSellerIdByProductId($productId);

        if ($stockStatus != 1) {
            $preorderAction = $this->getSellerPreorderAction($sellerId);

            if ((int) $preorderAction == 1) {
                $status = 1;
            } elseif ((int) $preorderAction == 2) {
                $filterProduct = $this->getFilterProducts(2, $sellerId);
                $filterProductArray = $this->getProductIdsFromSku($filterProduct, $sellerId);

                if (in_array($productId, $filterProductArray)) {
                    $status = 1;
                }
            } elseif ($preorderAction == 3) {
                $filterProduct = $this->getFilterProducts(3, $sellerId);
                $filterProductArray = $this->getProductIdsFromSku($filterProduct, $sellerId);
                if (!in_array($productId, $filterProductArray)) {
                    $status = 1;
                }
            } else {
                $collection = $this->_productCollectionFactory->create();
                $collection->addFieldToFilter('entity_id', $productId);
                $collection->addAttributeToSelect('*');
                foreach ($collection as $item) {
                    $product = $item;
                    $isProduct = true;
                    break;
                }
                if (!$isProduct) {
                    return false;
                }
                $attribute = $product->getResource()->getAttribute('wk_marketplace_preorder');
                $attributeId = $attribute->getSource()->getOptionId('Enable');
                if ($product->getdata('wk_marketplace_preorder') == $attributeId) {
                    $status = 1;
                }
            }

            if ($status == 1) {
                return true;
                // return "Preorder";
            } else {
                return false;
                // return "Normal Order";
            }
        } else {
            return false;
            // return "Normal Order";
        }
    }

    public function getAssociatedId($attribute, $product)
    {
        $configModel = $this->_configurable;
        $product = $configModel->getProductByAttributes($attribute, $product);
        $productId = $product->getId();
        return $productId;

    }

    /**
     * Get Stock Details of Product.
     *
     * @param int $productId
     *
     * @return array
     */
    public function getStockDetails($productId)
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
        $collection->addFieldToFilter('entity_id', $productId);
        foreach ($collection as $value) {
            $stockDetails['qty'] = $value->getQty();
            $stockDetails['is_in_stock'] = $value->getIsInStock();
            $stockDetails['name'] = $value->getName();
        }

        return $stockDetails;
    }

    /**
     * Product is in stock or not
     * @param  \Magento\Catalog\Model\Product $product
     * @return int
     */
    public function getProductStock(\Magento\Catalog\Model\Product $product)
    {
        $details = $this->getStockDetails($product->getId());
        return $details['is_in_stock'];
    }

    /**
     * @param  int $productId
     * @return int
     */
    public function getSellerIdByProductId($productId)
    {
        $product = $this->_marketplaceProductFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('mageproduct_id', $productId);
        if (count($product) > 0) {
            foreach ($product as $value) {
                $sellerid = $value->getSellerId();
            }
            return $sellerid;
        }
        return 0;
    }

    /**
     * Check Preorder Product Qty.
     * @param  $item
     * @param  \Magento\Catalog\Model\Product  $product
     * @return bool
     */
    public function getQtyCheck($item, $product)
    {
        $productType = $product->getTypeId();
        if ($productType == 'configurable') {
            $configModel = $this->_configurable;
            $usedProductIds = $configModel->getUsedProductIds($product);
            foreach ($usedProductIds as $usedProductId) {
                if ($this->isPreorder($usedProductId)) {
                    $product = $this->_productFactory->create()->load($usedProductId);
                    $preorderQty = $product->getWkMppreorderQty();
                    if ($preorderQty && (int) $preorderQty < $item->getQty()) {
                        return false;
                    }
                }
            }
        } else {
            $product = $this->_productFactory->create()->load($product->getId());
            $preorderQty = $product->getWkMppreorderQty();
            if ($preorderQty && (int) $preorderQty < $item->getQty()) {
                return false;
            }
        }

        return true;
    }

        /**
     * Check Product is Partial Preorder or Not.
     *
     * @param int $productId
     *
     * @return bool
     */
    public function isPartialPreorder($productId)
    {
        $sellerId = $this->getSellerIdByProductId($productId);
        if (!$this->isPreorder($productId)) {
            return false;
        }
        $preorderType = $this->getSellerPreorderType($sellerId);
        if ($preorderType == 1) {
            return true;
        }
        return false;
    }

    /**
     * Get Product's Price.
     *
     * @param int $productId
     *
     * @return float
     */
    public function getPrice($product)
    {
        if ($this->isInOffer($product)) {
            $price = $product->getSpecialPrice();
        } else {
            $price = $product->getFinalPrice();
        }

        return $price;
    }

    /**
     * @param  \Magento\Catalog\Model\Product  $product
     * @return boolean
     */
    public function isInOffer($product)
    {
        $specialPrice = number_format($product->getFinalPrice(), 2);
        $regularPrice = number_format($product->getPrice(), 2);

        if ($specialPrice != $regularPrice) {
            return $this->chekOffer(
                $product->getData('special_from_date'),
                $product->getData('special_to_date')
            );
        } else {
            return false;
        }
    }

    /**
     * Product Special Price
     * @param  string $fromDate
     * @param  string $toDate
     * @return bool
     */
    protected function chekOffer($fromDate, $toDate)
    {
        if ($fromDate) {
            $fromDate = strtotime($fromDate);
            $toDate = strtotime($toDate);
            $currentData = (array) $this->_locale->date()->setTime(0, 0, 0);
            $now = strtotime(
                (string) $currentData['date']
            );
            if ($toDate) {
                if ($fromDate <= $now && $now <= $toDate) {
                    return true;
                }
            } else {
                if ($fromDate <= $now) {
                    return true;
                }
            }
        }

        return false;
    }


    /**
     * Get Partial Preorder Price.
     *
     * @param object $product
     * @param int    $productId
     *
     * @return flaot
     */
    public function getPreorderPrice($product, $productId)
    {
        $price = $this->getPrice($product);
        $sellerId = $this->getSellerIdByProductId($productId);
        if ($this->isPartialPreorder($productId)) {
            $preorderPercent = (int) $this->getPreorderPercent($sellerId);
            if ($preorderPercent > 0) {
                $price = ($price * $preorderPercent) / 100;
            }
        }

        return $price;
    }

    /**
     * check Email send is manually|automatic
     * @param  int $sellerId
     * @return int
     */
    public function getEmailAction($sellerId)
    {
        if ($sellerId !== 0) {
            $configuration = $this->getSellerConfiguration($sellerId);
            if (is_array($configuration)) {
                if (count($configuration)) {
                    return $configuration['email_type'];
                }
            }
            return $this->getConfigData('preorder_mail');
        } else {
            return $this->getConfigData('preorder_mail');
        }
    }

    /**
     *
     * @param  int $sellerId
     * @return int|string
     */
    public function getSellerPreorderAction($sellerId)
    {
        if ($sellerId !== 0) {
            $configuration = $this->getSellerConfiguration($sellerId);
            if (is_array($configuration)) {
                if (count($configuration)) {
                    return $configuration['preorder_action'];
                }
                # code...
            }
            return $this->getConfigData('preorder_action');
        } else {
            return $this->getConfigData('preorder_action');
        }
    }

    /**
     * Get Custom Preorder Custom Message
     * @param  int $productId
     * @return string
     */
    public function getPreorderCustomMessage($productId)
    {
        $sellerId = $this->getSellerIdByProductId($productId);
        $msg = '';
        if ($sellerId !== 0) {
            $configuration = $this->getSellerConfiguration($sellerId);
            if (is_array($configuration)) {
                if (count($configuration)) {
                    $msg =  $configuration['custom_message'];
                } else {
                    $msg =  $this->getConfigData('custom_message');
                }
            }
            if ($msg == '') {
                $msg =  $this->getConfigData('custom_message');
            }
        } else {
            $msg =  $this->getConfigData('custom_message');
        }
        return $msg;
    }

    /**
     * preorder percent(%) set by seller|Admin
     * @param  int $sellerId
     * @return float
     */
    public function getPreorderPercent($sellerId)
    {
        $percent = 0;
        if ($sellerId !== 0) {
            $configuration = $this->getSellerConfiguration($sellerId);

            if (is_array($configuration)) {
                if (count($configuration)) {
                    $percent =  $configuration['preorder_percent'];
                } else {
                    $percent =  $this->getConfigData('percent');
                }
            }

            if ($percent <= 0 && $percent == '') {
                $percent =  $this->getConfigData('percent');
            }

        } else {
            $percent =  $this->getConfigData('percent');
        }
        return $percent;
    }

    /**
     * Preorder Type is Complete|Percent
     * @param  int $sellerId
     * @return int
     */
    public function getSellerPreorderType($sellerId)
    {
        $type = 0;
        if ($sellerId !== 0) {
            $configuration = $this->getSellerConfiguration($sellerId);
            if (is_array($configuration)) {
                if (count($configuration)) {
                    $type =  $configuration['type'];
                } else {
                    $type =  $this->getConfigData('preorder_type');
                }
            }
            if ($type <= 0 && $type == '') {
                $type =  $this->getConfigData('preorder_type');
            }
        } else {
            $type =  $this->getConfigData('preorder_type');
        }
        return $type;
    }

    /**
     * getSellerPreorderSpecification used to get buyer preorder configuration
     * @param  int $sellerId
     * @return boolean
     */
    public function getSellerPreorderSpecification($sellerId)
    {
        if (intval($sellerId)!==0 && $sellerId!==null && $sellerId!=="") {
            $configuration = $this->getSellerConfiguration($sellerId);
            if (is_array($configuration)) {
                if (count($configuration) > 0) {
                    if ($configuration['preorder_specific']==0) {
                        return true;
                    } else {
                        return false;
                    }
                } else {
                    return false;
                }
            } else {
                return false;
            }
        } else {
            $specification = $this->getConfigData('mppreorder_specific');
            if (intval($specification)==0) {
                return true;
            } else {
                return false;
            }
        }
    }

    /**
     * check if seller has saved product skus for preorder.
     * @param  int $type
     * @param  int $sellerId
     * @return string
     */
    public function getFilterProducts($type, $sellerId)
    {
        if ($sellerId !== 0) {
            $configuration = $this->getSellerConfiguration($sellerId);
            if (is_array($configuration)) {
                if (count($configuration)) {
                    if ($type == 2) {
                        return $configuration['few_products'];
                    }
                } else {
                    if ($type == 2) {
                        return $this->getConfigData('few_products');
                    }
                    return $this->getConfigData('disable_products');
                }
            }

            return $configuration['disable_products'];
        } else {
            if ($type == 2) {
                return $this->getConfigData('few_products');
            }
            return $this->getConfigData('disable_products');
        }
    }

    /**
     * load product by sku
     * @param  array $skus
     * @param  int $sellerId
     * @return array
     */
    public function getProductIdsFromSku($skus, $sellerId)
    {
        $arrayOfProductIds = [];
        $isAvailable = strpos($skus, ',');
        if (strlen($skus) > 0) {
            if ($isAvailable !== false) {
                $arrayOfProductSkus = explode(',', $skus);
                foreach ($arrayOfProductSkus as $value) {
                    $value = trim($value);
                    if ($sellerId !== 0) {
                        $value = $this->_productFactory->create()->getIdBySku($value);
                        if ($value !== null && $value !== '' && is_numeric($value)) {
                            $arrayOfProductIds[] = $value;
                        }
                    }
                }
            } else {
                $id = $this->_productFactory->create()->getIdBySku($skus);
                if ($id !== null && $id !== '' && is_numeric($id)) {
                    $arrayOfProductIds[] = $id;
                }
            }
        }
        $arrayOfProductIds = array_unique($arrayOfProductIds);

        return $arrayOfProductIds;
    }
    /**
     * Check Product is Child Product or Not.
     *
     * @return bool
     */
    public function isChildProduct($productId = '')
    {
        if ($productId == '') {
            $productId = $this->_request->getParam('id');
        }
        $productModel = $this->_productFactory->create();
        $product = $productModel->load($productId);
        $productType = $product->getTypeID();
        $productTypeArray = ['bundle', 'grouped'];
        if (in_array($productType, $productTypeArray)) {
            return true;
        }

        return false;
    }

        /**
     * Check Configurable Product is Preorder or Not.
     *
     * @param int $productId
     *
     * @return bool
     */
    public function isConfigPreorder($productId)
    {
        $isProduct = false;
        $collection = $this->_productCollectionFactory->create();
        $collection->addFieldToFilter('entity_id', $productId);
        $collection->addAttributeToSelect('*');
        foreach ($collection as $item) {
            $product = $item;
            $isProduct = true;
            break;
        }
        if ($isProduct) {
            $productType = $product->getTypeId();
            if ($productType == 'configurable') {
                $configModel = $this->_configurable;
                $usedProductIds = $configModel->getUsedProductIds($product);
                foreach ($usedProductIds as $usedProductId) {
                    if ($this->isPreorder($usedProductId)) {
                        return true;
                    }
                }
            }
        }
        return false;
    }

    /**
     * Get Html Block of Pay Preorder Amount (Partial Preorder).
     *
     * @return html
     */
    public function getPayPreOrderHtml($productId)
    {
        $sellerId = $this->getSellerIdByProductId($productId);

        $html = '';
        $type = $this->getSellerPreorderType($sellerId);
        $percent = (int) $this->getPreorderPercent($sellerId);
        if ($type == 1 && $percent > 0) {
            $html .= "<div class='wk-msg-box wk-info wk-pay-preorer-amount'>";
            $html .= __('Pay %1% as Preorder.', $percent);
            $html .= '</div>';
        }

        return $html;
    }

    /**
     * Get Prorder Complete Product Id.
     *
     * @return int
     */
    public function getPreorderCompleteProductId()
    {
        $productModel = $this->_productFactory->create();
        $productId = (int) $productModel->getIdBySku('preorder_complete');

        return $productId;
    }

    /**
     * Get Preorder Complete Product's Options.
     *
     * @return json
     */
    public function getPreorderCompleteOptions()
    {
        $array = [];
        $productId = (int) $this->getPreorderCompleteProductId();
        $product = $this->_productFactory->create()->load($productId);
        foreach ($product->getOptions() as $option) {
            $optionId = $option->getId();
            $optionTitle = $option->getTitle();
            $array[] = ['id' => $optionId, 'title' => $optionTitle];
        }

        return json_encode($array);
    }

    /**
     * Check Order Item is Preorder or Not.
     *
     * @param int $itemId
     *
     * @return bool
     */
    public function isPreorderOrderedItem($orderId)
    {
        $collection = $this->_itemsCollectionFactory->create()
            ->addFieldToFilter('order_id', ['eq' => $orderId]);

        if (count($collection) > 0) {
            return true;
        }
        return false;
    }

    /**
     * @param  array $fields
     * @param  array $values
     * @return bool|object
     */
    public function getPreorderItemCollectionData($fields, $values)
    {
        $collection = $this->_itemsCollectionFactory->create();
        foreach ($fields as $key => $field) {
            $collection->addFieldToFilter($field, ['eq' => $values[$key]]);
        }
        foreach ($collection as $value) {
            return $value;
        }

        return false;

    }

    /**
     * Get Preorder Status.
     *
     * @param int $itemId
     *
     * @return int
     */
    public function getPreorderStatus($itemId)
    {
        $status = 0;
        $item = $this->getPreorderItemData('item_id', $itemId, 'eq');
        if ($item) {
            $status = $item['status'] + 1;
        }
        return $status;
    }

    /**
     * Check if Configurable Product is Available or Not to Complete Preorder.
     *
     * @param int $productId
     * @param int $qty
     * @param int $parentId
     *
     * @return bool
     */
    public function isConfigAvailable($productId, $qty, $parentId)
    {
        if ($this->isAvailable($productId, $qty)) {
            if ($this->isAvailable($parentId, $qty, 1)) {
                return true;
            }
        }
        return false;
    }
    /**
     * Check Product is Available or Not to Complete Preorder.
     *
     * @param int $productId
     * @param int $qty
     * @param int $isQty
     *
     * @return bool
     */
    public function isAvailable($productId, $qty, $isQty = 0)
    {
        $stockDetails = $this->getStockDetails($productId);
        if ($stockDetails['is_in_stock'] == 1) {
            if ($isQty == 0) {
                if ($stockDetails['qty'] > $qty) {
                    return true;
                }
            } else {
                return true;
            }
        }

        return false;
    }

    /**
     * updatePreorderQty used to update prorder quantity after place order of a preorder product
     * @param int $itemId
     */
    public function updatePreorderQty($item)
    {
        $productId = $item->getProductId();
        if ($productId) {
            $catalogModel = $this->_productFactory->create()->load($productId);
            $preorderQty = $catalogModel->getWkMppreorderQty();
            if ($preorderQty) {
                $stockDetails = $this->getStockDetails($productId);
                $qty = $stockDetails['qty'];
                $this->_objectManager->get(
                    'Magento\Catalog\Model\Product\Action'
                )->updateAttributes(
                    [$productId],
                    [
                        'wk_mppreorder_qty' => intval($qty)-intval($item->getQtyOrdered())
                    ],
                    $this->_storeManager->getStore()->getId()
                );
            } elseif ($preorderQty >= $item->getQtyOrdered()) {
                $this->_objectManager->get(
                    'Magento\Catalog\Model\Product\Action'
                )->updateAttributes(
                    [$productId],
                    [
                        'wk_mppreorder_qty' => intval($preorderQty)-intval($item->getQtyOrdered())
                    ],
                    $this->_storeManager->getStore()->getId()
                );
            }
        }
    }


    /**
     * setProductDisabled disable product if preorder allow only preorder buyer
     * so normal customer can not buy this product
     * only preorder buyer can complete there order.
     * @param int $productId
     * @param int $storeId
     */
    public function setProductDisabled($productId, $storeId)
    {
        $this->_objectManager->get(
            'Magento\Catalog\Model\Product\Action'
        )->updateAttributes([$productId], ['status' => 2], $storeId);
    }
    /**
     * setProductEnabled enable product if preorder allow all
     * so all customer can not buy this product.
     * @param int $productId
     * @param int $storeId
     */
    public function setProductEnabled($productId, $storeId)
    {
        $this->_objectManager->get(
            'Magento\Catalog\Model\Product\Action'
        )->updateAttributes([$productId], ['status' => 1], $storeId);
    }
}

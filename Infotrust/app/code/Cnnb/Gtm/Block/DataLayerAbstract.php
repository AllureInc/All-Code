<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For rendering tag manager JS
 */
namespace Cnnb\Gtm\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\Gtm\Model\Cart as CurrentCart;
use Cnnb\Gtm\Helper\Data as GtmHelper;
use Magento\Checkout\Model\Cart as Cart;
use Magento\Checkout\Model\Session as CheckoutSession;
use Magento\Framework\Session\SessionManagerInterface as MageSession;
use Cnnb\Gtm\Helper\DataLayerItem as dataLayerItemHelper;
use Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable as Configurable;
use Magento\Catalog\Model\Product as product;

/**
 * DataLayerAbstract | Block Class
 */
class DataLayerAbstract extends Template
{
    /**
     * @var GtmHelper
     */
    protected $_gtmHelper;

    /**
     * @var string
     */
    protected $_dataLayerEventName = 'Cnnb_datalayer';

    /**
     * Push elements last to the data layer
     * @var array
     */
    protected $_customVariables = [];

    /**
     * Magento\Framework\UrlInterface
     */
    protected $_urlInterface;

    /**
     * Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @var array
     */
    protected $_variables = [];

    /**
     * Magento\Framework\Registry
     */
    protected $_registry;

    /**
     * Magento\Catalog\Model\ProductRepository
     */
    protected $_productRepository;

    /**
     * string
     */
    protected $_banner_type = '';

    /**
     * array
     */
    protected $_promotion_ids = [];

    /**
     * array
     */
    protected $_banner_ids = [];

    /**
     * array
     */
    protected $_banner_product_ids = [];

    /**
     * array
     */
    protected $_promotion_click_labels = [];

    /**
     * array
     */
    protected $_currentCart;

    /**
     * @var pageType
     */
    protected $_categoryCollectionFactory;

    /**
     * @var pageType
     */
    protected $_productCollectionFactory;
    
     /**
      * @var CheckoutSession
      */
    protected $_checkoutSession;

    /**
     * @var orderRepository
     */
    protected $_orderRepository;

    /**
     * @var result
     */
    protected $_result;

    /**
     * @var currencyCode
     */
    protected $_currencyCode;
    
    /**
     * @var pageType
     */
    protected $_pageType;

    /**
     * @var Cart model
     */
    protected $_cart;

    /**
     * @var Session data
     */
    protected $_coreSession;

    /**
     * @var dataLayerItemHelper
     */
    protected $_dataLayerItemHelper;

    /**
     * @var configurableproduct
     */
    protected $_configurableProduct;

    /**
     * @var productdetail
     */
    protected $_productDetail;
    
    /**
     * @var jsonHelper
     */
    protected $_jsonHelper;

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * @var $refundClassId
     */
    protected $_refundClassId;

    /**
     * @var $_mageSession
     */
    protected $_mageSession;


    /**
     * @param Context $context
     * @param GtmHelper $gtmHelper
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        GtmHelper $gtmHelper,
        CheckoutSession $checkoutSession,
        CurrentCart $currentCart,
        \Magento\Framework\Registry $registry,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        \Magento\Framework\UrlInterface $urlInterface,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Catalog\Model\ResourceModel\Category\CollectionFactory $categoryCollectionFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        Cart $cart,
        dataLayerItemHelper $dataLayerItemHelper,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        Configurable $configurableProduct,
        Product $productDetail,
        MageSession $mageSession,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        array $data = []
    ) {
        $this->_gtmHelper = $gtmHelper;
        parent::__construct($context, $data);
        $this->_init();
        $this->_registry = $registry;
        $this->_productRepository = $productRepository;
        $this->_urlInterface = $urlInterface;
        $this->_request = $request;
        $this->_cart = $cart;
        $this->_currentCart = $currentCart;
        $this->_categoryCollectionFactory = $categoryCollectionFactory;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_orderRepository = $orderRepository;
        $this->_dataLayerItemHelper = $dataLayerItemHelper;
        $this->_coreSession = $coreSession;
        $this->_configurableProduct = $configurableProduct;
        $this->_productDetail = $productDetail;
        $this->_mageSession = $mageSession;
        $this->_jsonHelper = $jsonHelper;
        $this->_logger = $this->getZendLogger();
    }

    /**
     * Function for logger
     */
    public function getZendLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/gtm_check.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }

    /**
     * @return $this
     * @throws NoSuchEntityException
     */
    protected function _init()
    {
        $this->addVariable('ecommerce', ['currencyCode' => $this->getStoreCurrencyCode()]);
        $this->addVariable('pageType', $this->_request->getFullActionName());
        $this->addVariable('list', 'other');
        /*echo '<script type="text/JavaScript">
            prompt("GeeksForGeeks");
        </script>';*/

        return $this;
    }

    /**
     * Return helper
     */
    public function getHelper()
    {
        return $this->_gtmHelper;
    }

    /**
     * Return data layer json
     *
     * @return array
     */
    public function getDataLayer()
    {
        $this->_pageType = '';
        $currencyCode = '';
        $this->_eventManager->dispatch(
            $this->_dataLayerEventName,
            ['dataLayer' => $this]
        );

        $result = [];

        if (!empty($this->getVariables())) {
            $this->_pageType = $this->getVariables()['pageType'];
            $current_url = $this->_urlInterface->getCurrentUrl();
            $base_url = $this->_urlInterface->getBaseUrl();
            $currencyCode = $this->getVariables()['ecommerce']['currencyCode'];
            $result[] = $this->getVariables();
        }

        if (!empty($this->_customVariables)) {
            ksort($this->_customVariables);
            foreach ($this->_customVariables as $priorityVariable) {
                foreach ($priorityVariable as $data) {
                    $result[] = $data;
                }
            }
        }

        $this->_result = $result;
        $this->_currencyCode = $currencyCode;

        if ($this->_pageType) {
            switch ($this->_pageType) {

                case "cms_index_index":
                    if ($current_url == $base_url) {
                        $this->_banner_type = 'homepage';
                    } else {
                        $this->_banner_type = 'cmspage';
                    }
                    
                    $result = $this->getDataForCategoryPage($result, $this->_pageType, $currencyCode);
                    break;

                case "catalog_category_view":
                    $this->_banner_type = 'categorypage';
                    $result = $this->getDataForCategoryPage($result, $this->_pageType, $currencyCode);
                    break;

                case "catalog_product_view":
                    $this->_banner_type = 'productpage';
                    $result = $this->getDataForProductPage($this->_pageType, $result);
                    break;

                case "checkout_cart_index":
                    $this->_banner_type = 'productpage';
                    $result = $this->getDataForCartPage($this->_pageType, $result, $currencyCode);
                    break;
                case "checkout_index_index":
                    $this->_banner_type = 'checkout';
                    $result = $this->getCartData($result, $this->_pageType, $currencyCode);
                    break;

                case "checkout_onepage_success":
                    $this->_banner_type = 'checkout_success';
                    $result = $this->getOrderData($result, $this->_pageType, $currencyCode);
                    break;
            }
        }
        return $result;
    }

    /**
     * @return string
     */
    public function getDataLayerJson()
    {
        return json_encode($this->getDataLayer());
    }

    /**
     * @return string
     */
    public function getDataLayerJs()
    {
        $result = [];

        foreach ($this->getDataLayer() as $data) {
            $result[] = sprintf("window.%s.push(%s);\n", $this->getDataLayerName(), json_encode($data));
        }

        return implode("\n", $result);
    }

    /**
     * Add Variables
     * @param string $name
     * @param string|array $value
     * @return $this
     */
    public function addVariable($name, $value)
    {
        if (!empty($name)) {
            $this->_variables[$name] = $value;
        }

        return $this;
    }

    /**
     * Return Data Layer Variables
     *
     * @return array
     */
    public function getVariables()
    {
        return $this->_variables;
    }

    /**
     * Add variable to the custom push data layer
     *
     * @param  array  $data
     * @param  int  $priority
     * @param  null  $group
     * @return $this
     */
    public function addCustomDataLayer($data, $priority = 0, $group = null)
    {
        $priority = (int) $priority;

        if (is_array($data) && empty($group)) {
            $this->_customVariables[$priority][] = $data;
        } elseif (is_array($data) && !empty($group)) {
            if (array_key_exists($priority, $this->_customVariables)
                && array_key_exists($group, $this->_customVariables[$priority])
            ) {
                $this->_customVariables[$priority][$group] = array_merge(
                    $this->_customVariables[$priority][$group],
                    $data
                );
            } else {
                $this->_customVariables[$priority][$group] =  $data;
            }
        }
        return $this;
    }

    /**
     * @param $event
     * @param $data
     * @param  int  $priority
     * @return $this
     */
    public function addCustomDataLayerByEvent($event, $data, $priority = 20)
    {
        if (!empty($event)) {
            $data['event'] = $event;
            $this->addCustomDataLayer($data, $priority, $event);
        }

        return $this;
    }

    /**
     * Format Price
     *
     * @param $price
     * @return float
     */
    public function formatPrice($price)
    {
        return $this->_gtmHelper->formatPrice($price);
    }

    /**
     * @return string
     */
    public function getDataLayerName()
    {
        if (!$this->getData('data_layer_name')) {
            $this->setData('data_layer_name', $this->_gtmHelper->getDataLayerName());
        }
        return $this->getData('data_layer_name');
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getStoreCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * Return cookie restriction mode value.
     *
     * @return int
     */
    public function isCookieRestrictionModeEnabled()
    {
        return (int) $this->_gtmHelper->isCookieRestrictionModeEnabled();
    }
    /**
     * Return current website id.
     *
     * @return int
     * @throws LocalizedException
     */
    public function getCurrentWebsiteId()
    {
        return $this->_storeManager->getWebsite()->getId();
    }

    /**
     * Function for getting category data
     *
     * @return array
     */
    public function getDataForCategoryPage($result, $pageType, $currencyCode)
    {
        $attribute_data = $this->_gtmHelper->getAttributeMappingData();
        $category_data = [
            'event' => 'uaevent',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> 'Product Impressions',
            'eventLabel' => 'Product List Page'
        ];

        $category_data['ecommerce']['currencyCode'] = $currencyCode;
        if (isset($result[1])) {
            foreach ($result[1]['products'] as $key => $value) {
                $product_data_arr =[];
                $product_data_arr = $this->generateAttributesData($attribute_data, $value, $product_data_arr);
                $product_data_arr['id'] = $value['entity_id'];
                $product_data_arr['name'] = $value['name'];
                $product_data_arr['category'] = $result[1]['category']['name'];
                $product_data_arr['list'] = $result[1]['eventLabel'];
                $product_data_arr['position'] = $value['cat_index_position'];
                $product_data_arr['ratings'] = $value['ratings'];

                if (isset($value['variant_ids'])) {
                    $product_data_arr['variant'] = implode(',', $value['variant_ids']);
                } else {
                    $product_data_arr['variant'] = '';
                }

                $product_data_arr['price'] = (isset($value['price'])) ? $value['price'] : '';
                $category_data['ecommerce']['impressions'][] = $product_data_arr;
            }
        }
        return $category_data;
    }

    /**
     * Function for getting attributes data
     *
     * @return array
     */
    public function generateAttributesData($attribute_data, $value, $product_data_arr)
    {
        if (!empty($attribute_data)) {
            foreach ($attribute_data as $attr_key => $attr_value) {
                if (isset($value[$attr_value])) {
                    
                    if ($attr_value == 'quantity_and_stock_status') {
                        if (isset($value[$attr_value])) {
                            $attr_value = ($value[$attr_value] == 1) ? 'in stock' : 'out of stock';
                        }
                        $product_data_arr[$attr_key] = $attr_value;
                    } else {
                        $product_data_arr[$attr_key] = $value[$attr_value];
                    }
                }
            }
        }
        return $product_data_arr;
    }

    /**
     * Function for getting product data
     *
     * @return array
     */
    public function getDataForProductPage($pageType, $result)
    {
        $currencyCode = $this->_currencyCode;
        $attribute_data = $this->_gtmHelper->getAttributeMappingData();
        $product_data_arr =[];
        $product_data_arr['id'] = $result[1]['product']['id'];
        $product_data_arr['name'] = $result[1]['product']['name'];
        $product_data_arr['category'] = $result[1]['product']['category'];
        $product_data_arr['variant'] = $result[1]['product']['variant'];
        $product_data_arr['price'] = $result[1]['product']['price'];
        $product_data_arr['brand'] = $result[1]['product']['brand'];
        $product_data_arr['type'] = $result[1]['product']['product_type'];
        $product_data_arr['ratings'] = $result[1]['product']['ratings'];
        if (!empty($attribute_data)) {
            foreach ($attribute_data as $attr_key => $attr_value) {
                if (isset($result[1]['product']['attributes'][$attr_value])) {
                    if ($attr_value == 'quantity_and_stock_status') {
                        if (isset($result[1]['product']['attributes'][$attr_value])) {
                            $in_stock = $result[1]['product']['attributes'][$attr_value]['value'];
                            $attr_value = ($in_stock['is_in_stock'] == 1) ? __('in stock') : __('out of stock');
                        }
                        $product_data_arr[$attr_key] = $attr_value;
                    } else {
                        $product_data_arr[$attr_key] = $result[1]['product']['attributes'][$attr_value]['value'];
                    }
                }
            }
        }
          
        $productType = $result[1]['product']['product_type'];
        $productArray = [
            'event' => 'uaevent',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> 'Product Detail',
            'eventLabel' => $result[1]['product']['name'].'::'. $result[1]['product']['id']
        ];
        $actionField = [
            'action' => 'detail',
            'list' => 'category-page'
        ];
        $productArray['ecommerce']['currencyCode'] = $currencyCode;
        $productArray['ecommerce']['detail']['actionField'] = $actionField;
        $productArray['ecommerce']['detail']['products'] = $product_data_arr;

        return $productArray;
    }

    /**
     * Function for getting banner data
     *
     * @return array
     */
    public function getDataForCartPage($pageType, $result, $currencyCode)
    {
        $cart_data = [
            'event' => 'cartPage',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> 'Cart Page',
            'eventLabel' => 'Checkout Cart Page'
        ];
        $cart_data['ecommerce']['currencyCode'] = $currencyCode;
        if(array_key_exists("items",$result[1]['cart'])) {
            $cart_data['ecommerce']['products'][] = $result[1]['cart']['items'];
        } else {
            $cart_data['ecommerce']['products'][] = [];
        }
        return $cart_data;
    }

    /**
     * Function for getting banner data
     *
     * @return array
     */
    public function getBannnerTagData()
    {
        $banner_type = $this->_banner_type;

        $banner_data = $this->_gtmHelper->getBannerMappingData();
        $banner_push_result = [];
        $data_for_push = [];
        $not_run_for = ['sales_order_view'];
        $banner_push_result['event'] = 'uaevent';
        $banner_push_result['eventCategory'] = 'Ecommerce';
        $banner_push_result['eventAction'] = 'Promotion Impressions';

        if (isset($banner_data[$banner_type]) && !empty($banner_data[$banner_type])) {
            $banner_push_result['event'] = 'uaevent';
            $banner_push_result['eventCategory'] = 'Ecommerce';
            $banner_push_result['eventAction'] = 'Promotion Impressions';
            $banner_push_result['eventLabel'] = $banner_type;
            foreach ($banner_data[$banner_type] as $key => $value) {
                $this->_banner_product_ids[] = $value['gtm_product_associated'];
                $this->_promotion_ids[$value['gtm_product_associated']] = $value['gtm_id'];
                $this->_promotion_click_labels[$value['gtm_id']] = $value['gtm_product_associated'];
                $this->_banner_ids[] = $value['gtm_id'];
                $data_for_push[] = [
                    'id' => $value['gtm_id'],
                    'name' => $value['gtm_name'],
                    'creative' => $value['gtm_creative'],
                    'position' => $value['gtm_posotion']
                ];
            }

            $banner_push_result['ecommerce']['promoView']['promotions'] = $data_for_push;
        }

        return $banner_push_result;
    }

    /**
     * Function for getting banner data
     *
     * @return array
     */
    public function getPromotionClickData()
    {
        $promotion_data = $this->_gtmHelper->getPromotionClickMappingData();
        $banner_push_result = [];
        $data_for_push = [];
        
        if (!empty($promotion_data)) {
            foreach ($promotion_data as $key => $value) {
                if (in_array($key, $this->_banner_product_ids)) {
                    foreach ($value as $new => $data) {
                        $data_for_push[$this->_promotion_ids[$key]][] = [
                            'gtm_event_label' => $data['gtm_event_label'],
                            'id' => $data['gtm_id'],
                            'name' => $data['gtm_name'],
                            'creative' => $data['gtm_creative'],
                            'position' => $data['gtm_posotion']
                        ];
                    }
                }
            }
        }

        return $data_for_push;
    }

    /**
     * Function for getting associated product ids of banner
     *
     * @return array
     */
    public function getBannerProductIds()
    {
        return $this->_banner_product_ids;
    }

    /**
     * Function for getting promotion ids
     *
     * @return array
     */
    public function getPromotionIds()
    {
        return $this->_promotion_ids;
    }

    /**
     * Function for getting banner ids
     *
     * @return array
     */
    public function getBannerIds()
    {
        return $this->_banner_ids;
    }

    /**
     * Function for getting clicked promotion labels
     *
     * @return array
     */
    public function getPromotionClickLabels()
    {
        return $this->_promotion_click_labels;
    }

    /**
     * Get active quote
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getCartDetails($callfrom, $productType, $productId)
    {
        $configurable_product = false;
        $currencyCode = $this->getVariables()['ecommerce']['currencyCode'];
        $quote = $this->_cart->getQuote();
        /*$itemsVisible = $quote->getItems();*/
        $itemsVisible = $quote->getAllItems();
        $this->_logger->info('------- Get Class Methods --------');
        $totalItems=count($quote->getAllItems());
        $this->_logger->info('Total Items: '.$totalItems);

        if ($callfrom == 0) {
            $cart_data = [
                'event' => ''.$this->_gtmHelper->getEventNameAdd(),
                'eventCategory' => 'Ecommerce',
                'eventAction'=> 'Add to Cart'
            ];
        } elseif($callfrom == 2) {
            $cart_data = [
                'event' => ''.$this->_gtmHelper->getEventNameUpdate(),
                'eventCategory' => 'Ecommerce',
                'eventAction'=> 'Add to Cart'
            ];
        } else {
            $cart_data = [
                'event' => ''.$this->_gtmHelper->getEventNameRemove(),
                'eventCategory' => 'Ecommerce',
                'eventAction'=> 'Remove from Cart'
            ];
        }

        $cart_data['ecommerce']['currencyCode'] = $currencyCode;
        $cart = [];
        $cart_data['ecommerce']['add']['products'] = [];
        if ($quote->getItemsCount() && $itemsVisible) {
            $this->_logger->info(' --- Quotes has data ----');
            foreach ($itemsVisible as $item) {
                $this->_logger->info(' --- ForEach Loop ----');
                $product = $item->getProduct();
                $this->_logger->info('');
                $this->_logger->info(' Product ID:  '.$product->getId());
                $this->_logger->info(' productId:  '.$productId);
                if ($product->getId() == $productId) {
                    $product_type = $item->getProductType();
                    if ($product_type == 'configurable') {
                        $configurable_product = true;
                    }
                    $attributes = $item->getProduct()->getAttributes();
                    $attr_array = [];
                    $getAttribute = $this->_gtmHelper->getAttributeValue($product);
                    $getCategoryArray = $getAttribute['category_array'];
                   
                    $categoryData = implode(',', $getCategoryArray);
                    $itemData = [
                    'id' => $product->getEntityId(),
                    'name' => $item->getName(),
                    'category' => $categoryData,
                    'brand' =>  $getAttribute['brand'],
                    'quantity' => $item->getQty() * 1,
                    'price' => $item->getPrice(),
                    'rating' => $this->_gtmHelper->getRating($product->getEntityId()),
                    ];

                    $this->_logger->info(print_r($itemData,true));
                    foreach ($getAttribute as $attr_key => $attr_value) {
                        if ($attr_key != 'category_array') {
                            $itemData[$attr_key] = $attr_value;
                        }
                    }

                    $cart[] = $itemData;
                    $cart_data['ecommerce']['add']['products'] = $cart;
                    $this->_logger->info(' Cart data added: ');
                    $this->_logger->info(print_r($cart, true));
                }
            }
        } else {
            $this->_logger->info('------ Unexpected Else Part ------');
        }

        if ($configurable_product) {
            $childData = [];
            $childProductData = $this->_productDetail->load($productId);
            $attributes = $childProductData->getAttributes();
            $attr_array = [];
            $getAttribute = $this->_gtmHelper->getAttributeValue($childProductData);
            $getCategoryArray = $getAttribute['category_array'];
           
            $categoryData = implode(',', $getCategoryArray);
            $childItemData = [
            'id' => $childProductData->getEntityId(),
            'name' => $childProductData->getName(),
            'category' => $categoryData,
            'brand' =>  $getAttribute['brand'],
            'quantity' => 1,
            'price' => $childProductData->getPrice(),
            'rating' => $this->_gtmHelper->getRating($childProductData->getEntityId()),
            ];

            foreach ($getAttribute as $attr_key => $attr_value) {
                if ($attr_key != 'category_array') {
                    $childItemData[$attr_key] = $attr_value;
                }
            }

            $childData[] = $childItemData;

            $parentProductData = $childProductData->getTypeInstance()->getParentIdsByChild($productId);
            $parentProductId = implode(',', $parentProductData);

            foreach ($cart_data['ecommerce']['add']['products'] as $cart_key => $cart_value) {
                if ($cart_value['id'] == $parentProductId) {
                    $cart_data['ecommerce']['add']['products'][$cart_key]['simple_product'] = $childData;
                }
            }
        }
           
        return $cart_data;
    }

    /**
     * Function for getting current cart data
     *
     * @return array
     */
    public function getCartData($result, $pageType, $currencyCode)
    {
        $quote = $this->getQuote();
        $cart_data = [
            'event' => 'checkout',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> 'Checkout',
            'eventLabel' => 'Step 1 - Shipping Address'
        ];
        $cart_data['ecommerce']['checkout']['actionField'] = [
            'step'=> 1,
            'option'=> 'not-selected'
        ];
        $cart_data = $this->prepareProductDataForCart($quote, $cart_data);

        return $cart_data;
    }

    /**
     * Function for getting categories name
     *
     * @return array
     */
    public function getCategoriesName($categoryIds)
    {
        $category_result = [];
        $collection = $this->_categoryCollectionFactory->create();
        $collection->addAttributeToSelect('*')->addAttributeToFilter('entity_id', $categoryIds);
        foreach ($collection as $category) {
            $category_result[] = $category->getName();
        }

        return implode(', ', $category_result);
    }

    /**
     * Function for getting product collection
     *
     * @return array
     */
    public function getProductCollection($productIds)
    {
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToSelect('*');
        $collection->addFieldToFilter(
            'entity_id',
            ['in' => $productIds]
        );
        return $collection;
    }

    /**
     * Function for preparing data for cart
     *
     * @return array
     */
    public function prepareProductDataForCart($quote, $cart_data)
    {
        $productIds = [];
        $itemData = [];
        $productQuoteData = [];

        if ($quote->getItemsCount()) {
            $items = [];
            foreach ($quote->getAllVisibleItems() as $item) {
                $product = $item->getProduct();
                $productIds[] = $product->getId();
                $productQuoteData[$product->getId()]['quantity'] = $item->getQty() * 1;
            }

            $productCollection = $this->getProductCollection($productIds);
            $attribute_data = $this->_gtmHelper->getAttributeMappingData();

            foreach ($productCollection as $product) {
                $quoteData = [
                    'id'=> $product->getEntityId(),
                    'name'=> $product->getName(),
                    'category'=> $this->getCategoriesName($product->getCategoryIds()),
                    'quantity'=> $productQuoteData[$product->getId()]['quantity'],
                    'price'=> $this->_gtmHelper->formatPrice($product->getPrice()),
                ];

                if (isset($productQuoteData[$product->getEntityId()]['variant'])) {
                    $quoteData['variant'] = $productQuoteData[$product->getEntityId()]['variant'];
                } else {
                    $quoteData['variant'] = '';
                }

                $quoteData = $this->generateAttributesData($attribute_data, $product, $quoteData);
                $itemData[] = $quoteData;
            }

            $cart_data['ecommerce']['checkout']['products'] = $itemData;
        }

        return $cart_data;
    }

    /**
     * Function for getting payment tag data
     *
     * @return array
     */
    public function getPaymentTagData()
    {
        $cart_data = [
            'event' => 'checkout',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> 'Checkout',
            'eventLabel' => 'Step 2 - Payment and Reviews'
        ];
        $cart_data['ecommerce']['checkout_option']['actionField'] = [
            'step'=> 2,
            'option'=> 'Cash On Delivery'
        ];
        
        $quote = $this->getQuote();
        $cart_data = $this->prepareProductDataForCart($quote, $cart_data);
        return $cart_data;
    }

    /**
     * Function for getting page type
     *
     * @return array
     */
    public function getPageType()
    {
        return $this->_pageType;
    }

    /**
     * Get active quote
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getQuote()
    {
        return $this->_checkoutSession->getQuote();
    }

    /**
     * Get placed order
     *
     * @return Quote
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getLastRealOrder()
    {
        return $this->_checkoutSession->getLastRealOrder();
    }

    /**
     * Function for getting order data for data layer
     *
     * @return array
     */
    public function getOrderData($result, $pageType, $currencyCode)
    {
        $order = $this->getLastRealOrder();
        $order_data = [
            'event' => 'checkout',
            'eventCategory' => 'Ecommerce',
            'eventAction'=> 'Purchase'
        ];
        $order_data['ecommerce']['currencyCode'] = $currencyCode;
        
        $action_field = [
            'id'=> $order->getEntityId(),
            'increment_id'=> $order->getIncrementId(),
            'affiliation'=> 'null',
            'revenue'=> $this->_gtmHelper->formatPrice($order->getBaseGrandTotal()),
            'tax' => $this->_gtmHelper->formatPrice($order->getBaseTaxAmount()),
            'shipping'=> $this->_gtmHelper->formatPrice($order->getBaseShippingAmount()),
            'coupon' => $order->getCouponCode()
        ];

        $order_data['ecommerce']['purchase']['actionField'] = $action_field;
        $productIds = [];
        $product_qty = [];

        $attribute_data = $this->_gtmHelper->getAttributeMappingData();

        foreach ($order->getItems() as $item) {
            if ($item->getProductType() == 'simple') {
                $productIds[] = $item->getProductId();
                $product_qty[$item->getProductId()] = $item->getQtyOrdered();
            }
        }

        $order_data['ecommerce']['purchase']['products']= $this->getProductDataForDataLayer($productIds, $product_qty);

        return $order_data;
    }

    /**
     * Function for getting product data for data layer
     *
     * @return array
     */
    public function getProductDataForDataLayer($productIds, $product_qty)
    {
        $productCollection = $this->getProductCollection($productIds);
        $attribute_data = $this->_gtmHelper->getAttributeMappingData();
        $product_data = [];
        $item_data = [];

        foreach ($productCollection as $product) {
            $product_data = [
                'id'=> $product->getEntityId(),
                'name'=> $product->getName(),
                'category'=> $this->getCategoriesName($product->getCategoryIds()),
                'quantity'=> $product_qty[$product->getEntityId()],
                'price'=> $this->_gtmHelper->formatPrice($product->getPrice()),
                'review'=> $this->_gtmHelper->getRating($product->getEntityId()),
            ];

            $product_data = $this->generateAttributesData($attribute_data, $product, $product_data);

            $item_data[] = $product_data;
        }

        return $item_data;
    }

     /**
      * Function for getting refund data for data layer
      *
      * @return array
      */
    public function getRefundOrderData()
    {
        $result = $this->_result;
        $pageType = $this->_pageType;
        $currencyCode = $this->_currencyCode;
        $productIds = [];
        $product_qty = [];
        if ($this->_request->getParam('order_id') !== null) {
            $order = $this->getCurrentOrderData();
            $result[1]['eventLabel'] = 'Order ID: '.$order->getIncrementId();
            $result[1]['eventValue'] = $this->_gtmHelper->formatPrice($order->getBaseGrandTotal());
            $result[1]['ecommerce']['currencyCode'] = $currencyCode;
            $result[1]['ecommerce']['refund']['actionField']['id'] = $order->getIncrementId();

            if ($this->_gtmHelper->isPartialRefundEnabled() != 0) {
                foreach ($order->getItems() as $item) {
                    if ($item->getProductType() == 'simple') {
                        $productIds[] = $item->getProductId();
                        $product_qty[$item->getProductId()] = $item->getQtyOrdered();
                    }
                }

                $pddlResult = $this->getProductDataForDataLayer($productIds, $product_qty);
                $result[1]['ecommerce']['refund']['products'] = $pddlResult;
                $refundBtnData = $this->_gtmHelper->refundButtonClassId();
                $this->_refundClassId = $refundBtnData;
            }
        }
        
        if (isset($result[1])) {
            return $result[1];
        } else {
            return $result;
        }
    }

    /**
     * Function for getting refund button if
     *
     * @return string
     */
    public function getRefundBtnClassId()
    {   
        return $this->_refundClassId;
    }

    /**
     * Function for getting current order
     *
     * @return object
     */
    public function getCurrentOrderData()
    {
        if ($this->_request->getParam('order_id') !== null) {
            return $this->_orderRepository->get($this->_request->getParam('order_id'));

        }
    }

    public function getLogger()
    {
        return $this->_logger;
    }

    public function setCartActionData()
    {
        if($this->_mageSession->getCartDataLayer()){
            echo '<script type="text/JavaScript">
            window.dataLayer.push('.$this->_mageSession->getCartDataLayer().');
            </script>';
            $this->_mageSession->unsCartDataLayer();
        }
    }

    public function setUpdateCartActionData()
    {
        if($this->_mageSession->getUpdatedCartDataLayer()){
            echo '<script type="text/JavaScript">
            window.dataLayer.push('.$this->_jsonHelper->jsonEncode($this->_mageSession->getUpdatedCartDataLayer()).');
            </script>';
            $this->_mageSession->unsUpdatedCartDataLayer();
        }
    }

    public function setRemoveCartDataLayer()
    {
        if($this->_mageSession->getRemoveCartDataLayer()){
            echo '<script type="text/JavaScript">
            window.dataLayer.push('.$this->_jsonHelper->jsonEncode($this->_mageSession->getRemoveCartDataLayer()).');
            </script>';
            $this->_mageSession->unsRemoveCartDataLayer();
        }
    }

    public function setDeleteCartDataLayer()
    {
        if($this->_mageSession->getDeleteCartDataLayer()){
            echo '<script type="text/JavaScript">
            window.dataLayer.push('.$this->_jsonHelper->jsonEncode($this->_mageSession->getDeleteCartDataLayer()).');
            </script>';
            $this->_mageSession->unsDeleteCartDataLayer();
        }
    }
}

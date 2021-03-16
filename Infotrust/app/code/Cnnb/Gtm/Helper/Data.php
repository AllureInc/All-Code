<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper Class
 * For providing extension configurations
 */
namespace Cnnb\Gtm\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product as Product;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Shipping\Model\Config;
use Magento\Payment\Model\Config as PaymentConfig;
use Magento\Review\Model\ReviewFactory;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Data Helper Class
 */
class Data extends AbstractHelper
{
    /**
     * Active flag
     */
    const XML_PATH_ACTIVE = 'google/analytics/active';

    /**
     * Account number
     */
    const XML_PATH_ACCOUNT = 'google/analytics/container_id';

    /**
     * Datalayer name
     */
    const XML_PATH_DATALAYER_NAME = 'googletagmanager/general/datalayer_name';

    /**
     * Path to configuration, check for attribute mapping
     */
    const XML_PATH_ATTRIBUTE_MAPPING = 'googletagmanager/attribute_mapping/attributes';

    /**
     * Path to configuration, check for banner mapping
     */
    const XML_PATH_BANNER_MAPPING = 'googletagmanager/banner_mapping/banners';

    /**
     * Path to configuration, check for banner mapping
     */
    const XML_PATH_BANNER_PROMOTION_MAPPING = 'googletagmanager/banner_promotion_mapping/banners';

    /**
    * Path to configuration, check for partial refund
    */
    const XML_PATH_PARTIAL_REFUND = 'googletagmanager/partial_refund/is_enabled';

    /**
    * Refund bitton Id or class
    */
    const XML_PARTIAL_REFUND_ID_CLASS = 'googletagmanager/partial_refund/refund_button';

    /**
    * Refund bitton Id or class
    */
    const XML_PRODUCT_CART_ADD = 'googletagmanager/product_cart/add';

    /**
    * Refund bitton Id or class
    */
    const XML_PRODUCT_CART_REMOVE = 'googletagmanager/product_cart/remove';

    /**
    * Refund bitton Id or class
    */
    const XML_PRODUCT_CART_UPDATE = 'googletagmanager/product_cart/update';

    /**
     * @var string
     */
    protected $_dataLayerName = 'dataLayer';

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $scopeConfig;

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $shippingmodelconfig;

    /**
     * @var Config
     */
    protected $paymentModelConfig;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
    
    /**
     * @var string
     */
    protected $ratingFactory;
    
    /**
     * @var string
     */
    protected $_productData = '';

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        Product $productData,
        Config $shippingmodelconfig,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        \Magento\Review\Model\Rating $ratingFactory,
        \Psr\Log\LoggerInterface $logger,
        PaymentConfig $paymentConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->_productData = $productData;
        $this->shippingmodelconfig = $shippingmodelconfig;
        $this->paymentModelConfig = $paymentConfig;
        $this->storeManager = $storeManager;
        $this->reviewFactory = $reviewFactory;
        $this->ratingFactory = $ratingFactory;
        $this->_logger = $logger;
    }

    /**
     * Whether Tag Manager is ready to use
     *
     * @param null $store_id
     * @return bool
     */
    public function isEnabled($store_id = null)
    {
        $accountId = $this->scopeConfig->getValue(
            self::XML_PATH_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
      
        $active = $this->scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
        
        return $accountId && $active;
    }

    /**
     * @param null $store_id
     * @return int
     */
    public function addJsInHead($store_id = null)
    {
        return (int) $this->scopeConfig->isSetFlag(
            'googletagmanager/gdpr/add_js_in_header',
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * Get Tag Manager Account ID
     *
     * @param null $store_id
     * @return null | string
     */
    public function getAccountId($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * Format Price
     *
     * @param $price
     * @return float
     */
    public function formatPrice($price)
    {
        return (float)sprintf('%.2F', $price);
    }

    /**
     * @param null $store_id
     * @return string
     */
    public function getDataLayerName($store_id = null)
    {
        if (!$this->_dataLayerName) {
            $this->_dataLayerName = $this->scopeConfig->getValue(
                self::XML_PATH_DATALAYER_NAME,
                ScopeInterface::SCOPE_STORE,
                $store_id
            );
        }
        
        return $this->_dataLayerName;
    }

    /**
     * @param $name
     * @return $this
     */
    public function setDataLayerName($name)
    {
        $this->_dataLayerName = $name;
        return $this;
    }

    /**
     * Function for retrive existing attribute mapping
     * @return array
     */
    public function getAttributeMappingData($store_id = null)
    {
        $data = [];
        $attribute_json = json_decode($this->scopeConfig->getValue(
            self::XML_PATH_ATTRIBUTE_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $store_id
        ), true);
        if (!empty($attribute_json)) {
            foreach ($attribute_json as $key => $value) {
                $data[$value['gtm_attribute']] = $value['magento_attribute'];
            }
        }

        return $data;
    }

    /**
     * Function for getting attribute mapping
     * @return array
     */
    public function getAttributeValue($product)
    {
        $attributes = $this->getAttributeMappingData();
        $product_id = $product->getId();
        $attrArray = [];
        if ($product_id) {
            $productData = $this->_productData->load($product_id);
            $attrArray['brand'] = $productData->getData('brand');
            $collection = $product->getCategoryCollection()->addAttributeToSelect('name');
            $categories = [];
            if ($collection->getSize()) {
                foreach ($collection as $category) {
                    if (!in_array($category->getName(), $categories)) {
                        $categories[] = $category->getName();
                    }
                }
            }

            $attrArray['category_array'] = $categories;

            foreach ($attributes as $attr_key => $attr_value) {
                if ($productData->getData($attr_value)) {
                    if ($attr_value == "quantity_and_stock_status") {
                        $in_stock = $productData->getData($attr_value);
                        $attr_value = ($in_stock['is_in_stock'] == 1) ? __('in stock') : __('out of stock');
                        $attrArray[$attr_key] = $attr_value;
                    } else {
                        $attrArray[$attr_key] = $productData->getData($attr_value);
                    }
                }
            }
        }

        return $attrArray;
    }

    /**
     * Function for retrive existing banner mapping
     * @return array
     */
    public function getBannerMappingData($store_id = null)
    {
        $data = [];
        $banner_json = json_decode($this->scopeConfig->getValue(
            self::XML_PATH_BANNER_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $store_id
        ), true);

        if (!empty($banner_json)) {
            foreach ($banner_json as $key => $value) {
                $data[$value['gtm_event_label']][] = $value;
            }
        }

        return $data;
    }

    /**
     * Function for retrive promotion click mapping data
     * @return array
     */
    public function getPromotionClickMappingData($store_id = null)
    {
        $data = [];
        $banner_json = json_decode($this->scopeConfig->getValue(
            self::XML_PATH_BANNER_PROMOTION_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $store_id
        ), true);

        if (!empty($banner_json)) {
            foreach ($banner_json as $key => $value) {
                $data[$value['gtm_event_label']][] = $value;
            }
        }

        return $data;
    }

    /**
     * Function for retrive active shipping methods
     * @return array
     */
    public function getActiveShippingMethod()
    {
        $shippings = $this->shippingmodelconfig->getActiveCarriers();
        $methods = [];
        foreach ($shippings as $shippingCode => $shippingModel) {
            if ($carrierMethods = $shippingModel->getAllowedMethods()) {
                foreach ($carrierMethods as $methodCode => $method) {
                    $code = $shippingCode.'_'.$methodCode;
                    $carrierTitle = $this->scopeConfig->getValue('carriers/'. $shippingCode.'/title');
                    $methods[$code] = $carrierTitle;
                }
            }
        }

        return $methods;
    }

    /**
     * Function for retrive active payment methods
     * @return array
     */
    public function getActivePaymentMethod()
    {
        $payments = $this->paymentModelConfig->getActiveMethods();
        $methods = [];
        foreach ($payments as $paymentCode => $paymentModel) {
            $paymentTitle = $this->scopeConfig->getValue('payment/'.$paymentCode.'/title');
            $methods[$paymentCode] = $paymentTitle;
        }

        return $methods;
    }

    /**
     * @return product rating
     */
    public function getRating($productId)
    {
        $_ratingSummary = $this->ratingFactory->getEntitySummary($productId);
        $ratingCollection = $this->reviewFactory->create()->getResourceCollection()->addStoreFilter(
            $this->storeManager->getStore()->getId()
        )->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)->addEntityFilter('product', $productId);
        $review_count = count($ratingCollection);

        return $review_count;
    }

    /**
     * Get status of partial refund
     *
     * @param null $store_id
     * @return null | string
     */
    public function isPartialRefundEnabled($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_PARTIAL_REFUND,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * Get id or class of refund button
     *
     * @param null $store_id
     * @return null | string
     */
    public function refundButtonClassId($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PARTIAL_REFUND_ID_CLASS,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * cart action events
     *
     * @param null $store_id
     * @return null | string
     */
    public function getEventNameAdd($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PRODUCT_CART_ADD,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * cart action events
     *
     * @param null $store_id
     * @return null | string
     */
    public function getEventNameRemove($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PRODUCT_CART_REMOVE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * cart action events
     *
     * @param null $store_id
     * @return null | string
     */
    public function getEventNameUpdate($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PRODUCT_CART_UPDATE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }
}

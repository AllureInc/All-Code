<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper Class
 * For providing extension configurations
 */
namespace Cnnb\GtmWeb\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Locale\Resolver as Resolver;
use Magento\Newsletter\Model\SubscriberFactory as Subscriber;

/**
 * Data Helper Class
 */
class Data extends AbstractHelper
{
    /**
     * Active flag
     */
    const XML_PATH_ACTIVE = 'googletagmanager/general/active';

    /**
     * Web Active flag
     */
    const XML_PATH_GTM_WEB_ACTIVE = 'googletagmanager_web/module_status/is_enabled';

    /**
     * Account number
     */
    const XML_PATH_ACCOUNT = 'googletagmanager/general/account';

    /**
     * Datalayer name
     */
    const XML_PATH_DATALAYER_NAME = 'googletagmanager/general/datalayer_name';

    /**
     * Registration value
     */
    const XML_PATH_REGISTRATION_VALUE = 'googletagmanager_web/login_registration_mapping/registration_ecomm';

    /**
     * Login value
     */
    const XML_PATH_LOGIN_VALUE = 'googletagmanager_web/login_registration_mapping/login_ecomm';

    /**
     * Path to configuration, check for attribute mapping
     */
    const XML_PATH_MEGA_MENU_ECOMMERCE = 'googletagmanager_web/mega_menu_mapping/ecommerce';

    /**
     * PDP tab value
     */
    const XML_PATH_PDP_TAB_MAPPING = 'googletagmanager_web/pdp_tab_mapping/pdp';

    /**
     * Path to configuration, check for attribute mapping
     */
    const XML_PATH_MEGA_MENU_MAPPING = 'googletagmanager_web/mega_menu_mapping/mapping';

    /**
     * Path to configuration, check for pdp mapping
     */
    const XML_PATH_PDP_MAPPING = 'googletagmanager_web/pdp_mapping/pdp';

    /**
     * Path to configuration, check for pda arrow mapping
     */
    const XML_PATH_PDP_ARROW_MAPPING = 'googletagmanager_web/pdp_arrow_mapping/pdp_arrow';
    /**
     * Path to configuration, check for saloon mapping
     */
    const XML_PATH_SALOON_MAPPING = 'googletagmanager_web/find_saloon_mapping/saloon';
    
    /**
     * Path to configuration, check for footer mapping
     */
    const XML_PATH_FOOTER_MAPPING = 'googletagmanager_web/footer_mapping/mapping';

    /**
     * Path to configuration, check for footer mapping
     */
    const XML_PATH_FOOTER_ECOMMERCE = 'googletagmanager_web/footer_mapping/ecommerce';

    /**
     * Path to configuration, check for related article mapping
     */
    const XML_PATH_RELATED_ARTICAL_MAPPING = 'googletagmanager_web/related_artical_mapping/mapping';

    /**
     * Path to configuration, check for related article ecommerce
     */
    const XML_PATH_RELATED_ARTICAL_ECOMMERCE = 'googletagmanager_web/related_artical_mapping/ecommerce';

    /**
     * Path to configuration, check for wishlist mapping
     */
    const XML_PATH_WISHLIST_MAPPING = 'googletagmanager_web/wishlist_mapping/wishlist';

    /**
     * Path to configuration, check for newsletter mapping
     */
    const XML_PATH_NEWSLETTER_MAPPING = 'googletagmanager_web/newsletter_mapping/newsletter';

    /**
     * Path to configuration, check for newsletter ecommerce
     */
    const XML_PATH_NEWSLETTER_ECOMMERCE = 'googletagmanager_web/newsletter_mapping/ecommerce';

    /**
     * Path to configuration, check for chat with us mapping
     */
    const XML_PATH_CHATWITHUS_MAPPING = 'googletagmanager_web/chatwithus_mapping/chatwithus';

    /**
     * Path to configuration, check for chat with us ecommerce
     */
    const XML_PATH_CHATWITHUS_ECOMMERCE = 'googletagmanager_web/chatwithus_mapping/ecommerce';

    /**
     * Path to configuration, check for country mapping
     */
    const XML_PATH_COUNTRY_SELECTOR_ELEMENT = 'googletagmanager_web/country_selector_mapping/element_name';

    /**
     * Path to configuration, check for country mapping
     */
    const XML_PATH_COUNTRY_SELECTOR_ECOMMERCE = 'googletagmanager_web/country_selector_mapping/ecommerce';

    /**
     * Path to configuration, check for language mapping
     */
    const XML_PATH_LANGUAGE_SELECTOR_ELEMENT = 'googletagmanager_web/language_selector_mapping/element_name';

    /**
     * Path to configuration, check for language mapping
     */
    const XML_PATH_LANGUAGE_SELECTOR_ECOMMERCE = 'googletagmanager_web/language_selector_mapping/ecommerce';

    /**
     * Path to configuration, check for psp mapping
     */
    const XML_PATH_PSP_FILTER_ECOMMERCE = 'googletagmanager_web/psp_configuration/filter_ecommerce';

    /**
     * Path to configuration, check for psp cta mapping
     */
    const XML_PATH_PSP_CTA_MAPPING = 'googletagmanager_web/psp_configuration/cta_mapping';

    /**
     * Path to configuration, check for psp cta mapping
     */
    const XML_PATH_PSP_CTA_ECOMMERCE = 'googletagmanager_web/psp_configuration/cta_ecommerce';

    /**
     * Path to configuration, check for psp cta mapping
     */
    const XML_PATH_PSP_OTHER_SERVICES_MAPPING = 'googletagmanager_web/psp_configuration/other_services_mapping';

    /**
     * Path to configuration, check for psp cta mapping
     */
    const XML_PATH_PSP_OTHER_SERVICES_ECOMMERCE = 'googletagmanager_web/psp_configuration/other_services_ecommerce';

    /**
     * Get country path
     */
    const COUNTRY_CODE_PATH = 'general/country/default';

    /**
     * Get start button config of diagnosis
     */
    const XML_PATH_START_DIAGNOSIS_BUTTON = 'googletagmanager_web/diagnose_hair_configuration/start_button';

    /**
     * Path to configuration, getting diagnosis attribute mapping
     */
    const XML_DIAGNOSIS_ATTRIBUTE = 'googletagmanager_web/diagnose_hair_configuration/diagnosis_attribute_mapping';

    /**
     * Get next button config of diagnosis
     */
    const XML_PATH_DIAGNOSIS_NEXT_BUTTON_MAPPING = 'googletagmanager_web/diagnose_hair_configuration/next_button';

    /**
     * Path to configuration, check for attribute mapping
     */
    const XML_PATH_RATING_MAPPING = 'googletagmanager_web/rating_mapping/mapping';

    /**
     * Path to configuration, check for attribute mapping
     */
    const XML_PATH_RATING_ECOMMERCE = 'googletagmanager_web/rating_mapping/ecommerce';

    /**
     * Path to configuration, check for back to top mapping
     */
    const XML_PATH_BACK_TO_TOP_EVENTLABEL = 'googletagmanager_web/back_to_top_mapping/eventLabel';

    /**
     * Path to configuration, check for back to top mapping
     */
    const XML_PATH_BACK_TO_TOP_ECOMMERCE = 'googletagmanager_web/back_to_top_mapping/ecommerce';

    /**
     * Path to configuration, check for back to top mapping
     */
    const XML_PATH_BACK_TO_TOP_ELEMENT = 'googletagmanager_web/back_to_top_mapping/element';

    /**
     * Path to configuration, check for back to top mapping
     */
    const XML_PATH_KPROFILE_EMAIL_ELEMENT = 'googletagmanager_web/kprofile_email/element';

    /**
     * Path to configuration, check brand mapping
     */
    const XML_PATH_DIAGNOSIS_BRAND_ELEMENT = 'googletagmanager_web/diagnose_hair_configuration/brand';

    /**
     * Path to configuration, check steps to options mapping
     */
    const XML_PATH_DIAGNOSIS_STEP_OPTIONS_MAPPING = 'googletagmanager_web/step_option_configuration/step_options';

    /**
     * Get reset button config of diagnosis
     */
    const XML_PATH_RESET_DIAGNOSIS_BUTTON = 'googletagmanager_web/diagnose_hair_configuration/reset_button';

    /**
     * Get previous button config of diagnosis
     */
    const XML_PATH_PREVIOUS_DIAGNOSIS_BUTTON = 'googletagmanager_web/diagnose_hair_configuration/previous_button';

    /**
     * Get previous button config of diagnosis
     */
    const XML_PATH_RESULT_DIAGNOSIS_BUTTON = 'googletagmanager_web/diagnose_hair_configuration/see_the_result_button';

    /**
     * Path to configuration, check for diagnosis email element mapping
     */
    const XML_PATH_DIAGNOSE_EMAIL_ELEMENT = 'googletagmanager_web/diagnose_email/element';

    /**
     * Path to configuration, check for diagnosis email submit element mapping
     */
    const XML_PATH_DIAGNOSE_EMAIL_SUBMIT_ELEMENT = 'googletagmanager_web/diagnose_email_submit/element';
    
    /**
     * Path to configuration, check for diagnosis shop now cta mapping
     */
    const XML_PATH_DIAGNOSE_SHOP_NOW_CTA = 'googletagmanager_web/diagnose_shop_now_cta/element';

    /**
     * Path to configuration, check for diagnosis newsletter element mapping
     */
    const XML_PATH_DIAGNOSE_NEWSLETTER_ELEMENT = 'googletagmanager_web/diagnose_newsletter/element';

    /**
     * Path to configuration, check for diagnosis newsletter event category mapping
     */
    const XML_PATH_DIAGNOSE_NEWSLETTER_EVENTACATEGORY = 'googletagmanager_web/diagnose_newsletter/eventCategory';

    /**
     * Path to configuration, check for diagnosis newsletter event action mapping
     */
    const XML_PATH_DIAGNOSE_NEWSLETTER_EVENTACTION = 'googletagmanager_web/diagnose_newsletter/eventAction';

    /**
     * Path to configuration, check for diagnosis newsletter event label mapping
     */
    const XML_PATH_DIAGNOSE_NEWSLETTER_EVENTLABEL = 'googletagmanager_web/diagnose_newsletter/eventLabel';

    /**
     * Path to configuration, check for diagnosis cgu element mapping
     */
    const XML_PATH_DIAGNOSE_CGU_ELEMENT = 'googletagmanager_web/diagnose_cgu/element';

    /**
     * Path to configuration, check for diagnosis cgu event category mapping
     */
    const XML_PATH_DDIAGNOSE_CGU_EVENTACATEGORY = 'googletagmanager_web/diagnose_cgu/eventCategory';

    /**
     * Path to configuration, check diagnosis cgu event action mapping
     */
    const XML_PATH_DIAGNOSE_CGU_EVENTACTION = 'googletagmanager_web/diagnose_cgu/eventAction';

    /**
     * Path to configuration, check for diagnosis cgu event label mapping
     */
    const XML_PATH_DIAGNOSE_CGU_EVENTLABEL = 'googletagmanager_web/diagnose_cgu/eventLabel';

    /**
     * Get previous button config of diagnosis
     */
    const XML_PATH_DIAGNOSIS_STEP = 'googletagmanager_web/diagnose_hair_configuration/step_counter';

    /**
     * @var string
     */
    protected $_dataLayerName = 'dataLayer';

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var megaMenuClasses
     */
    protected $_megaMenuClasses = [];

    /**
     * @var pdpClassesAndIds
     */
    protected $_pdpClassesAndIds = [];

    /**
     * @var Product
     */
    protected $_product = [];

    /**
     * @var FooterClassesAndIds
     */
    protected $_footerClassesAndIds = [];

    /**
     * @var Registry
     */
    protected $_registry = [];

    /**
     * @var pdpTabClassesAndIds
     */
    protected $_pdpTabClassesAndIds = [];

    /**
     * @var pdpArrowClassesAndIds
     */
    protected $_pdpArrowClassesAndIds = [];

    /**
     * @var findSaloonClassesAndIds
     */
    protected $_findSaloonClassesAndIds = [];

    /**
     * @var relatedArticleClassesAndIds
     */
    protected $_relatedArticleClassesAndIds = [];

    /**
     * @var newsletterClassesAndIds
     */
    protected $_newsletterClassesAndIds = [];

    /**
     * @var chatwithusClassesAndIds
     */
    protected $_chatwithusClassesAndIds = [];

    /**
     * @var diagnosisStepsOptionsData
     */
    protected $_diagnosisStepsOptionsData = [];
    /**
     * Core registry
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Customer session
     * @var customerSession
     */
    protected $_customerSession = null;

    /**
     * @var Request
     */
    protected $_request;

    /**
     * @var EntityAttributeCollection
     */
    protected $_entityAttributeCollection;

    /**
     * @var EntityAttribute
     */
    protected $_entityAttribute;

    /**
     * @var AttributeOptionCollection
     */
    protected $_attributeOptionCollection;

    /**
     * @var PspCtaClassesAndIds
     */
    protected $_pspCtaClassesAndIds;

    /**
     * @var Resolver
     */
    private $_localeResolver;

    /**
     * Get store detail
     */
    protected $_storeManager;

    /**
     * @var subscriber
     */
    protected $_subscriber;

    /**
     * @var RatingClassesAndIds
     */
    protected $_ratingClassesAndIds;

    /**
     * @var Cms Page
     */
    protected $_cmsPage;

    /**
     * @var DiagnosticSendByEmailMapping
     */
    protected $_diagnosticSendByEmailElements = [];

    /**
     * @var DiagnosticSendByEmailSubmitMapping
     */
    protected $_diagnosticSendByEmailSubmitElements = [];

    /**
     * @var DiagnoseShopNowCta
     */
    protected $_diagnoseShopNowCta = [];

    /**
     * @var $pspOtherServiceClassesAndIds
     */
    protected $_pspOtherServiceClassesAndIds = [];

    /**
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param Magento\Framework\Registry $registry
     * @param Magento\Customer\Model\Session $customerSession
     * @param Magento\Framework\App\Request\Http $request
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $entityAttributeCollection,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection,
        \Magento\Customer\Model\Session $customerSession,
        Resolver $localeResolver,
        Subscriber $subscriber,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Cms\Model\Page $cmsPage
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_request = $request;
        $this->_registry = $registry;
        $this->_customerSession = $customerSession;
        $this->_entityAttributeCollection = $entityAttributeCollection;
        $this->_entityAttribute = $entityAttribute;
        $this->_attributeOptionCollection = $attributeOptionCollection;
        $this->_localeResolver = $localeResolver;
        $this->_subscriber = $subscriber;
        $this->_storeManager = $storeManager;
        $this->_cmsPage = $cmsPage;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Whether Tag Manager is ready to use
     * @return bool
     */
    public function isEnabled()
    {
        $accountId = $this->_scopeConfig->getValue(
            self::XML_PATH_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $active = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $accountId && $active;
    }

    /**
     * Whether GTM (Web) Tag Manager is ready to use
     * @return bool
     */
    public function getCurrentModuleStatus()
    {
        $active = $this->_scopeConfig->isSetFlag(
            self::XML_PATH_GTM_WEB_ACTIVE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $active;
    }

    /**
     * @return int
     */
    public function addJsInHead()
    {
        return (int) $this->_scopeConfig->isSetFlag(
            'googletagmanager/gdpr/add_js_in_header',
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Tag Manager Account ID
     * @return null | string
     */
    public function getAccountId()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_ACCOUNT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * DataLayer Name
     * @return string
     */
    public function getDataLayerName()
    {
        return $this->_dataLayerName;
    }

    /**
     * @return register value (type string)
     */
    public function getRegisterValue()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_REGISTRATION_VALUE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return login value (type string)
     */
    public function getLoginValue()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_LOGIN_VALUE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * @return tab configuration value array
     */
    public function getPdpTabConfiguration()
    {
        $data = [];
        $pdp_tab_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_PDP_TAB_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);
        if (!empty($pdp_tab_json)) {
            foreach ($pdp_tab_json as $key => $value) {
                $this->_pdpTabClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']] = $value['gtm_ecommerce'];
            }
        }
        if ($this->_registry->registry('current_product')) {
            $data['product_name'] = $this->_registry->registry('current_product')->getName();
            $data['product_id'] = $this->_registry->registry('current_product')->getEntityId();
        }
        return $data;
    }

    /**
     * Function for getting product tab classes
     * @return array
     */
    public function getPdpTabClassesAndIds()
    {
        return json_encode($this->_pdpTabClassesAndIds);
    }

    /**
     * Function for retrive existing Mega Menu Mapping
     * @return array
     */
    public function getMegaMenuMappingData()
    {
        $data = [];
        $attribute_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_MEGA_MENU_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        if (!empty($attribute_json)) {
            foreach ($attribute_json as $key => $value) {
                $this->_megaMenuClasses[] = $value['gtm_class_name'];
                $data[$value['gtm_class_name']] = $value['mega_menu_level'];
            }
        }

        return $data;
    }

    /**
     * Get Mega Menu Classes From the Admin Configuration
     */
    public function getMegaMenuClasses()
    {
        return json_encode($this->_megaMenuClasses);
    }

    /**
     * Get PDP Admin Configuration
     * @return Array
     */
    public function getPdpConfiguration()
    {
        $data = [];
        $pdp_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_PDP_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        if (!empty($pdp_json)) {
            foreach ($pdp_json as $key => $value) {
                $this->_pdpClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']] = $value['gtm_event_action'];
            }
        }

        if ($this->_registry->registry('current_product')) {
            $data['product_name'] = $this->_registry->registry('current_product')->getName();
        }

        return $data;
    }

    /**
     * Get PDP Class and IDs from the Admin Configuration
     */
    public function getpdpClassesAndIds()
    {
        return json_encode($this->_pdpClassesAndIds);
    }

    /**
     * Get PDP Admin Configuration for product picture arrows
     * @return Array
     */
    public function getPdpArrowConfiguration()
    {
        $data = [];
        $pdp_arrow_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_PDP_ARROW_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);
        if (!empty($pdp_arrow_json)) {
            foreach ($pdp_arrow_json as $key => $value) {
                $this->_pdpArrowClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']] = $value['gtm_ecommerce'];
            }
        }
        return $data;
    }

    /**
     * Get PDP Class and IDs from the Admin Configuration for product carousel arrows
     */
    public function getPdpArrowClassesAndIds()
    {
        return json_encode($this->_pdpArrowClassesAndIds);
    }
    
    /**
     * Get configuration for find saloon
     */
    public function getSaloonConfiguration()
    {
        $data = [];
        $saloon_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_SALOON_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);
        if (!empty($saloon_json)) {
            foreach ($saloon_json as $key => $value) {
                $this->_findSaloonClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['ecommerce'] = $value['gtm_ecommerce'];
                $data[$value['gtm_element_name']]['is_diagnose'] = $value['gtm_element_is_diagnose_page'];
            }
        }
        
        return $data;
    }

    /**
     * Get class and id from admin configuration
     */
    public function getSaloonClassesAndIds()
    {
        return json_encode($this->_findSaloonClassesAndIds);
    }

    /**
     * Get Mega Menu Ecommerce Value
     * @return string
     */
    public function getMegaMenuEcommerceValue()
    {
        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_MEGA_MENU_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $ecommerce_value;
    }

    /**
     * Get Footer Link Mapping Configuration
     * @return array
     */
    public function getFooterLinkMapping()
    {
        $data = [];

        $footer_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_FOOTER_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_FOOTER_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $data['ecommerce'] = $ecommerce_value;

        if (!empty($footer_json)) {
            foreach ($footer_json as $key => $value) {
                $this->_footerClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['head'] = $value['gtm_element_for'];
                $data[$value['gtm_element_name']]['label'] = $value['gtm_element_lebel'];
            }
        }

        return $data;
    }

    /**
     * Get Footer Classes and Ids
     * @return array
     */
    public function getFooterClasesAndIds()
    {
        return $this->_footerClassesAndIds;
    }

    /**
     * Get Related Articles Mapping
     * @return array
     */
    public function getRelatedArticlesMapping()
    {
        $data = [];

        $footer_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_RELATED_ARTICAL_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_RELATED_ARTICAL_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $data['ecommerce'] = $ecommerce_value;

        if (!empty($footer_json)) {
            foreach ($footer_json as $key => $value) {
                $this->_relatedArticleClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = $value['gtm_element_event_category'];
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_event_action'];
                $data[$value['gtm_element_name']]['eventLabel'] = $value['gtm_element_event_label'];
            }
        }

        return $data;
    }

    /**
     * Get Related Article Classes and Ids
     * @return array
     */
    public function getRelatedArticleClasesAndIds()
    {
        return $this->_relatedArticleClassesAndIds;
    }

    /**
     * Get wish list class, id and product name
     * @return array
     */
    public function getWishListClassIdsAndProduct()
    {

        $data = [];
        $wishlist_value = $this->_scopeConfig->getValue(
            self::XML_PATH_WISHLIST_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $data[] = $wishlist_value;
        return $data;
    }

    /**
     * Retrieve current category model object
     * @return \Magento\Catalog\Model\Category
     */
    public function getProductNameAndId()
    {
        $category = $this->_registry->registry('current_category');
        if ($category) {
            $productCollection = $category->getProductCollection()->addAttributeToSelect('*');
            $productsData = [];
            foreach ($productCollection as $product) {
                $products['id'] = $product->getId();
                $products['name'] = $product->getName();
                $productsData[] = $products;
            }
            return $productsData;
        }
    }

    /**
     * Retrieve current customer data
     * @return customer id
     */
    public function getCustomerStatus()
    {
        return $this->_customerSession->getCustomer()->getId();
    }

    /**
     * Retrieve product data from detail page
     * @return array
     */
    public function getNameFromDetailPage()
    {
        $productsData = [];
        if ($this->_registry->registry('current_product')) {
            $products['id'] = $this->_registry->registry('current_product')->getId();
            $products['name'] = $this->_registry->registry('current_product')->getName();
            $productsData[] = $products;
        }
        return $productsData;
    }

    /**
     * Retrieve news letter config data
     * @return array
     */
    public function getNewsletterConfig()
    {
        $data = [];

        $newsletter_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_NEWSLETTER_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_NEWSLETTER_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $customer_name  = '';
        if ($this->_customerSession->getCustomer()->getId()) {
            $customer_name = $this->_customerSession->getCustomer()->getName();
        }

        $data['ecommerce'] = $ecommerce_value;
        $data['customer'] = $customer_name;

        if (!empty($newsletter_json)) {
            foreach ($newsletter_json as $key => $value) {
                $this->_newsletterClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']] = $value['gtm_label'];
            }
        }

        return $data;
    }

    /**
     * Retrieve news letter class and ids
     * @return array
     */
    public function getNewsletterClassId()
    {
        return $this->_newsletterClassesAndIds;
    }
    
    /**
     * Retrieve chat with us data
     * @return array
     */
    public function getChatwithusConfig()
    {
        $data = [];

        $chatwithus_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_CHATWITHUS_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_CHATWITHUS_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        
        $data['ecommerce'] = $ecommerce_value;

        if (!empty($chatwithus_json)) {
            foreach ($chatwithus_json as $key => $value) {
                $this->_chatwithusClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']] = $value['gtm_label'];
            }
        }

        return $data;
    }

    /**
     * Retrieve chat with us class and ids
     * @return array
     */
    public function getChatwithusClassId()
    {
        return $this->_chatwithusClassesAndIds;
    }

    /**
     * Get Country Selector's Configurations
     * @return array
     */
    public function getCountrySelectorsConfig()
    {
        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_COUNTRY_SELECTOR_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $element_value = $this->_scopeConfig->getValue(
            self::XML_PATH_COUNTRY_SELECTOR_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return ['element'=> $element_value, 'ecommerce'=> $ecommerce_value];
    }

    /**
     * Get Language Selector's Configurations
     * @return array
     */
    public function getLanguageSelectorsConfig()
    {
        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_LANGUAGE_SELECTOR_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $element_value = $this->_scopeConfig->getValue(
            self::XML_PATH_LANGUAGE_SELECTOR_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return ['element'=> $element_value, 'ecommerce'=> $ecommerce_value];
    }

    /**
     * Get current category data
     * @return array
     */
    public function getCurrentCategory()
    {
        return $this->_registry->registry('current_category');
    }
    
    /**
     * Get filter data
     * @return array
     */
    public function getPspFilterData()
    {
        $data = [];
        $params = $this->_request->getParams();

        if (isset($params['id'])) {
            unset($params['id']);
        }

        $category = $this->getCurrentCategory();
        if ($category && !empty($params)) {
            $data['eventCategory'] = 'product selector page';
            $data['eventAction'] = 'filter:: '.$category->getName().' :: collection';
            $data['eventLabel'] = ''.$this->getCurrentCategoryFilters($params);
            $data['ecommerce'] = ''.$this->getPspFilterEcommerce();
        }

        return $data;
    }

    /**
     * Get current category filters data
     *
     * @return array
     */
    public function getCurrentCategoryFilters($params)
    {
        $filters = [];

        foreach ($params as $attributeCode => $value) {
            if ($attribute = $this->getAttributeInfo($attributeCode)) {
                $filters[] = $attribute->getFrontendLabel();
            }
        }

        if (!empty($filters)) {
            return implode(' | ', $filters);
        } else {
            return 'No Filters';
        }
    }

    /**
     * Load attribute data by code
     * @param   string $attributeCode
     * @return  \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeInfo($attributeCode)
    {
        $attribute = $this->_entityAttribute->loadByCode('catalog_product', $attributeCode);
        return ($attribute->hasData() > 0) ? $attribute : null;
    }

    public function getPspFilterEcommerce($store_id = 0)
    {
        $element_value = $this->_scopeConfig->getValue(
            self::XML_PATH_PSP_FILTER_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $element_value;
    }

    /**
     * Get PSP CTA Mapping
     *
     * @return array
     */
    public function getPspCtaMapping()
    {
        $data = [];

        $cta_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_PSP_CTA_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_PSP_CTA_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $data['ecommerce'] = $ecommerce_value;

        if (!empty($cta_json)) {
            foreach ($cta_json as $key => $value) {
                $this->_pspCtaClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = $value['gtm_element_event_category'];
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_event_action'];
                $data[$value['gtm_element_name']]['eventLabel'] = $value['gtm_element_event_label'];
            }
        }

        return $data;
    }

    /**
     * Get PSP CTA Classes and Ids
     *
     * @return array
     */
    public function getPspCtaClasesAndIds()
    {
        return $this->_pspCtaClassesAndIds;
    }

    /**
     * Get PSP Other Services Mapping
     *
     * @return array
     */
    public function getPspOtherServicesMapping()
    {
        $data = [];

        $other_services_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_PSP_OTHER_SERVICES_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_PSP_OTHER_SERVICES_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $data['ecommerce'] = $ecommerce_value;

        if (!empty($other_services_json)) {
            foreach ($other_services_json as $key => $value) {
                $this->_pspOtherServiceClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = $value['gtm_element_event_category'];
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_event_action'];
                $data[$value['gtm_element_name']]['eventLabel'] = $value['gtm_element_event_label'];
            }
        }

        return $data;
    }

    /**
     * Get PSP Other Service Classes and Ids
     *
     * @return array
     */
    public function getOtherServiceClassesAndIds()
    {
        return $this->_pspOtherServiceClassesAndIds;
    }

    /**
     * get country name
     * @return string
     */
    public function getCountryValue()
    {
        $country = $this->_scopeConfig->getValue(
            self::COUNTRY_CODE_PATH,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $country;
    }

    /**
     * Get languange
     * @return string
     */
    public function getCurrentLocale()
    {
        $currentLocaleCode = $this->_localeResolver->getLocale(); // fr_CA
        $languageCode = strstr($currentLocaleCode, '_', true);
        return $languageCode;
    }

    /**
     * Get subscription status
     * @return string
     */
    public function checkSubscription($customerId)
    {
        $status = 'false';
        $checkSubscriber = $this->_subscriber->create()->loadByCustomerId((int)$customerId);
        if ($checkSubscriber->isSubscribed()) {
            $status = 'true';
        }

        return $status;
    }

    /**
     * Get site type
     * @return string
     */
    public function getSiteType()
    {
        return $this->_storeManager->getWebsite()->getName();
    }

    /**
     * Get customer logged in status
     * @return string
     */
    public function getCustomerIsLoggedIn()
    {
        return $this->_customerSession->isLoggedIn();
    }

    /**
     * Get diagnosis configuration data
     * @return array
     */
    public function getDiagnosisData()
    {
        $data = [];
        $start_btn = $this->_scopeConfig->getValue(
            self::XML_PATH_START_DIAGNOSIS_BUTTON,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $next_btn = $this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSIS_NEXT_BUTTON_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $rst_btn = $this->_scopeConfig->getValue(
            self::XML_PATH_RESET_DIAGNOSIS_BUTTON,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $pre_btn = $this->_scopeConfig->getValue(
            self::XML_PATH_PREVIOUS_DIAGNOSIS_BUTTON,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $result_btn = $this->_scopeConfig->getValue(
            self::XML_PATH_RESULT_DIAGNOSIS_BUTTON,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $step_config = $this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSIS_STEP,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $brand_config = $this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSIS_BRAND_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        if (($start_btn && $next_btn && $rst_btn && $pre_btn && $result_btn && $step_config && $brand_config) != null) {
            $data['start_button_config'] = $start_btn;
            $data['next_button_config'] = $next_btn;
            $data['reset_btn_config'] = $rst_btn;
            $data['previous_btn_config'] = $pre_btn;
            $data['result_btn_config'] = $result_btn;
            $data['step_counter'] = $step_config;
            $data['brand_config'] = $brand_config;
        }

        $diagnosis_attribute_json = json_decode($this->_scopeConfig->getValue(
            self::XML_DIAGNOSIS_ATTRIBUTE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $diagnosis_step_option_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSIS_STEP_OPTIONS_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);
        
        if (!empty($diagnosis_attribute_json)) {
             $index = 0;
            foreach ($diagnosis_attribute_json as $key => $value) {
                $data['steps_data'][$value['gtm_step']] = $value;
                $data['steps_data'][$index] = $value;
                $dataArray = [];
                if (!empty($diagnosis_step_option_json)) {
                    foreach ($diagnosis_step_option_json as $option_key => $option_value) {
                        if ($option_value['step_name'] == $value['gtm_step']) {
                            $dataArray = $this->getArray($option_value, $dataArray);
                            $data['steps_data'][$index]['options'] =  $dataArray;
                        }
                    }
                }
                $index++;
            }
        }

        return $data;
    }

    /**
     * Get diagnosis configuration data
     * @return array
     */
    public function getArray($option_value, $dataArray)
    {
        $valueArray = [];
        foreach (explode('|', $option_value['step_option_value']) as $step_option_value) {
            $valueArray[] = $step_option_value;
        }
        $dataArray[] = [
            'id' => $option_value['step_option_class_id'],
            'array_text' => $option_value['step_array_text'],
            'value_array' => $valueArray
            ];
        return $dataArray;
    }
    
    /**
     * Get diagnosis brand
     * @return string
     */
    public function getDiagnosisBrand()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSIS_BRAND_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get cms page url
     * @return string
     */
    public function getCmsPageUri()
    {
        return $this->_cmsPage->getIdentifier();
    }

    /**
     * Get current category product
     * @return array
     */
    public function getCurrentCategoryProducts()
    {
        $data =[];
        if ($category = $this->getCurrentCategory()) {
            $data['category_name'] = $category->getName();
            $productCollection = $category->getProductCollection()->addAttributeToSelect('*');
            foreach ($productCollection as $product) {
                $data[$product->getEntityId()] = $product->getName();
            }
        }
        
        return $data;
    }

    /**
     * Get Rating Mapping Configuration
     * @return array
     */
    public function getRatingMappingConfiguration()
    {
        $data = [];

        $rating_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_RATING_MAPPING,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_RATING_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        if ($ecommerce_value != null) {
            $data['ecommerce'] = $ecommerce_value;
        }
        if (!empty($rating_json)) {
            foreach ($rating_json as $key => $value) {
                $this->_ratingClassesAndIds[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = $value['gtm_element_event_category'];
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_event_action'];
            }
        }

        return $data;
    }

    /**
     * Get Ratings Classes and Ids
     * @return array
     */
    public function getRatingClassesAndIds()
    {
        return $this->_ratingClassesAndIds;
    }

    /**
     * Get Back to Top Configuration
     * @return array
     */
    public function getBackToTopConfiguration()
    {
        $data = [];

        $eventLabel = $this->_scopeConfig->getValue(
            self::XML_PATH_BACK_TO_TOP_EVENTLABEL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $ecommerce_value = $this->_scopeConfig->getValue(
            self::XML_PATH_BACK_TO_TOP_ECOMMERCE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $element = $this->_scopeConfig->getValue(
            self::XML_PATH_BACK_TO_TOP_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        if ($eventLabel != null && $ecommerce_value != null) {
            $data['element'] = $element;
            $data['eventLabel'] = $eventLabel;
            $data['ecommerce'] = $ecommerce_value;
        }

        return $data;
    }

    /**
     * Get k profile email button selector
     * @return string
     */
    public function getKprofileSendEmailButtonSelector()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_KPROFILE_EMAIL_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Get Diagnostic Send By Email Mapping
     * @return array
     */
    public function getDiagnosticSendByEmailMapping()
    {
        $data = [];

        $diagnostic_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_EMAIL_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        if (!empty($diagnostic_json)) {
            foreach ($diagnostic_json as $key => $value) {
                $this->_diagnosticSendByEmailElements[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = $value['gtm_element_event_category'];
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_event_action'];
                $data[$value['gtm_element_name']]['eventLabel'] = $value['gtm_element_event_label'];
            }
        }

        return $data;
    }

    /**
     * Get Diagnostic Send By Email Submit
     * @return array
     */
    public function getDiagnosticSendByEmailElements()
    {
        return $this->_diagnosticSendByEmailElements;
    }

    /**
     * Get Diagnostic Send By Email Submit Event
     * @return array
     */
    public function getDiagnosticSendByEmailSubmitMapping()
    {
        $data = [];

        $diagnostic_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_EMAIL_SUBMIT_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        if (!empty($diagnostic_json)) {
            foreach ($diagnostic_json as $key => $value) {
                $this->_diagnosticSendByEmailSubmitElements[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = $value['gtm_element_event_category'];
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_event_action'];
                $data[$value['gtm_element_name']]['eventLabel'] = $value['gtm_element_event_label'];
            }
        }

        return $data;
    }

    /**
     * Get Diagnostic Send By Email Submit Event
     * @return array
     */
    public function getDiagnosticSendByEmailSubmitElements()
    {
        return $this->_diagnosticSendByEmailSubmitElements;
    }

    /**
     * Get Diagnose Shop Now Cta
     * @return array
     */
    public function getDiagnoseShopNowCta()
    {
        $data = [];

        $diagnostic_json = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_SHOP_NOW_CTA,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        if (!empty($diagnostic_json)) {
            foreach ($diagnostic_json as $key => $value) {
                $this->_diagnoseShopNowCta[] = $value['gtm_element_name'];
                $data[$value['gtm_element_name']]['eventCategory'] = 'Ecommerce';
                $data[$value['gtm_element_name']]['eventAction'] = $value['gtm_element_store'];
                $data[$value['gtm_element_name']]['eventLabel'] = $value['gtm_element_product'];
            }
        }

        return $data;
    }

    /**
     * Get Diagnose Shop Now Cta Elements
     * @return array
     */
    public function getDiagnoseShopNowCtaElements()
    {
        return $this->_diagnoseShopNowCta;
    }

    /**
     * Get news letter config data
     * @return array
     */
    public function getDaignoseNewsletterData($store_id = 0)
    {
        $data = [];

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_NEWSLETTER_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['element'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_NEWSLETTER_ELEMENT,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_NEWSLETTER_EVENTACATEGORY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['eventCategory'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_NEWSLETTER_EVENTACATEGORY,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_NEWSLETTER_EVENTACTION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['eventAction'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_NEWSLETTER_EVENTACTION,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_NEWSLETTER_EVENTLABEL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['eventLabel'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_NEWSLETTER_EVENTLABEL,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        return $data;
    }
    
    /**
     * Get diagnosis cgu data
     * @return array
     */
    public function getDaignoseCguData($store_id = 0)
    {
        $data = [];

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_CGU_ELEMENT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['element'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_CGU_ELEMENT,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DDIAGNOSE_CGU_EVENTACATEGORY,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['eventCategory'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DDIAGNOSE_CGU_EVENTACATEGORY,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_CGU_EVENTACTION,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['eventAction'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_CGU_EVENTACTION,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        if ($this->_scopeConfig->getValue(
            self::XML_PATH_DIAGNOSE_CGU_EVENTLABEL,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ) != null) {
            $data['eventLabel'] = $this->_scopeConfig->getValue(
                self::XML_PATH_DIAGNOSE_CGU_EVENTLABEL,
                ScopeInterface::SCOPE_STORE,
                $this->getStoreId()
            );
        }

        return $data;
    }
}

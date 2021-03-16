<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For providing data for datalayer
 */
namespace Cnnb\GtmWeb\Block;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Cnnb\GtmWeb\Helper\Data as GtmHelper;
use Magento\Catalog\Model\ResourceModel\Category\CollectionFactory as CategoryCollection;
 
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
    protected $_dataLayerEventName = 'cnnb_datalayer';

    /**
     * @var array
     */
    protected $_additionalVariables = [];

    /**
     * Push elements last to the data layer
     * @var array
     */
    protected $_customVariables = [];

    /**
     * @var array
     */
    protected $_variables = [];

    /**
     * @var Session data
     */
    protected $_coreSession;

    /**
     * @var array
     */
    protected $_categoryCollection = [];

    /**
     * @var request
     */
    protected $_request;

    /**
     * @param Context $context
     * @param GtmHelper $gtmHelper
     * @param CategoryCollection $categoryCollection
     * @param array $data
     * @throws NoSuchEntityException
     */
    public function __construct(
        Context $context,
        GtmHelper $gtmHelper,
        CategoryCollection $categoryCollection,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        array $data = []
    ) {
        $this->_gtmHelper = $gtmHelper;
        $this->_categoryCollection = $categoryCollection;
        $this->_coreSession = $coreSession;
        parent::__construct($context, $data);
        $this->_init();
        $this->_request = $request;
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

        return $this;
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
     * @deprecated - use addCustomDataLayer and addCustomDataLayerByEvent
     * @param $name
     * @param null $value
     * @return $this
     */
    public function addAdditionalVariable($name, $value = null)
    {
        if (is_array($name)) {
            $this->_additionalVariables[] = $name;
        } else {
            $this->_additionalVariables[] = [$name => $value];
        }

        return $this;
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
     * @return string
     */
    public function getDataLayerName()
    {
        return $this->_gtmHelper->getDataLayerName();
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
     * Function for getting session data
     * @return array
     */
    public function getSavedSessionData()
    {
        $response = [];
        $cnnbCustId = $this->_coreSession->getCnnbCustomerId();
        $isRegister = $this->_coreSession->getIsRegister();
        $isLogin = $this->_coreSession->getIsLogin();
        $isLoginAfterRegiter = $this->_coreSession->getLoginAfterRegister();
        $registerConfigValue = $this->_gtmHelper->getRegisterValue();
        $loginConfigValue = $this->_gtmHelper->getLoginValue();
        $response['cnnb_cust_id'] = $cnnbCustId;
        $response['is_register'] = $isRegister;
        $response['is_login'] = $isLogin;
        $response['is_login_after_register'] = $isLoginAfterRegiter;
        $response['register_config_value'] = $registerConfigValue;
        $response['login_config_value'] = $loginConfigValue;
        return $response;
    }

    /**
     * Get Category Collection
     *
     * @return array
     */
    public function getCategoryCollectionData()
    {
        $categories = $this->_categoryCollection->create()->addAttributeToSelect('*');
        $menu = [];
        foreach ($categories as $category) {
            if ($category->getParentCategories()) {
                $child  = [];
                foreach ($category->getParentCategories() as $parent) {
                    if ($category->getId() != $parent->getId()) {
                        $child[] = $parent->getName();
                    }
                }
                if (!empty($child)) {
                    $menu[$category->getName()] = implode(' :: ', $child).' :: '.$category->getName();
                } else {
                    $menu[$category->getName()] = $category->getName();
                }
            }
        }

        return json_encode($menu);
    }

    /**
     * Get Current PageType
     * @return string
     */
    public function getPageType()
    {
        return $this->_request->getFullActionName();
    }

    /**
     * Get page load data
     * @return array
     */
    public function getPageLoadData()
    {
        $language = $this->_gtmHelper->getCurrentLocale();
        $country = $this->_gtmHelper->getCountryValue();
        $siteTypeLevel = $this->_gtmHelper->getSiteType();
        $pageCategory = $this->getPageType();
        $isLoggedIn = $this->_gtmHelper->getCustomerIsLoggedIn();
        $customerId = $this->_gtmHelper->getCustomerStatus();
        $brand = $this->_gtmHelper->getDiagnosisBrand();
        $cmsPageUri = $this->_gtmHelper->getCmsPageUri();
        if ($cmsPageUri == 'diagnose-your-hair') {
            $pageCategory = 'diagnositic';
        }
        $subscriptionStatus = 'false';
        if ($isLoggedIn == 1) {
            $subscriptionStatus = $this->_gtmHelper->checkSubscription($customerId);
        }
        $dataArray = [
            'brand'=> $brand,
            'language' => $language,
            'country'=> $country,
            'siteTypeLevel' => $siteTypeLevel,
            'pageCategory' => $pageCategory,
            'newsletterSubscription' => $subscriptionStatus
        ];
        
        return $dataArray;
    }
}

<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\Vendorcommission\Block\Adminhtml\Customer\Edit;

use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Directory\Model\ResourceModel\Country\Collection as CountryModel;

/**
 * Customer account form block.
 */
class CommissionTab extends \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\CommissionTab
{

    /**
     * @var \Magento\Store\Model\System\Store
     */
    protected $_systemStore;

    protected $_dob = null;
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry;
    /**
     * @var CountryModel
     */
    protected $_country;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Framework\Registry               $registry
     * @param \Magento\Framework\Data\FormFactory       $formFactory
     * @param \Magento\Store\Model\System\Store         $systemStore
     * @param CountryModel                              $country
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        CountryModel $country,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->customerEdit = $customerEdit;
        $this->_country = $country;
        parent::__construct($context, $registry, $formFactory, $systemStore, $country, $customerEdit, $data);
    }

    public function getObjectManager(){
        return \Magento\Framework\App\ObjectManager::getInstance();
    }
    
    /**
     * @return string|null
     */
    public function getCustomerId()
    {
        return $this->_coreRegistry->registry(
            RegistryConstants::CURRENT_CUSTOMER_ID
        );
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return __('Commission');
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return __('Commission');
    }

    /**
     * @return bool
     */
    protected function getSellerStatus()
    {
        /*$coll = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getMarketplaceUserCollection();*/
        
        $coll = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getMarketplaceUserCollection();
        $isSeller = false;
        foreach ($coll as $row) {
            $isSeller = $row->getIsSeller();
        }
        if ($this->getCustomerId() && $isSeller) {
            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function canShowTab()
    {
        return $this->getSellerStatus();
    }

    /**
     * @return bool
     */
    public function isHidden()
    {
        return $this->getSellerStatus();
    }

    /**
     * Tab class getter.
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content.
     *
     * @return string
     */
    public function getTabUrl()
    {
        return '';
    }

    /**
     * Tab should be loaded trough Ajax call.
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    public function getFieldValue($commissionRuleArray, $attrValue, $value)
    {
        $commission = [];
        $fieldValue = null;        
        foreach ($commissionRuleArray as $key => $data) {
            if ($key == $attrValue) {

                foreach ($data as $newkey => $newvalue) {
                    if (trim($newkey) == trim($value)) {
                        $fieldValue = $newvalue;
                    }
                }
            }
        }
        return $fieldValue;
    }

    public function initForm()
    {
        $data = 0;
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityAttributeCollection = $newObjectManager->get('Mangoit\Vendorcommission\Helper\Data');
        $myData = $entityAttributeCollection->getCustomAttributeOption();
        $attrArray = array();
        foreach ($myData as $label => $value) {
            $attrArray[$value['value']] = $value['label'];
        }
        if (!$this->canShowTab()) {
            return $this;
        }
        /**@var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('marketplace_');
        $customerId = $this->_coreRegistry->registry(
            RegistryConstants::CURRENT_CUSTOMER_ID
        );
        $storeid = $this->_storeManager->getStore()->getId();

        $fieldset = $form->addFieldset(
            'base_fieldset',
            ['legend' => __('Commission Details')]
        );

        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSalesPartnerCollection();
        if (count($collection)) {
            foreach ($collection as $value) {
                $rowcom = $value->getCommissionRate();
            }
        } else {
            $rowcom = $this->getObjectManager()->create(
                'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
            )->getConfigCommissionRate();
        }
        
        $saleCollection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSalesPartnerCollection();

        foreach ($saleCollection as $value) {            
           $data = $value->getCommissionRule();
        }

        $newStoreManager =  $this->getObjectManager()->create('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $newStoreManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->getObjectManager()->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();
        $commission = [];
        $commissionRuleArray;

        if(empty($data)){
            $fieldZero = 0;
        } else {
            $commissionRuleArray = unserialize($data);
        }

        $attrArray = $this->getAttributeData();
        $rangeArray = $this->getCommissionValuesFromStore();
        $collectionData = $this->getCommissionValues();
        $sellerId = $this->getCustomerId();
        $range =  explode(',', $rangeArray);
        $counter = 1;

        foreach ($range as $key => $value) {
            foreach ($attrArray as $key => $attrValue) {
                     if (empty($data)) {
                        $commissionValue = null;
                     } else {
                    $commissionValue = $this->getFieldValue($commissionRuleArray, $attrValue, $value);
                        
                     }
                $fieldset->addField(
                    $attrValue.'-range-'.$counter,
                    'text',
                    [
                        'name' => $attrValue.'+'.trim($value),
                        'data-form-part' => $this->getData('target_form'),
                        'label' => __($attrValue." (".$currencySymbol." ".trim($value).")"),
                        'title' => __($attrValue."(".$value.")"),
                        'class' => 'validate-number',
                        'value' => isset($commissionValue) ? $commissionValue : '',
                    ]
                );
                
            $counter++;
            }

           
        }
         
        $fieldset->addField(
            'Add More',
            'button',
            [
                'value' => __('button value'),
                'name' => 'addMore',
                'class' => 'addMore',
            ]
        );


        // $this->setForm($form);

        return $this;
    }

    /**
     * @return string
     */
    protected function _toHtml()
    {
        if ($this->canShowTab()) {
            $this->initForm();

            return parent::_toHtml();
        } else {
            return '';
        }
    }

    public function getStrorConfigData()
    {
        $blockObject = $this->getObjectManager()->create('Mangoit\Vendorcommission\Block\Adminhtml\Edit\Tab\Commissionrule');
        return $blockObject;
    }

        public function getAttributeData()
    {
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityAttributeCollection = $newObjectManager->get('Mangoit\Vendorcommission\Helper\Data');
        $myData = $entityAttributeCollection->getCustomAttributeOption();
        $attrArray = array();
        foreach ($myData as $label => $value) {
            $attrArray[$value['value']] = $value['label'];
        }
        return $attrArray;
    }

    public function getCommissionValuesFromStore(){
        $storeValue = $this->getObjectManager()->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $rangeArray = $storeValue->getValue('marketplace/commission_setting/ranges');
        return trim($rangeArray);
    }

    public function getCommissionValues()
    {
        $commissionData = $this->getObjectManager()->get('Mangoit\Vendorcommission\Model\Turnover');
        $commissionCollection = $commissionData->getCollection();
        return $commissionCollection;
    }
}

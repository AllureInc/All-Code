<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Software Private Limited 
 */

namespace Mangoit\Marketplace\Block\Adminhtml\Customer\Edit;


use Magento\Customer\Controller\RegistryConstants;
use Magento\Ui\Component\Layout\Tabs\TabInterface;
use Magento\Backend\Block\Widget\Form;
use Magento\Backend\Block\Widget\Form\Generic;
use Magento\Customer\Model\CustomerFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\SellerFactory;

/**
 * Customer Seller form block.
 */
class Tabs extends \Webkul\Marketplace\Block\Adminhtml\Customer\Edit\Tabs
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
     * @var Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_country;

    /**
     * @var \Magento\Cms\Model\Wysiwyg\Config
     */
    protected $_wysiwygConfig;

    /**
     * @var CustomerFactory
     */
    protected $customerModel;

    /**
     * @var \Magento\Store\Ui\Component\Listing\Column\Store\Options
     */
    protected $options;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var SellerFactory
     */
    protected $sellerModel;


    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry             $registry
     * @param \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit
     * @param CustomerFactory                         $customerModel
     * @param \Magento\Store\Ui\Component\Listing\Column\Store\Options $options
     * @param MpHelper                                $mpHelper
     * @param SellerFactory                           $sellerModel
     * @param array                                   $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Magento\Directory\Model\ResourceModel\Country\Collection $country,
        \Magento\Cms\Model\Wysiwyg\Config $wysiwygConfig,
        \Webkul\Marketplace\Block\Adminhtml\Customer\Edit $customerEdit,
        CustomerFactory $customerModel,
        \Magento\Store\Ui\Component\Listing\Column\Store\Options $options,
        MpHelper $mpHelper,
        SellerFactory $sellerModel,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_systemStore = $systemStore;
        $this->_country = $country;
        $this->_wysiwygConfig = $wysiwygConfig;
        $this->customerEdit = $customerEdit;
        $this->customerModel = $customerModel;
        $this->options = $options;
        $this->mpHelper = $mpHelper;
        $this->sellerModel = $sellerModel;
        $this->_formFactory = $formFactory;
        parent::__construct($context, $registry, $formFactory,  $systemStore, $country, $wysiwygConfig, $customerEdit, $customerModel, $options, $mpHelper, $sellerModel, $data);
    }

    public function getObjectManager(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }

    public function initForm()
    {
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
            ['legend' => __('Seller Profile Information')]
        );

        $customer = $this->getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        )->load($customerId);
        // echo "<pre>"; print_r($customer->getData());echo "</pre>";
        //  die('died');
        $partner = $this->getObjectManager()->create(
            'Webkul\Marketplace\Block\Adminhtml\Customer\Edit'
        )->getSellerInfoCollection();

        $sellerData = $this->getObjectManager()->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('store_id', 0);
        $sellerFirstItem = $sellerData->getFirstItem();
        $sellerUrl = $sellerFirstItem->getShopUrl();

        $sellerAccDeactivate = $sellerFirstItem->getAccountDeactivate();
        $twAactive = '';
        $fbAactive = '';
        $gplusActive = '';
        $instagramActive = '';
        $youtubeActive = '';
        $vimeoActive = '';
        $pinterestActive = '';
        $moleskineActive = '';

        if (isset($partner['tw_active'])) {
            if ($partner['tw_active'] == 1) {
                $twAactive = "value='1' checked='checked'";
            }
        }
        if ($partner['fb_active'] == 1) {
            $fbAactive = "value='1' checked='checked'";
        }
        if ($partner['gplus_active'] == 1) {
            $gplusActive = "value='1' checked='checked'";
        }
        if ($partner['instagram_active'] == 1) {
            $instagramActive = "value='1' checked='checked'";
        }
        if ($partner['youtube_active'] == 1) {
            $youtubeActive = "value='1' checked='checked'";
        }
        if ($partner['vimeo_active'] == 1) {
            $vimeoActive = "value='1' checked='checked'";
        }
        if ($partner['pinterest_active'] == 1) {
            $pinterestActive = "value='1' checked='checked'";
        }
        if ($partner['moleskine_active'] == 1) {
            $moleskineActive = "value='1' checked='checked'";
        }
        $allStoreViews = $this->getObjectManager()->create(
            'Magento\Store\Ui\Component\Listing\Column\Store\Options'
        )->toOptionArray();
        $len = count($allStoreViews);
        $allStoreViews[$len]['label'] = __('Admin Store');
        $allStoreViews[$len]['value'][0]['label'] = __('Admin Store View');
        $allStoreViews[$len]['value'][0]['value'] = 0;
        $allStores = $this->getObjectManager()->create(
            'Webkul\Marketplace\Helper\Data'
        )->getAllStores();
        $currentUrl = $this->getCurrentUrl();
        $currentUrlArr = explode("store", $currentUrl);
        $currentUrlBase = $currentUrlArr[0];
        $storeUrl = $currentUrlBase."store/0";
        $data = '<input type="hidden" id="wk_mp_store0" value="'.$storeUrl.'">';
        foreach ($allStores as $store) {
            $storeUrl = $currentUrlBase."store/".$store->getId()."/";
            $data = $data.'<input type="hidden" id="wk_mp_store'.$store->getId().'" value="'.$storeUrl.'">';
        }
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $fieldset->addField(
            'account_deactivate',
            'select',
            [
                'name' => 'account_deactivate',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Account Deactivate'),
                'title' => __('Account Deactivate'),
                'note'      => __('Global store'),
                'values' => array(
                    '0' => 'No',
                    '1' => 'Yes',
                ),
                'value' => $sellerAccDeactivate,
            ]
        );
        $fieldset->addField(
            'trustworthy',
            'select',
            [
                'name' => 'trustworthy',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Trustworthy?'),
                'title' => __('Trustworthy?'),
                'values' => array(
                    '0' => 'No',
                    '1' => 'Yes',
                ),
                'value' => $partner['trustworthy'],
            ]
        );


        $fieldset->addField(
            'store_id',
            'select',
            [
                'name' => 'store_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Select Store'),
                'title' => __('Select Store'),
                'values' => $allStoreViews,
                'value' => $storeId,
                'after_element_html' => $data.'<script>
                require([
                    "jquery"
                ], function($){
                    $("#marketplace_store_id").on("change", function() {
                        var storeId = $(this).val();
                        window.location.href = $("#wk_mp_store"+storeId).val();
                    });
                });
                </script>'
            ]
        );

        $fieldset->addField(
            'is_profile_approved',
            'select',
            [
                'name' => 'is_profile_approved',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Is profile approved?'),
                'title' => __('Is profile approved?'),
                'values' => array(
                    '0' => 'Disapproved',
                    '1' => 'Approved',
                ),
                'value' => $partner['is_profile_approved'],
            ]
        );

        $fieldset->addField(
            'generate_invoice',
            'select',
            [
                'name' => 'generate_invoice',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Generate Invoice'),
                'title' => __('Generate Invoice'),
                'values' => array(
                    'weekly' => 'Weekly',
                    'monthly' => 'Monthly',
                ),
                'value' => $partner['generate_invoice'],
            ]
        );

        $fieldset->addField(
            'twitter_id',
            'text',
            [
                'name' => 'twitter_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Twitter ID'),
                'title' => __('Twitter ID'),
                'value' => $partner['twitter_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="tw_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Twitter Icon in Profile Page').'" 
                    '.$twAactive.'
                >',
            ]
        );
        $fieldset->addField(
            'facebook_id',
            'text',
            [
                'name' => 'facebook_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Facebook ID'),
                'title' => __('Facebook ID'),
                'value' => $partner['facebook_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="fb_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Facebook Icon in Profile Page').'" 
                    '.$fbAactive.'
                >',
            ]
        );
        $fieldset->addField(
            'instagram_id',
            'text',
            [
                'name' => 'instagram_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Instagram ID'),
                'title' => __('Instagram ID'),
                'value' => $partner['instagram_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="instagram_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Instagram Icon in Profile Page').'" 
                    '.$instagramActive.'
                >',
            ]
        );
        $fieldset->addField(
            'gplus_id',
            'text',
            [
                'name' => 'gplus_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Google+ ID'),
                'title' => __('Google+ ID'),
                'value' => $partner['gplus_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="gplus_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Google+ Icon in Profile Page').'" 
                    '.$gplusActive.'
                >',
            ]
        );
        $fieldset->addField(
            'youtube_id',
            'text',
            [
                'name' => 'youtube_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Youtube ID'),
                'title' => __('Youtube ID'),
                'value' => $partner['youtube_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="youtube_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Youtube Icon in Profile Page').'" 
                    '.$youtubeActive.'
                >',
            ]
        );
        $fieldset->addField(
            'vimeo_id',
            'text',
            [
                'name' => 'vimeo_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Vimeo ID'),
                'title' => __('Vimeo ID'),
                'value' => $partner['vimeo_id'],
                'after_element_html' => '<input 
                type="checkbox" 
                name="vimeo_active" 
                data-form-part="customer_form" 
                onchange="this.value = this.checked ? 1 : 0;" 
                title="'.__('Allow to Display Vimeo Icon in Profile Page').'" 
                '.$vimeoActive.'
            >',
            ]
        );
        $fieldset->addField(
            'pinterest_id',
            'text',
            [
                'name' => 'pinterest_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Pinterest ID'),
                'title' => __('Pinterest ID'),
                'value' => $partner['pinterest_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="pinterest_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Pinterest Icon in Profile Page').'" 
                    '.$pinterestActive.'
                >',
            ]
        );
        $fieldset->addField(
            'moleskine_id',
            'text',
            [
                'name' => 'moleskine_id',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Moleskine ID'),
                'title' => __('Moleskine ID'),
                'value' => $partner['moleskine_id'],
                'after_element_html' => '<input 
                    type="checkbox" 
                    name="moleskine_active" 
                    data-form-part="customer_form" 
                    onchange="this.value = this.checked ? 1 : 0;" 
                    title="'.__('Allow to Display Moleskine Icon in Profile Page').'" 
                    '.$moleskineActive.'
                >',
            ]
        );
        $fieldset->addField(
            'contact_number',
            'text',
            [
                'name' => 'contact_number',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Contact Number'),
                'title' => __('Contact Number'),
                'value' => $partner['contact_number'],
            ]
        );
        $fieldset->addField(
            'taxvat',
            'text',
            [
                'name' => 'taxvat',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Tax/VAT Number'),
                'title' => __('Tax/VAT Number'),
                'value' => $customer->getTaxvat(),
            ]
        );
        $fieldset->addField(
            'shop_title',
            'text',
            [
                'name' => 'shop_title',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Shop Title'),
                'title' => __('Shop Title'),
                'value' => $partner['shop_title'],
            ]
        );
        $fieldset->addField(
            'company_locality',
            'text',
            [
                'name' => 'company_locality',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Locality'),
                'title' => __('Company Locality'),
                'value' => $partner['company_locality'],
            ]
        );
        $fieldset->addField(
            'website_url',
            'text',
            [
                'name' => 'website_url',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company URL'),
                'title' => __('Company URL'),
                'value' => $partner['website_url'],
            ]
        );
        $fieldset->addField(
            'country_pic',
            'select',
            [
                'name' => 'country_pic',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Select Country'),
                'title' => __('Select Country'),
                'values' => $this->_country->loadByStore()->toOptionArray(),
                'value' => $partner['country_pic'],
            ]
        );
        $fieldset->addField(
            'company_description',
            'textarea',
            [
                'name' => 'company_description',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Description'),
                'title' => __('Company Description'),
                'value' => $partner['company_description'],
            ]
        );
        $fieldset->addField(
            'return_policy',
            'textarea',
            [
                'name' => 'return_policy',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Return Policy'),
                'title' => __('Return Policy'),
                'value' => $partner['return_policy'],
            ]
        );
        $fieldset->addField(
            'shipping_policy',
            'textarea',
            [
                'name' => 'shipping_policy',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Shipping Policy'),
                'title' => __('Shipping Policy'),
                'value' => $partner['shipping_policy'],
                'after_element_html' => "<script>
                require([
                    'jquery',
                    'mage/adminhtml/wysiwyg/tiny_mce/setup'
                ], function(jQuery){
                
                    var config = '".$this->getWysiwygConfig()."',
                        editor;
                
                    jQuery.extend(config, {
                        settings: {
                            theme_advanced_buttons1 : 'bold,italic,|,justifyleft,justifycenter,justifyright,|,' +
                                'fontselect,fontsizeselect,|,forecolor,backcolor,|,link,unlink,image,|,bullist,numlist,|,code',
                            theme_advanced_buttons2: null,
                            theme_advanced_buttons3: null,
                            theme_advanced_buttons4: null,
                            theme_advanced_statusbar_location: null
                        },
                        files_browser_window_url: false
                    });
                
                    editor = new tinyMceWysiwygSetup(
                        'marketplace_company_description',
                        config
                    );
                
                    editor.turnOn();
                
                    jQuery('#marketplace_company_description')
                    .addClass('wysiwyg-editor')
                    .data(
                        'wysiwygEditor',
                        editor
                    );
                    editor = new tinyMceWysiwygSetup(
                        'marketplace_return_policy',
                        config
                    );
                    editor.turnOn();
                    jQuery('#marketplace_return_policy')
                    .addClass('wysiwyg-editor')
                    .data(
                        'wysiwygEditor',
                        editor
                    );
                
                    editor = new tinyMceWysiwygSetup(
                        'marketplace_shipping_policy',
                        config
                    );
                    editor.turnOn();
                    jQuery('#marketplace_shipping_policy')
                    .addClass('wysiwyg-editor')
                    .data(
                        'wysiwygEditor',
                        editor
                    );
                });
                </script>"
            ]
        );
        $fieldset->addField(
            'meta_keyword',
            'textarea',
            [
                'name' => 'meta_keyword',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Meta Keywords'),
                'title' => __('Meta Keywords'),
                'value' => $partner['meta_keyword'],
            ]
        );
        $fieldset->addField(
            'meta_description',
            'textarea',
            [
                'name' => 'meta_description',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Meta Description'),
                'title' => __('Meta Description'),
                'value' => $partner['meta_description'],
            ]
        );
        $fieldset->addField(
            'banner_pic',
            'file',
            [
                'name' => 'banner_pic',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Banner'),
                'title' => __('Company Banner'),
                'value' => $partner['banner_pic'],
                'after_element_html' => '<label style="width:100%;">
                    Allowed File Type : [jpg, jpeg, gif, png]
                </label>
                <img style="margin:5px 0;width:700px;" 
                src="'.$this->getBaseUrl().'pub/media/avatar/'.$partner['banner_pic'].'"
                />',
            ]
        );
        $fieldset->addField(
            'logo_pic',
            'file',
            [
                'name' => 'logo_pic',
                'data-form-part' => $this->getData('target_form'),
                'label' => __('Company Logo'),
                'title' => __('Company Logo'),
                'value' => $partner['logo_pic'],
                'after_element_html' => '<label style="width:100%;">
                    Allowed File Type : [jpg, jpeg, gif, png]
                </label>
                <img style="margin:5px 0;width:250px;" 
                src="'.$this->getBaseUrl().'pub/media/avatar/'.$partner['logo_pic'].'"
                />',
            ]
        );
        $url = $this->getBaseUrl().'marketplace/seller/profile/shop/'.$sellerUrl.'/adminlogin/true';
        $fieldset->addField('view_profile', 'button', array(
            'label' => '',
            'value' => 'Preview Profile',
            'name'  => 'view_profile',
            'class' => 'form-button',
            'onclick' => "window.open('".$url."')",
            'style' => 'background-color: #eb5202;
                    border-color: #eb5202;
                    color: #fff;
                    text-shadow: 1px 1px 0 rgba(0,0,0,0.25);
                    padding: 8px 12px;
                    box-sizing: border-box;
                    font-size: 20px;
                    border: none;
                }',
        ));
        
        $form->setUseContainer(true);

        $this->setForm($form);

        return parent::_prepareForm();
    }
}

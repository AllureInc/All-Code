<?php 
namespace Mangoit\MisTooltip\Block\Adminhtml\Tool;
use Magento\Framework\App\Filesystem\DirectoryList;
/**
* 
*/
class Index extends \Magento\Backend\Block\Template
{
    protected $_objectManagaer;
    protected $_storeRepository;
    protected $_session;
    protected $_storeManager;
    protected $misHelper;

    public function __construct(
        \Magento\Store\Model\StoreRepository $storeRepository,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Session $session,
        \Mangoit\Marketplace\Helper\Data $misHelper,
        \Magento\Backend\Block\Template\Context $context
        )
    {
        $this->_session = $session;
        $this->_storeRepository = $storeRepository;
        $this->_objectManagaer = $objectmanager;
        $this->_storeManager = $storeManager;
        $this->misHelper = $misHelper;
        parent::__construct($context);
    }

    public function getSections()
    {
        return array(
            ''=> 'select',
            'my_account'=> 'My Account',
            'edit_profile'=> 'Edit Profile',
            'import_shop_config'=> 'Import Shop Configuration',
            'vacation_setting'=> 'Vacation Setting',
            'account_deactivation'=> 'Account Deactivation',
            'new_product'=> 'Add/Edit Product',
            'vendor_attributes'=> 'Create Attribute',
            'admin_order_view'=> 'Order View (Admin)',
            'product_faq'=> 'Product FAQ',
            'mygermany_price_calculation'=> 'myGermany Price Calculation',
            'vendor_payment'=> 'Vendor Payment',
            'rakuten'=> 'Rakuten',
            'amazon'=> 'Amazon',
            'vendor_invoice'=> 'Vendor Invoice',
            'dhl_label'=> 'DHL'
            );
    }

    public function getSectionOptions($sectionId)
    {
        switch ($sectionId) {
            case 'my_account':
                return array('account_information' => 'Account Information',
                    'marketplace_orders_status' => 'Marketplace Orders & Status Block');
                break;

            case 'admin_order_view':
                return array(
                        'order_statuses' => 'Order Statuses'
                    );
                break;

            case 'edit_profile':
                return array(
                    'store' => 'Store',
                    'meta_keywords' => 'Meta Keywords',
                    'meta_description' => 'Meta Description',
                    'shop_layout' => 'Shop Layout',
                    'internal_note_profile' => 'Internal Note For myGermany',
                    'company_banner'=> 'Company Banner',
                    'return_policy'=> 'Return Policy',
                    'shipping_policy'=> 'Shipping Policy',
                    'theme_background'=> 'Theme Background',
                    'generate_invoice'=> 'Generate Invoice'
                    );
                break;

            case 'import_shop_config':
                return array(
                    'import_shop_config_msg' => 'Import Shop Configuration Block'
                    );
                # code...
                break; 

            case 'vacation_setting':
                return array(
                    'vacation_setting_block' => 'Vacation Setting Block',
                    'vacation_status_text' => 'Vacation Status',
                    'vacation_message' => 'Vacation Message',
                    'vacation_date_from' => 'Vacation Date From',
                    'vacation_date_to' => 'Vacation Date To'
                    );
                break;

            case 'account_deactivation':
                return array(
                    'account_deactivation_text' => 'Account Deactivation'
                    );
                break;

            case 'product_faq':
                return array(
                    'product_faq_block' => 'Product FAQ Block'
                    );
                break;

            case 'new_product':

                $returnArr = array(
                        'product_type' => 'Product Type (Configurable/Simple)',
                        'internal_note' => 'Internal Note',
                        'visibility' => 'Visibility',
                        'fsk' => 'FSK Compulsory',
                        'product_cat_type'=> 'Select Product Type (Electronic/Non-Electronic)',                    
                        'product_meta_title' => 'Meta Title',
                        'product_meta_keywords' => 'Meta Keywords',
                        'product_meta_description' => 'Meta Description',
                        'related_product'=> 'Related Product',
                        'net_price'=> 'Net Price',
                        'special_price'=> 'Special Price',
                        'shipping_price_to_mygermany'=> 'Shipping Price to myGermany'
                    );
                $sensitiveAttrs = $this->misHelper->getSensitiveAttributes();

                foreach ($sensitiveAttrs as $value) {
                    $returnArr[$value->getAttributeCode()] = $value->getDefaultFrontendLabel();
                }
                return $returnArr;
                break;

            case 'mygermany_price_calculation':
                return array(
                    'shipping_charges' => 'Shipping Charges',
                    'mygermany_commission' => 'myGermany Commission',
                    'mygermany_commission_logged_in' => 'myGermany Commission for Logged In',
                    'payment_fees'=> 'Payment Fees',                    
                    'exchange_fees' => 'Exchange Fees',
                    'total_fees_from_mygermany' => 'Total Fees From myGermany',
                    'your_earnings' => 'Your Earnings'
                    );
                break;

            case 'vendor_attributes':
                return array(
                    'create_attribute_msg' => 'Create Attribute Block',
                    'attribute_code' => 'Attribute Code',
                    'attribute_label'=> 'Attribute Label',                    
                    'attribute_input_type' => 'Attribute Input Type',
                    'generic_attribute_tip' => 'Generic Attribute Tooltip',
                    'config_attribute_tip' => 'Configurable Attribute Tooltip'
                    );
                break;

            case 'vendor_payment':
                return array(
                    'payment_methods_block' => 'Payment Method Block',
                    'commission_structure_block' => 'Commission Structure Block'
                    );
                break;

            case 'amazon':
                return array(
                    'amazon_manage_account' => 'Amazon Manage Account Block',
                    'amazon_sync_products' => 'Amazon Sync Products Block'
                    );
                break;

            case 'rakuten':
                return array(
                    'rakuten_manage_account' => 'Rakuten Manage Account Block',
                    'rakuten_sync_products' => 'Rakuten Sync Products Block'
                    );
                break;

            case 'dhl_label':
                return array(
                    'dhl_label_msg' => 'DHL Label Message'
                    );
                break;

            case 'vendor_invoice':
                return array(
                    'vendor_invoice_text_to_btn' => 'Text next to the Generate Invoice button',
                    'vendor_invoice_topic_for_tbl' => 'Topic For Table'
                    );
                break;
            
            default:
                return array(''=> 'select');
                break;
        }
    }

    public function getStoreList()
    {
        $stores = $this->_storeRepository->getList();
        $storeList[''] = 'select';
        // $storeList[0] = 'All Store';
        foreach ($stores as $store) {
            if ($store["store_id"] != 0) {
                $storeId = $store["store_id"];
                $storeName = $store["name"]; 
                $storeList[$storeId] = $storeName;
            }
        }
        return $storeList;
    }

}


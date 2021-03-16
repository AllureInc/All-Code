<?php 
/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Academic Free License (AFL 3.0)
  * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  *
  * @category    Ced
  * @package     Ced_Twiliosmsnotification
  * @author      CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */

namespace Ced\Twiliosmsnotification\Helper;

require_once  BP.'/lib/Twilio.php';

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const CONFIG_PATH = 'twilio_notification/';

    protected $_objectManager;
    protected $_storeManager;
    protected $_scopeConfigManager;
    protected $_configValueManager;
    protected $_serialize;

    protected $_storeId = 0;

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $serialize,
        \Magento\Framework\ObjectManagerInterface $objectManager
        )
    {
        $this->_objectManager = $objectManager;
        parent::__construct($context);
        $this->_storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $this->_scopeConfigManager = $this->_objectManager->get('Magento\Framework\App\Config\ScopeConfigInterface');
        $this->_configValueManager = $this->_objectManager->get('Magento\Framework\App\Config\ValueInterface');
        $this->_serialize = $serialize;
    }
    
    /**
     * Set a specified store ID value
     *
     * @param int $store
     * @return $this
     */
    public function setStoreId($store){
        $this->_storeId = $store;
        return $this;
    }
    
    /**
     * Get current store
     * @return Mage_Core_Model_Store
     */
    public function getStore() {
       if ($this->_storeId) $storeId = (int)$this->_storeId;
       else $storeId =  isset($_REQUEST['store'])?(int) $_REQUEST['store']:null;
        return $this->_storeManager->getStore($storeId);
    }

    public function isExtensionEnabled()
    {
        return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'enter/enable',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }
    
    public function getAccountSid()
    {
        return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'enter/account_sid',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }
    
    public function getAuthToken()
    {
        return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'enter/auth_token',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    public function getSender()
    {
        return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'enter/phone_number',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }

    public function isSectionEnabled($path){
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.$path,\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return false;
    }

    public function getTelephoneFromOrder(\Magento\Sales\Model\Order $order)
    {
        $billingAddress = $order->getBillingAddress();
        $number = $billingAddress->getTelephone();

        $country_id = $billingAddress->getCountryId();
        $available_country_codes = $this->getCountryCodes();
        if(!empty($available_country_codes) && (isset($available_country_codes[$country_id]))) {
            $number = $available_country_codes[$country_id].$number;
        }
        else
            $number="+1".$number;
        return $number;
    }

    public function getAdminTelephone()
    {
        return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'orders/receiver',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
    }
    
    public function isOrderStatusNotificationEnabled()
    {
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'order_status/enabled',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return false;
    }
    
    public function getAdminOrderStatusTelephone()
    {
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'order_status/receiver',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return false;
    }
    
    public function getAdminCustomerRegisterationTelephone() 
    {
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'customer_registration/receiver',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return false;
    }
    
    public function getAdminVendorRegisterationTelephone() 
    {
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_registration/receiver',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return false;
    }
    
    public function getMessage(\Magento\Sales\Model\Order $order)
    {
        $billingAddress = $order->getBillingAddress();
        $codes = array('{{firstname}}','{{middlename}}','{{lastname}}','{{fax}}','{{postal}}','{{city}}','{{email}}','{{order_id}}','{{name}}');
        $accurate = array($billingAddress->getFirstname(),
                $billingAddress->getMiddlename(),
                $billingAddress->getLastname(),
                $billingAddress->getFax(),
                $billingAddress->getPostcode(),
                $billingAddress->getCity(),
                $billingAddress->getEmail(),
                $order->getIncrementId(),
                $billingAddress->getFirstname().' '.$billingAddress->getLastname()
        );
    
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'orders/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function newVendorOrderMsg($product, $vendor, $orderId)
    {
        $codes = array('{{name}}','{{email}}', '{{productname}}','{{sku}}','{{order_id}}');
        $accurate = array($vendor->getName(),
                $vendor->getEmail(),
                $product->getName(),
                $product->getSku(),
                $orderId
        );
    
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_order/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function getOrderStatusChangeMsg(\Magento\Sales\Model\Order $order)
    {
        $status = $this->getStatusName($order->getState());
        $billingAddress = $order->getBillingAddress();
        $codes = array('{{firstname}}','{{middlename}}','{{lastname}}','{{fax}}','{{postal}}','{{city}}','{{email}}','{{order_id}}','{{status}}','{{name}}');
        $accurate = array($billingAddress->getFirstname(),
                $billingAddress->getMiddlename(),
                $billingAddress->getLastname(),
                $billingAddress->getFax(),
                $billingAddress->getPostcode(),
                $billingAddress->getCity(),
                $billingAddress->getEmail(),
                $order->getIncrementId(),
                $status,
                $billingAddress->getFirstname().' '.$billingAddress->getLastname()
        );
    
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'order_status/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function getStatusName($stateCode)
    {
        $statuses = $statuses = $this->_objectManager->get('\Magento\Sales\Model\ResourceModel\Order\Status\Collection')
        ->addStateFilter($stateCode)
        ->toOptionHash();
        if(is_array($statuses))  
            return $statuses[$stateCode];
        return false;
    }
    
    public function getCustomerRegistrationMsg($customer, $pass)
    {
        $codes = array('{{firstname}}','{{middlename}}','{{lastname}}','{{email}}','{{password}}','{name}}');
        $accurate = array($customer->getFirstname(),
                $customer->getMiddlename(),
                $customer->getLastname(),
                $customer->getEmail(),
                $pass,
                $customer->getFirstname().' '.$customer->getLastname()
                
        );
    
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'customer_registration/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function getVendorRegistrationMsg($data, $customer)
    {
        $codes = array('{{firstname}}','{{lastname}}','{{email}}','{{password}}','{{publicname}}','{{shopurl}}','{{name}}');
        $accurate = array($customer->getFirstname(),
                $customer->getLastname(),
                $customer->getEmail(),
                $data['password_confirmation'],
                $data['vendor']['public_name'],
                $data['vendor']['shop_url'],
                $customer->getFirstname().' '.$customer->getLastname()
        );
         
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_registration/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function getVendorStatusMsg($vendor, $status)
    {
        $codes = array('{{name}}','{{email}}','{{status}}');
        $accurate = array($vendor->getName(),
                $vendor->getEmail(),
                $status
        );
    
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_status/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function getVendorNewProductMgs($vendor, $product)
    {
        $codes = array('{{name}}','{{email}}', '{{productname}}','{{sku}}');
        $accurate = array($vendor->getName(),
                $vendor->getEmail(),
                $product->getName(),
                $product->getSku()
        );
         
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_new_product/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function getVendorProductStatusMsg($vendor, $product, $checkStatus)
    {
        //$checkStatus = $product->getCheckStatus();//old status
        $status='';
        if($checkStatus == '0')
            $status = 'Not Approved';
        elseif ($checkStatus == '1')
        $status = 'Approved';
        elseif ($checkStatus == '2')
        $status = 'Pending';
        elseif ($checkStatus == '3')
        $status = 'Delete';
         
        $codes = array('{{name}}','{{email}}', '{{productname}}','{{sku}}','{{status}}');
        $accurate = array($vendor->getName(),
                $vendor->getEmail(),
                $product->getName(),
                $product->getSku(),
                $status
        );
         
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_product_status/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function newVendorPaymentMsg($payment, $vendor)
    {
        $orderIds = '';
        $transaction = $payment->getTransactionType();
        $transaction_type = '';
    
        if($transaction == '0')
            $transaction_type = 'Credit';
        elseif ($transaction == '1')
        $transaction_type = 'Debit';
         
        $amount_desc = json_decode($payment->getAmountDesc());
        foreach ($amount_desc as $key => $value) {
            $orderIds .= $key.', ';
        }
        $codes = array('{{name}}','{{transactionid}}','{{amount}}','{{orderids}}','{{paymentcode}}','{{transactiontype}}');
        $accurate = array($vendor->getName(),
                $payment->getTransactionId(),
                $payment->getBaseAmount(),
                $orderIds,
                $payment->getPaymentCode(),
                $transaction_type
        );
        return str_replace($codes,$accurate,$this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_payment/message',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId()));
    }
    
    public function sendSms($to, $msg_body)
    {
        if($this->isExtensionEnabled()) {
            $account_sid = $this->getAccountSid();
            $auth_token = $this->getAuthToken();
            $from = trim(str_replace(' ','',$this->getSender()));
            $http = new \Services_Twilio_TinyHttp(
            'https://api.twilio.com',
            array('curlopts' => array(
                    CURLOPT_SSL_VERIFYPEER => false,
                    CURLOPT_SSL_VERIFYHOST => false,
            ))
        );
            $to = trim(str_replace(' ','',$to));
            $client = new \Services_Twilio($account_sid, $auth_token,"2010-04-01", $http);
            //$from="+".$this->_scopeConfigManager->getValue('marketplace/mptwiliomsgnotification_options/phone_number',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
            try{
                return $client->account->sms_messages->create($from, $to, $msg_body);
            }catch(\Magento\Framework\Exception $e){
                 $this->messageManager->addError($e->getMessage());
                $this->_logger->critical($e);
            }
        }
        else {
            return false;
        }
    }
    
    public function getCountryCodes()
    {
        $country_codes = $this->_scopeConfigManager->getValue('twilio_notification/enter/country_codes',\Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        //$country_codes = unserialize($country_codes);
        $country_codes = $this->_serialize->unserialize($country_codes);
        $options = array();
        if(count($country_codes)>0) {
            foreach ($country_codes as $key => $value) {
                if($key=='__empty')continue;
                if($value['country']) {
                    $options[$value['country']] = $value['code'];
                }
            }
        }
        return ($options);
    }

    public function customerAddressFields()
    {
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'customer_registration/customer_address_field',
                                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return $this->isExtensionEnabled();
    }

    public function vendorAddressFields()
    {
        if($this->isExtensionEnabled())
            return $this->_scopeConfigManager->getValue(self::CONFIG_PATH.'vendor_registration/vendor_address_field',
                                                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->getStore()->getId());
        else
            return $this->isExtensionEnabled();
    }
}

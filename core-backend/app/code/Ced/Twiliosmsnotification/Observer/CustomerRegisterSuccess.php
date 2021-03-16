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

namespace Ced\Twiliosmsnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerRegisterSuccess implements ObserverInterface
{
    protected $_httpRequest;
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    
      public function __construct(\Magento\Framework\ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
        $this->_httpRequest = $this->_objectManager->get('Magento\Framework\App\Request\Http');
    }

    public function getHelper()
    {
      return $this->_objectManager->get('\Ced\Twiliosmsnotification\Helper\Data');
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->getHelper()->isSectionEnabled('customer_registration/enable')) {
            if($customer = $observer->getEvent()->getCustomer()) {
                $telephone = $this->_httpRequest->getPost('telephone');
                $pass = $this->_httpRequest->getPost('password_confirmation');
          
                $country_id = $this->_httpRequest->getPost('country_id');
                $available_country_codes = $this->getHelper()->getCountryCodes();
                if(!empty($available_country_codes) && isset($available_country_codes[$country_id])) {
                    $telephone = $available_country_codes[$country_id].$telephone;
                } else {
                    $telephone = "+1".$telephone;  
                }

                try {
                    $is_vendor = $this->_httpRequest->getParam('is_vendor');
              
                    if ($telephone!="" && !isset($is_vendor)) {
                        $smsto = $telephone;
                        $smsmsg = $this->getHelper()->getCustomerRegistrationMsg($customer, $pass);
                        $this->getHelper()->sendSms($smsto, $smsmsg);
                    }
                    if($this->getHelper()->isSectionEnabled('customer_registration/enable') and $this->getHelper()->getAdminCustomerRegisterationTelephone()  && !isset($is_vendor)) {
                        $smsto = $this->getHelper()->getAdminCustomerRegisterationTelephone();
                        $smsmsg = __('New customer has been registered in your store');
                        $this->getHelper()->sendSms($smsto, $smsmsg);
                    }
                } catch (\Magento\Framework\Exception $e) {
                    $this->_logger->critical($e);
                }
            }
        }
    }
}

<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block CLass
 * For phone number coutry code
 */

namespace Cnnb\WhatsappApi\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Cnnb\WhatsappApi\Helper\Data;
use Magento\Directory\Api\CountryInformationAcquirerInterface;
use Magento\Store\Model\ScopeInterface;
use Cnnb\WhatsappApi\Logger\Logger;
use Magento\Customer\Api\AddressRepositoryInterface;

class PhoneNumber extends Template
{

    /**
     * @var Json
     */
    protected $jsonHelper;

    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CountryInformationAcquirerInterface
     */
    protected $countryInformation;

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var customerSession
     */
    protected $_customerSession;

    /**
     * @var customerRepository
     */
    protected $_customerRepository; 

    /**
     * @var messageManager
     */
    protected $_messageManager;

    /**
     * @var redirect
     */
    protected $_redirect;

    /**
     * @var storeManager
     */
    protected $_storeManager; 

    /**
     * @var customerFactory
     */
    protected $_customerFactory;

    /**
     * @var $_request
     */
    protected $_request; 

    /**
     * @var $_logger
     */
    protected $_logger;

    protected $_addressRepository;

    /**
     * number of digit for phone number
     */
    const XML_PATH_NO_OF_DIGIT = 'cnnb_whatsappapi/phone_number/digits';

    /**
     * warning message for phone number verification
     */
    const XML_PATH_FOR_WARNING_MESSAGE = 'cnnb_whatsappapi/warning_message/warning_message_text';

    /**
     * click here message for customers
     */
    const XML_PATH_FOR_CLICK_HERE_MESSAGE = 'cnnb_whatsappapi/warning_message/click_here_message';

    /**
     * For getting value of replace mobile number
     */
    const XML_PATH_FOR_REPLACE_MOBILE = 'cnnb_whatsappapi/warning_message/enable_replace_mobile';

    /**
     * For getting value of replace message
     */
    const XML_PATH_FOR_REPLACE_MESSAGE = 'cnnb_whatsappapi/warning_message/mobile_replacement_message';

    /**
     * For getting value of add address page message
     */
    const XML_PATH_FOR_ADD_ADDRESS_PAGE_MESSAGE = 'cnnb_whatsappapi/warning_message/message_for_add_address_page';

    /**
     * For getting value of update address page message
     */
    const XML_PATH_FOR_UPDATE_ADDRESS_PAGE_MESSAGE = 'cnnb_whatsappapi/warning_message/message_for_update_address_page';

    /**
     * For getting value of update address page message
     */
    const XML_PATH_FOR_WARNING_MESSAGE_STATUS = 'cnnb_whatsappapi/warning_message/warning_message_enable';

    /**
     * For getting value of allowed digit change status 
     */
    const XML_PATH_FOR_ALLOWED_DIGIT_STATUS = 'cnnb_whatsappapi/phone_number/digit_change_enable';

    /**
     * For getting value of allowed digit change message
     */
    const XML_PATH_FOR_ALLOWED_DIGIT_MESSAGE = 'cnnb_whatsappapi/phone_number/message_for_number_of_digits_change';

    /**
     * For getting value of all the country codes
     */
    const XML_PATH_FOR_COUNTRY_CODE = 'cnnb_whatsappapi/phone_number/all_country_codes';
    /**
     * PhoneNumber constructor.
     * @param Context $context
     * @param Json $jsonHelper
     */
    public function __construct(
        Context $context,
        Json $jsonHelper,
        CountryInformationAcquirerInterface $countryInformation,
        Data $helper,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\Response\Http $redirect,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\App\Request\Http $request,
        \Psr\Log\LoggerInterface $logger,
        AddressRepositoryInterface $addressRepository
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->helper = $helper;
        $this->countryInformation = $countryInformation;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession = $customerSession;
        $this->_customerRepository = $customerRepository;
        $this->_messageManager = $messageManager;
        $this->_redirect = $redirect;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_request = $request;
        $this->_logger = $logger;
        $this->_addressRepository = $addressRepository;
        parent::__construct($context);
    }

    /**
     * @return bool|string
     */
    public function phoneConfig()
    {
        $config  = [
            "nationalMode" => false,
            "utilsScript"  => $this->getViewFileUrl('Cnnb_WhatsappApi::js/utils.js'),
            "preferredCountries" => [$this->helper->preferedCountry()]
        ];

        if ($this->helper->allowedCountries()) {
            $config["onlyCountries"] = explode(",", $this->helper->allowedCountries());
        }

        return $this->jsonHelper->serialize($config);
    }

    /**
     * @return string|int
     */
    public function getDefaultDigit()
    {
        $data = $this->_scopeConfig->getValue(
            self::XML_PATH_NO_OF_DIGIT,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        return $data;
    }

    /**
     * Function for getting if warning message needs to show or not 
     * @return bool
     */
    public function getWarning()
    {
        $this->_logger->info('-------- #WhatsappAPI | warning data function starts --------');
        if ($this->_customerSession->getCustomer()->getId()) {
            $custId = $this->_customerSession->getCustomer()->getId();
        } elseif ($this->_customerSession->getCustomerId()) {
            $custId = $this->_customerSession->getCustomerId();
        } else {
            $custId = 0;
        }

        $this->_logger->info('#WhatsappAPI | Customer session ');
        /*$this->_logger->info('#WhatsappAPI | Customer ID '.$this->_customerSession->getCustomerId());
        $this->_logger->info(print_r($this->_customerSession->getData(), true));
        $this->_logger->info(print_r(get_class_methods($this->_customerSession), true));*/
        $this->_logger->info('#WhatsappAPI | Customer id: '.$custId);
        $data = [];
        $addressId = 0;
        if($custId){
            $this->_logger->info('#WhatsappAPI | If customer id exist then load customer data');
            $customer = $this->_customerRepository->getById($custId);
            $customerAddressCount = count($customer->getAddresses());
            if($customerAddressCount >= 1)
            {   
               $this->_logger->info('#WhatsappAPI | customer having multiple address');
               $addressData = $this->getCustomerAddressId($custId);
               $addressId = $addressData[0]['id'];
            } 
            $phoneVerifiedData = $customer->getCustomAttribute('phone_number_verified');
            if($phoneVerifiedData) {
                $this->_logger->info('#WhatsappAPI | if phoneVerifiedData is not null');
                $isNumberVerified = $phoneVerifiedData->getValue();
            } else {
                $this->_logger->info('#WhatsappAPI | if phoneVerifiedData is null');
                $isNumberVerified = 0;
            }

            $allowedDigitUpdatedData = $customer->getCustomAttribute('is_allowed_digit_update');
            if($allowedDigitUpdatedData) {
                $this->_logger->info('#WhatsappAPI | if allowedDigitUpdatedData is not null');
                $isAllowedDigitUpdate = $allowedDigitUpdatedData->getValue();
            } else {
                $this->_logger->info('#WhatsappAPI | if allowedDigitUpdatedData is null');
                $isAllowedDigitUpdate = 0;
            }

            $data = array(
                'cust_id' => $custId,
                'is_phone_verify'=> $isNumberVerified,
                'is_allowed_digit_update' =>$isAllowedDigitUpdate,
                'no_of_address' => $customerAddressCount,
                'address_id' => $addressId
            );
        } else {
            $this->_logger->info('#WhatsappAPI | if customer does not exist means guest user');
            $data = array(
                'cust_id' => 0, 
                'is_phone_verify'=> 0,
                'is_allowed_digit_update'=> 0,
                'no_of_address' => 0,
                'address_id' => $addressId
            );
        }

        return $data;
    }  

    /**
     * Function for getting customer address id 
     * @return array
     */
    public function getCustomerAddressId($customerId)
    {
        $customer = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customerModel = $customer->load($customerId);
        $customerAddressData = [];
        $customerAddress = [];
 
        if ($customerModel->getAddresses() != null)
        {
            foreach ($customerModel->getAddresses() as $address) {
                $customerAddress[] = $address->toArray();
            }
        }
 
        if ($customerAddress != null)
        {
            foreach ($customerAddress as $customerAddres)
            {
                $addressData = [];
                $addressData['id'] = $customerAddres['entity_id'];
                $customerAddressData[] = $addressData;
            }
        }

        return $customerAddressData;
    }

    /**
     * Function for getting customer address id having multiple address
     * @return array
     */
    public function getCustomerAddressIdForMultiple($customerId)
    {
        $customer = $this->_customerFactory->create();
        $websiteId = $this->_storeManager->getStore()->getWebsiteId();
        $customer->setWebsiteId($websiteId);
        $customerModel = $customer->load($customerId);
        $customerAddressData = [];
        $customerAddress = [];
 
        if ($customerModel->getAddresses() != null)
        {
            foreach ($customerModel->getAddresses() as $address) {
                $customerAddress[] = $address->toArray();
            }
        }
 
        if ($customerAddress != null)
        {
            foreach ($customerAddress as $customerAddres)
            {
                $customerAddressData[] = $customerAddres['entity_id'];
            }
        }  
     
        return $customerAddressData;
    }

    public function getDigitChangeData()
    {
        $allowedDigits = $this->getDefaultDigit();
        $verificationRequired = false; 
        $codesJson = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_COUNTRY_CODE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $codesArray = json_decode($codesJson,true);
        $countryCode = [];
        if ($this->_customerSession->getCustomer()->getId()) {
            $custId = $this->_customerSession->getCustomer()->getId();
        } elseif ($this->_customerSession->getCustomerId()) {
            $custId = $this->_customerSession->getCustomerId();
        } else {
            $custId = 0;
        }
        // $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
        // $logger = new \Zend\Log\Logger();
        // $logger->addWriter($writer);
        // $logger->info(print_r($codesArray,true));
        
        $addressIds = $this->getCustomerAddressId($custId);
        
        foreach ($addressIds as $key => $value) {
        
            $addressData = $this->_addressRepository->getById($value['id']);
            $phoneCode = $codesArray[strtolower($addressData->getCountryId())];
            $telephoneWithCode = $addressData->getTelephone();
            if(str_contains($telephoneWithCode, $phoneCode)){
               $telephoneWithoutCode = trim(explode($phoneCode, $telephoneWithCode)[1]);
               $legthOfTelephone = strlen($telephoneWithoutCode);
                if($legthOfTelephone!=$allowedDigits){
                   $verificationRequired = true;
                }
            } else {
              $verificationRequired = false;
            }
          
        }          
        return $verificationRequired;
    }

    /**
     * Function for getting warning message from backend
     * @return string
     */
    public function getWarningMessage($noOfAddress, $addressId)
    {

        $this->_logger->info("=========================================");
        $messageData = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_WARNING_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );  

        $clickHereMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_CLICK_HERE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $updatePageMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_UPDATE_ADDRESS_PAGE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        
        $addPageMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_ADD_ADDRESS_PAGE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $fullActionName = $this->getRequest()->getFullActionName();
        $this->_logger->info("### fullActionName: ".$fullActionName);
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $this->_logger->info("### baseUrl: ".$baseUrl);

        $redirectUrl = '';
        if($noOfAddress == 0) {
            $redirectUrl = $baseUrl.'customer/address/new/';
        } else if($noOfAddress >= 1) {
            $redirectUrl = $baseUrl.'customer/address/edit/id/'.$addressId;
        }

        $this->_logger->info("### Redirect URL: ".$redirectUrl);

        if($fullActionName == 'customer_address_form') {
            if($noOfAddress == 0) {

                $this->_messageManager->addWarning(__($addPageMessage));
                $this->_logger->info("### warning | addPageMessage : ".$addPageMessage);
            } elseif ($noOfAddress >= 1) {
                $this->_messageManager->addWarning(__($updatePageMessage));
                $this->_logger->info("### warning | updatePageMessage : ".$updatePageMessage);
            }
        } else {
            $this->_messageManager->addWarning(__($messageData. " ".$clickHereMessage,$redirectUrl));
        }
        $this->_logger->info("=========================================");
    } 

    /**
     * Function for getting redirect path
     * @return bool
     */
    public function getRedirectPath($noOfAddress, $addressId)
    {
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        if($noOfAddress == 0) {
            return $this->_redirect->setRedirect($baseUrl.'customer/address/new/');
        } else if($noOfAddress >= 1) {
            return $this->_redirect->setRedirect($baseUrl.'customer/address/edit/id/'.$addressId);
        }
    }

    /**
     * Function for getting admin setting 
     * @return string
     */
    public function getReplaceEnable()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_REPLACE_MOBILE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Function for getting replace message
     * @return string
     */
    public function getReplaceMessage()
    {
        return $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_REPLACE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
    }

    /**
     * Function for getting warning message enable data
     */
    public function warningMessageStatus()
    {
        $status = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_WARNING_MESSAGE_STATUS,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $status;
    }

    /**
     * Function for getting status of allowed digit change
     */
    public function allowedDigitChangeStatus()
    {
        $status = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_ALLOWED_DIGIT_STATUS,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        return $status;
    }

    /**
     * Function for getting allowed digit update message
     */
    public function getAllowedDigitUpdateMsg($noOfAddress, $addressId)
    {
        $digitUpdateMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_ALLOWED_DIGIT_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $clickHereMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_CLICK_HERE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );

        $updatePageMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_UPDATE_ADDRESS_PAGE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        
        $addPageMessage = $this->_scopeConfig->getValue(
            self::XML_PATH_FOR_ADD_ADDRESS_PAGE_MESSAGE,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $fullActionName = $this->getRequest()->getFullActionName();
        $baseUrl = $this->_storeManager->getStore()->getBaseUrl();
        $redirectUrl = '';
        if($noOfAddress == 0) {
            $redirectUrl = $baseUrl.'customer/address/new/';
        } else if($noOfAddress >= 1) {
            $redirectUrl = $baseUrl.'customer/address/edit/id/'.$addressId;
        }
        if($fullActionName == 'customer_address_form') {
            if($noOfAddress == 0) {
                $this->_messageManager->addWarning(__($addPageMessage));
            } elseif ($noOfAddress >= 1) {
                $this->_messageManager->addWarning(__($updatePageMessage));
            }
        } else {
            $this->_messageManager->addWarning(__($digitUpdateMessage. " ".$clickHereMessage,$redirectUrl));
            $this->_logger->info("Allowed digit updated msg: ".$digitUpdateMessage. " ".$clickHereMessage);
            $this->_logger->info("Allowed digit updated msg redirect url: ".$redirectUrl);
        }
    }
}

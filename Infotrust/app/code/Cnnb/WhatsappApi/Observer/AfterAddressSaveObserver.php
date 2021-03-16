<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Observer for saving customer attribute
 */

namespace Cnnb\WhatsappApi\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class AfterAddressSaveObserver implements ObserverInterface
{
    /**
     * @var customerRepository
     */
    protected $_customerRepository;

    /**
     * @var customer
     */
    protected $_customer;

    /**
     * @var customerFactory
     */
    protected $_customerFactory;

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var Cnnb\WhatsappApi\Block\PhoneNumber
     */
    protected $_phoneNumberBlock;

    /**
     * @var \Magento\Customer\Api\AddressRepositoryInterface
     */
    protected $_addressRepository;

    /**
     * @var $messageManager
     */
    protected $_messageManager;

    /**
     * @var $_logger
     */
    protected $_logger;

    /**
     * @param CustomerRepositoryInterface $customerRepository
     * @param Magento\Customer\Model\Address $addressRepository
     * @param Cnnb\WhatsappApi\Block\PhoneNumber $phoneNumberBlock,
     * @param Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory
     * @param Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        CustomerRepositoryInterface $customerRepository,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\ResourceModel\CustomerFactory $customerFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Cnnb\WhatsappApi\Block\PhoneNumber $phoneNumberBlock,
        \Magento\Customer\Model\Address $addressRepository,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->_customerRepository = $customerRepository;
        $this->_customer = $customer;
        $this->_customerFactory = $customerFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->_phoneNumberBlock = $phoneNumberBlock;
        $this->_addressRepository = $addressRepository;
        $this->_messageManager = $messageManager;
        $this->_logger = $logger;
    }
    
    /**
     * Customer address save after handler
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_logger->info('');
        $this->_logger->info("Class: " . __CLASS__. ", Function: ". __FUNCTION__);
        $this->_logger->info('-------- #WhatsappAPI | Observer starts ----------');
        $flag = false;
        $digitFlag = false;
        try {
            $customerAddress = $observer->getCustomerAddress();
            $customerId = $customerAddress->getData('customer_id');
            $customer = $this->_customer->load($customerId);
            $data = true;
            $customerData = $customer->getDataModel();
            $phoneVerifiedData = $customerData->getCustomAttribute('phone_number_verified');
            $allowedDigitData = $customerData->getCustomAttribute('is_allowed_digit_update');
            // $allowedDigitData = $this->_phoneNumberBlock->getDigitChangeData();

            /* $flag for checking if the phone_number_verified is set or not*/
            if (!$phoneVerifiedData) {
                $this->_logger->info('#WhatsappAPI | phoneVerifiedData is not set| Ready for new value.');
                $flag = true;
            } elseif ($phoneVerifiedData) {
                $this->_logger->info('#WhatsappAPI | phoneVerifiedData is set.');
                $isNumberVerified = $phoneVerifiedData->getValue();

                if (!$isNumberVerified) {
                    $this->_logger->info('#WhatsappAPI | phone number is not verified');
                    $flag = true;
                } else {
                    $this->_logger->info('#WhatsappAPI | phone number is verified.');
                }
            }

                        /* $flag for checking if the is_allowed_digit_update is set or not*/
            if(!$allowedDigitData){
                $this->_logger->info('#WhatsappAPI | allowedDigitData is not set| Ready for new value.');
                $digitFlag = true;
            } else if($allowedDigitData){
                $this->_logger->info('#WhatsappAPI | allowedDigitData is set.');
                $isDigitChanged = $allowedDigitData->getValue();

                if($isDigitChanged) {
                    $this->_logger->info('#WhatsappAPI | allowed digit has been changed and verification required');
                    $digitFlag = true;
                } else {
                    $this->_logger->info('#WhatsappAPI | Allowed digit not changed, verification not required');              
                }
            }

            if ($flag || $digitFlag) {
                $this->_logger->info('#WhatsappAPI | Flag or digitflag is true | start setting value of attribute');
                /* ---- attribute saving ---- */
                try {
                    /*If flag is set then phone_number_verified attribute will be true*/
                    if ($flag) {
                        $this->saveCustomerAttribute($customer, $customerData, 'phone_number_verified', true);
                        $this->_logger->info('#WhatsappAPI | phone_number_verified customer attribute has been saved. ');
                    }
                    /*If digitFlag is set then is_allowed_digit_update attribute will be false*/
                    if($digitFlag){
                        $this->saveCustomerAttribute($customer, $customerData, 'is_allowed_digit_update', false);
                        $this->_logger->info('#WhatsappAPI | is_allowed_digit_update customer attribute has been saved. ');
                    }
                } catch (Exception $e) {
                    $this->_logger->info('#WhatsappAPI | Exception[129] | '.$e->getMessage());
                }
                /* ---- attribute saving ends ---- */

                $getWarningData = $this->_phoneNumberBlock->getWarning();
                $replaceEnable = $this->_phoneNumberBlock->getReplaceEnable();
                $replaceMessage = $this->_phoneNumberBlock->getReplaceMessage();
                $getAddressIds = $this->_phoneNumberBlock->getCustomerAddressIdForMultiple($customerId);
                $getWarningData = $this->_phoneNumberBlock->getWarning();
                $updatedPhoneAddressId = $getWarningData['address_id'];
                $getAddress = $this->_addressRepository->load($updatedPhoneAddressId);
                $updatedTelephone = $getAddress->getTelephone();
                $this->_logger->info('#WhatsappAPI | check if multiple address and replace enable');
                if ($getWarningData['no_of_address'] > 1 && $replaceEnable == true) {
                    $this->_logger->info('#WhatsappAPI | Multiple addresses exists and replace is enabled');
                    foreach ($getAddressIds as $key => $value) {
                        $isUpdated = $this->updateAddresses($value, $updatedTelephone);
                    }
                    $message = __($updatedTelephone .' '.$replaceMessage);
                    $this->_messageManager->addSuccessMessage($message);
                } else {
                    $this->_logger->info('#WhatsappAPI | No multiple addresses and "Replace" is not enabled.');
                }
            }

            $this->_logger->info('=============================================');
        } catch (Exception $e) {
            $this->_logger->info(' #WhatsappAPI | something went wrong | '.$e->getMessage());
            $this->_messageManager->addErrorMessage(__('something went wrong while saving the data.'));
        }

        return $this;
    }

    /**
     * Update phone number to all address
     * @return boolean
     */
    public function updateAddresses($addressIdToUpdate, $updatedTelephone)
    {
        $this->_logger->info('#WhatsappAPI | Function : updateAddresses ');
        try {
            $addressToUpdate = $this->_addressRepository->load($addressIdToUpdate);
            $addressToUpdate->setTelephone($updatedTelephone);
            $this->_addressRepository->save($addressToUpdate);
        } catch (Exception $e) {
            $this->_logger->info('#WhatsappAPI | Exception[169] | '.$e->getMessage());
        }

        return true;
    }

    /**
     * save customer attribute
     */
    public function saveCustomerAttribute($customer, $customerData, $attribute_name, $attribute_value)
    {
        try {
            $customerData->setCustomAttribute($attribute_name, $attribute_value);
            $customer->updateData($customerData);
            $customerResource = $this->_customerFactory->create();
            $customerResource->saveAttribute($customer, $attribute_name);
            $customerResource->save($customer);
        } catch (Exception $e) {
            $this->_logger->info(' ##WhatsappAPI | Exception[192] | '.$e->getMessage());
        }
    }
}

<?php

namespace Cnnb\WhatsappApi\Model\Config\Backend;

use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\ResourceModel\CustomerFactory;
use Psr\Log\LoggerInterface;

class AllowedDigits extends \Magento\Framework\App\Config\Value
{
    /**
     * @return $_logger
     */
    protected $_logger;

    /**
     * @return $this
     */

    public function afterSave()
    {
        $this->_logger = $this->getLogger();
        try {
            if ($this->isValueChanged()) {
                $this->_logger->info("========================");
                $this->_logger->info(" # Is AllowedDigits Has been changed #");
                $customerObject = ObjectManager::getInstance()->get(Customer::class);
                $customerData = $customerObject->getCollection()->addAttributeToSelect("*")->load();
                foreach ($customerData as $customer) {
                    $this->_logger->info("### Customer Name: ".$customer->getName()." #");
                    $this->saveCustomerAttribute($customer, $customer->getDataModel(), 'is_allowed_digit_update', true);
                }
            }
        } catch (Exception $e) {
            $this->_logger->info('Exception[47] | '.$e->getMessage());
        }
        return parent::afterSave();
    }
    
    /**
     * Function for saving attribute for all the customers
     */
    public function saveCustomerAttribute($customer, $customerData, $attribute_name, $attribute_value)
    {
        try {

            $data = true;
            $customerData->setCustomAttribute($attribute_name, $data);
            $customer->updateData($customerData);
            $customerResource = ObjectManager::getInstance()->get(CustomerFactory::class)->create();
            $customerResource->saveAttribute($customer, $attribute_name);
            $customerResource->save($customer);
            $customer->save();
        } catch (Exception $e) {
            $this->_logger->info('Exception[61] | '.$e->getMessage());
        }
    }

    /**
     * Function for getting logger
     */
    public function getLogger()
    {
        $logger = ObjectManager::getInstance()->get(LoggerInterface::class);
        return $logger;
    }
}

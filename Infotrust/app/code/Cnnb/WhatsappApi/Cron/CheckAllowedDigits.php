<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Cron File
 * For checking the allowed digits has been changed or not.
 */

namespace Cnnb\WhatsappApi\Cron;

use Magento\Store\Model\ScopeInterface;
use Cnnb\WhatsappApi\Helper\Data as CnnbHelper;
use Cnnb\WhatsappApi\Logger\Logger as CnnbLogger;
use Magento\Store\Model\StoreManagerInterface;
use Cnnb\WhatsappApi\Model\AllowedDigits;
use Magento\Framework\App\ObjectManager;
use Magento\Customer\Model\CustomerFactory;

class CheckAllowedDigits
{
    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var $cnnbHelper
     */
    protected $_cnnbHelper;

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * @var $storeManager
     */
    protected $_storeManager;   

    /**
     * @var $alllowedDigits
     */
    protected $_allowedDigits;

    /**
     * @var $_currentModel
     */
    protected $_currentModel;

    protected $_customerFactory;

    /**
     * Active cron
     */
    const ALLOWED_DIGITS = 'cnnb_whatsappapi/phone_number/digits';

    /**
     * @param Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param cnnbHelper $cnnbHelper
     * @param Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CnnbHelper $cnnbHelper,
        CnnbLogger $customLogger,
        StoreManagerInterface $storeManager,
        AllowedDigits $allowedDigits,
        CustomerFactory $customerFactory
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_cnnbHelper = $cnnbHelper;
        $this->_logger = $customLogger;
        $this->_storeManager = $storeManager;
        $this->_allowedDigits = $allowedDigits;
        $this->_customerFactory = $customerFactory;
    }

    public function execute()
    {
        try {
            $this->_logger->info('============ Cnnb\WhatsappApi\Cron\CheckAllowedDigits ============');
            $this->_logger->info('Date '.date("Y-m-d h:i:sa"));
            $this->_logger->info('Allowed Digits '.$this->getAllowedDigits());
            $adminAllowedDigits = $this->getAllowedDigits();
            $allStores = $this->_cnnbHelper->getAllStoreIds();

            /*$writer = new \Zend\Log\Writer\Stream(BP . '/var/log/test.log');
            $logger = new \Zend\Log\Logger();
            $this->_logger->addWriter($writer);
*/
            foreach ($allStores as $key => $value) {
                $model = $this->isStoreDataExists($value);
                if ($model) {
                    try {
                        if($adminAllowedDigits != $this->_currentModel->getCurrentAllowedDigit()){
                            $this->_logger->info('### Saving for Store ID '.$value.' | Line 100 ###');
                            $this->_currentModel->setCurrentAllowedDigit($adminAllowedDigits)->save();
                            $this->setAttributeInCustomer($value);
                        }                        
                    } catch (Exception $e) {
                        $this->_logger->info('Exception: '.$e->getMessage());
                    }
                } else {
                    $this->_logger->info('### Saving for Store ID '.$value.' | Line 108 | New Data ###');
                    $dataModel = $this->_allowedDigits;
                    $dataModel->setData([
                    "store_id" => $value,
                    "config_path" => 'cnnb_whatsappapi/phone_number/digits',
                    "current_allowed_digit" => $adminAllowedDigits
                    ]);
                    $dataModel->save();
                    $this->setAttributeInCustomer($value);
                }
            }
        } catch (Exception $e) {
            $this->_logger->info('Exception '.$e->getMessage());            
        }
        $this->_logger->info('============ Cnnb\WhatsappApi\Cron\CheckAllowedDigits | Ends ============');
        return $this;
    }

    public function getAllowedDigitsModel()
    {
        return ObjectManager::getInstance()->create(AllowedDigits::class);
    }

    public function setAttributeInCustomer($store_id)
    {
        $customers = $this->_customerFactory->create()->getCollection()->addAttributeToSelect("*")->addAttributeToFilter("store_id", array("eq" => $store_id));
        foreach ($customers as $customer) {
            $customer->setIsAllowedDigitUpdate(true);
            $customer->save();
            $this->_logger->info('=======================');
            $this->_logger->info(print_r($customer->debug(), true));
            $this->_logger->info('=======================');
        }
    }

    /**
     *
     * @return int|boolean
     */
    public function getAllowedDigits()
    {
        return $this->_scopeConfig->getValue(self::ALLOWED_DIGITS, ScopeInterface::SCOPE_STORE);
    }

    public function isStoreDataExists($store_id)
    {
        $main_model = $this->getAllowedDigitsModel();

        $model = $main_model->load($store_id, 'store_id');
        if($model->hasData()){
            $this->_currentModel = $model;
            return true;
        } else {
            return false;
        }
    }
}

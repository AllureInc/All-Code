<?php
namespace Mangoit\LifeRayConnect\Model;
use Mangoit\LifeRayConnect\Api\CustomerComplianceInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerComplianceStatus implements CustomerComplianceInterface
{

    /**
     * @var orderInterface
     */
    protected $orderInterface;
    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;
        
    /**
     * @var customerRepositoryInterface
    */    
    protected $_customerRepositoryInterface;

    protected $_customer;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        LoggerInterface $logger,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->orderInterface = $orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->_customer = $customer;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }
    /**
     * Update customer compliance status
     *
     * @api
     * @param string $email Email of customer
     * @param string $compliancestatus is the status of the customer.
     * @return string success or failure message
     */
    public function customerupdate($email, $compliancestatus) {
        $objectManager = ObjectManager::getInstance();
        $this->validation($email, $compliancestatus);
        $customer = $this->_customer->loadByEmail($email);
        if ($customer->getId()) {
            $compliancestatus = (($compliancestatus == 10) ? 1 : 0);
            $customerObj = $this->_customerRepositoryInterface->getById($customer->getId());
            $customerObj->setCustomAttribute('compliance_check',$compliancestatus); 
            $this->_customerRepositoryInterface->save($customerObj);
            return $response = ['success' => 'Successfully saved!'];
        } else {
            throw new InputException(__('No customer found!'));
        }
    }
    public function validation($email, $status)
    {
        $statuses = [0, 10];
        $textOnly = '/^[a-zA-Z ]+$/';
        $emailOnly = '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/';
        if (strlen(trim($email)) > 0) {
            if (!preg_match($emailOnly, trim($email))) {
                throw new \Magento\Framework\Webapi\Exception(__('Email id is not valid.'));
            }           
        } else {
            throw new \Magento\Framework\Webapi\Exception(__('Email id is not valid'));
        }
        if (!in_array($status, $statuses)) {
            throw new InputException(__('No such status found!'));
        }
    }
}
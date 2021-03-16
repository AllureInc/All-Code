<?php
namespace Mangoit\LifeRayConnect\Model;
use Mangoit\LifeRayConnect\Api\CustomerCreateInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;

class CustomerCreate implements CustomerCreateInterface
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
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->orderInterface = $orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }
    /**
     * Create new customer in Magento
     *
     * @api
     * @param string $firstname is the firstname of the customer
     * @param string $lastname is the lastname of the customer.
     * @param string $email is the email of the customer.
     * @param string $password is the password of the customer.
     * @return string success or failure message
     */
    public function createcustomer($firstname, $lastname, $email, $password) {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $customerModel = $objectManager->create('\Magento\Customer\Model\Customer');
        $customerFactory = $objectManager->create('\Magento\Customer\Model\CustomerFactory');
        $websiteId = $storeManager->getWebsite()->getWebsiteId();
        $loadedCustomer = $customerModel->loadByEmail($email);
        $this->validation($firstname, $lastname, $email, $password);
        if (count($loadedCustomer->getData()) > 0) {
        	throw new \Magento\Framework\Webapi\Exception(__('Email id already exist'));
        }        
        $customer = $customerFactory->create();
        $customer->setWebsiteId($websiteId);
		$customer->setEmail($email);
		$customer->setFirstname($firstname);
		$customer->setLastname($lastname);
		$customer->setPasswordHash($password.':2');
		$customer->save();
		$id = $customer->getEntityId();


        return ['status'=> '200', 'message' => 'New customer has been added with id '.$id];
    }

    public function validation($firstname, $lastname, $email, $password)
    {
    	$textOnly = '/^[a-zA-Z ]+$/';
    	$emailOnly = '/^[\w-\.]+@([\w-]+\.)+[\w-]{2,4}$/';
    	
    	if (strlen(trim($firstname)) > 0) {
	    	if (!preg_match($textOnly, trim($firstname))) {
	    		throw new \Magento\Framework\Webapi\Exception(__('Name should contains only alphabets.'));
	    	}    		
    	} else {
    		throw new \Magento\Framework\Webapi\Exception(__('Invalid name.'));
    	}

    	if (strlen(trim($lastname)) > 0) {
	    	if (!preg_match($textOnly, trim($lastname))) {
	    		throw new \Magento\Framework\Webapi\Exception(__('LastName should contains only alphabets.'));
	    	}    		
    	} else {
    		throw new \Magento\Framework\Webapi\Exception(__('Invalid LastName.'));
    	}

    	if (strlen(trim($email)) > 0) {
	    	if (!preg_match($emailOnly, trim($email))) {
	    		throw new \Magento\Framework\Webapi\Exception(__('Email id is not valid.'));
	    	}    		
    	} else {
    		throw new \Magento\Framework\Webapi\Exception(__('Email id is not valid'));
    	}

    }
}
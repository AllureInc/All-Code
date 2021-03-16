<?php


namespace Kerastase\Core\Helper;


use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

class Data extends AbstractHelper
{
    protected $storeManager;

    protected $_url;

    protected $_pageFactory;

    protected $_filterProvider;
    /**
     * @var \Magento\Customer\Model\Customer
     */
    private $_customer;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Kerastase\Core\Model\ResourceModel\Customer
     */
    private $customerResource;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;

    const  ATTRIBUTES_CUSTOMER_LIST = ['firstname', 'lastname', 'middlename'];
    const ATTRIBUTES_ADDRESS_LIST =  ['street', 'city', 'company', 'telephone', 'fax', 'postcode', 'vat_id'];


    const SALTY_ENCRYPTOR_START = '$dhiju$';

    public function __construct(
        Context $context,
        \Magento\Framework\Url $url,
        \Magento\Cms\Model\PageFactory $pageFactory,
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        \Magento\Customer\Model\Customer $customer,
        \Psr\Log\LoggerInterface $logger,
        \Kerastase\Core\Model\ResourceModel\Customer $customerResource,
        EncryptorInterface $encryptor
    )
    {
        $this->storeManager = $storeManager;
        $this->_customer = $customer;
        $this->logger = $logger;
        $this->customerResource = $customerResource;
        $this->_url = $url;
        $this->_pageFactory = $pageFactory;
        $this->_filterProvider = $filterProvider;
        $this->encryptor = $encryptor;

        parent::__construct($context);
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreConfig($path, $store = null)
    {
        if ($store == null || $store == '') {
            $store = $this->storeManager->getStore()->getId();
        }
        $store = $this->storeManager->getStore($store);
        $config = $this->scopeConfig->getValue(
            $path,
            ScopeInterface::SCOPE_STORE,
            $store);
        return $config;
    }


    /**
     * @param $customer
     * @param $attributesList
     * @throws \Exception
     */
    public function encryptCustomer($customer)
    {
        $attributesList = self:: ATTRIBUTES_CUSTOMER_LIST;
        $customer = $this->_customer->load($customer->getId());
        $this->log(__METHOD__, true);
        $this->logger->info('Customer Not encrypted', array($customer->getData()));

        foreach ($attributesList as $customer_attribute) {

            if ($customer->getData($customer_attribute)){
                $ecnrypted_value = self::SALTY_ENCRYPTOR_START.$this->encryptor->encrypt($customer->getData($customer_attribute));
                $customer->setData($customer_attribute, $ecnrypted_value);
            }
        }

        $this->customerResource->UpdateIsEncrypted($customer->getId(),1);
        $customer->save();
        $this->logger->info(str_repeat('=', 100));
        $this->logger->info('Customer Data After Encryption', array($customer->getData()));
    }


    /**
     * @param $message
     * @param bool $use_separator
     */
    public function log($message, $useSeparator = false)
    {

        if ($useSeparator) {
            $this->logger->info(str_repeat('=', 100));
        }

        $this->logger->info($message);

    }










}
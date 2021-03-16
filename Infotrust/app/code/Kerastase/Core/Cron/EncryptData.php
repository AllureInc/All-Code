<?php


namespace Kerastase\Core\Cron;


use Magento\Framework\Encryption\EncryptorInterface;

class EncryptData
{

    const  CUSTOMER_ENCRYPT_ENABLED = 'customer/security/enable';

    const ATTRIBUTES_ADDRESS_LIST =  ['street', 'city', 'company', 'telephone', 'fax', 'postcode', 'vat_id'];
    const SALTY_ENCRYPTOR_START = '$dhiju$';

    protected $_customer;
    protected $_customerFactory;
    /**
     * @var EncryptorInterface
     */
    private $encryptor;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Customer\Model\Address
     */
    private $address;
    /**
     * @var \Magento\Customer\Api\CustomerRepositoryInterface
     */
    private $customerRepository;
    /**
     * @var \Kerastase\Core\Model\ResourceModel\Customer
     */
    private $customerResource;
    /**
     * @var \Kerastase\Core\Helper\Data
     */
    private $_helper;
    /**
     * @var \Magento\Customer\Model\ResourceModel\Address\Collection
     */
    private $addressCollection;

    public function __construct(
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $customerCollection,
        \Magento\Customer\Model\ResourceModel\Address\CollectionFactory $addressCollection,

        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Model\Address $address,
        \Kerastase\Core\Model\ResourceModel\Customer $customerResource,
        EncryptorInterface $encryptor,
        \Psr\Log\LoggerInterface $logger,
        \Kerastase\Core\Helper\Data $helper
    )
    {

        $this->collectionFactory = $customerCollection;
        $this->addressCollection = $addressCollection;
        $this->_customer = $customer;
        $this->customerResource = $customerResource;
        $this->address = $address;
        $this->encryptor = $encryptor;
        $this->logger = $logger;
        $this->_helper = $helper;
    }


    public function execute()
    {

        try {
            if ($this->_helper->getStoreConfig(self::CUSTOMER_ENCRYPT_ENABLED)) {

                /** Encrypt Cutomer Personal data by default (firstname,middlename,lastname) */

                $customers = $this->getFilteredCustomerCollection();
                $this->_helper->log('Filtered CustomerCollection Count ' . count($customers), true);
                /****** Customer Attributes List to Encrypt *******/

                foreach ($customers as $customer) {
                    $this->_helper->encryptCustomer($customer);
                }

                /** Encrypt Address  data by default (street', 'city', 'company', 'telephone', 'fax', 'postcode', 'vat_id) */
                $this->encryptCustomersAddress();

            }
        } catch (\Exception $e) {
            $errorMessage = 'Exception::' . $e->getMessage();
            $this->_helper->log($errorMessage, true);
        }
    }

    /**
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public  function encryptCustomersAddress()
    {
        /****** Address Attributes List to Encrypt *******/
        $addressAttributes = self::ATTRIBUTES_ADDRESS_LIST;

        /** Encrypt Address Data  */

            $addresses = $this->getFilteredAddressCollection();
            foreach ($addresses as $addr) {
                $address = $this->address->load($addr->getId());
                foreach ($addressAttributes as $addressAttribute) {

                    if ($address->getData($addressAttribute)) {
                        $ecnrypted_value = self::SALTY_ENCRYPTOR_START.$this->encryptor->encrypt($address->getData($addressAttribute));
                        $address->setData($addressAttribute, $ecnrypted_value);
                    }
                }
                $this->customerResource->UpdateAddressIsEncrypted($address->getId(),1);
                $address->save();
                $this->logger->info('Customer Address Data after Encryption ', array($address->getData()));
            }

    }

    /**
     * Return all customers with data Not crypted
     * @return mixed
     */
    public function getFilteredCustomerCollection()
    {
        $this->logger->info(__METHOD__);
        $customerCollection = $this->collectionFactory->create();
        $customerCollection->addAttributeToSelect('*');
        $customerCollection->getSelect()->where(
            'is_encrypted = ?',
            0
        );
        return $customerCollection;
    }

    /**
     * @return \Magento\Customer\Model\ResourceModel\Address\Collection
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getFilteredAddressCollection()
    {
        $this->logger->info(__METHOD__);
        $addressCollection = $this->addressCollection->create();
        $addressCollection->addAttributeToSelect('*');
        $addressCollection->getSelect()->where(
            'is_encrypted = ?',
            0
        );
        return $addressCollection;
    }


}
<?php


namespace Kerastase\Customers\Cron;


use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Framework\App\ResourceConnection;

class AutoDeleteCustomers
{


    /**
     * @var \Magento\Customer\Model\customer
     */
    private $customer;
    /**
     * @var \Magento\Customer\Model\LogFactory
     */
    private $logFactory;
    /**
     * @var ResourceConnection
     */
    private $resource;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    private $_localeDate;
    /**
     * @var \Kerastase\Customers\Logger\Logger
     */
    private $logger;
    /**
     * @var CustomerRepository
     */
    private $customerRepository;
    /**
     * @var \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory
     */
    private $_quoteFactory;

    public function __construct(
        ResourceConnection $resource,
        \Magento\Customer\Model\Log $logFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Kerastase\Customers\Logger\Logger $logger,
        CustomerRepository $customerRepository,
        \Magento\Quote\Model\ResourceModel\Quote\CollectionFactory $quoteFactory
    )
    {
        $this->resource = $resource;
        $this->logFactory = $logFactory;
        $this->_localeDate = $localeDate;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->_quoteFactory = $quoteFactory;

    }


    public function execute()
    {

        try {
            $customersIds = $this->getInactiveCustomers();

            if ($customersIds) {
                $this->logger->info('List of Customers Ids', array($customersIds));

                foreach ($customersIds as $customerId){
                    $this->deleteCustomer($customerId);
                }
            }else{
                $this->logger->info('No Inactive customers Found ');
            }


        } catch (\Exception $exception) {
            throw new $exception;

        }

    }


    /**
     * Load inactive customers (not logged in since one year)
     * @return array
     */
    public function getInactiveCustomers()
    {
        $this->log(__METHOD__,true);

        $lastYear = $this->_localeDate->date()->modify('-1 year')->format('Y-m-d H:i:s');
        /** @var \Magento\Framework\DB\Adapter\AdapterInterface $connection */
        $connection = $this->resource->getConnection();

        $select = $connection->select()
            ->from($this->resource->getTableName('customer_log'), ['customer_id'])
            ->where(
                'last_login_at < ?',
                $lastYear
            );

        return $connection->fetchRow($select);

    }

    /**
     * @param $customerId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function deleteCustomer($customerId)
    {
        try{
            $this->log(__METHOD__,true);

            if($customerId){

                $this->customerRepository->deleteById($customerId);
                $quoteCollection = $this->_quoteFactory->create()->addFieldToFilter('customer_id', $customerId);


                if(count($quoteCollection)>0){
                    foreach($quoteCollection as $_quote){
                        $_quote->delete();
                    }
                }
            }

        }catch(\NoSuchElementException $ex){
            throw new $ex;

        }
    }
    /** Logging Utility
    *
    * @param $message
    * @param bool|false $useSeparator
    */
        public function log($message, $useSeparator = false)
        {
            if ($useSeparator) {
                $this->logger->info(str_repeat('=', 100));
            }

            $this->logger->info($message);
        }


}
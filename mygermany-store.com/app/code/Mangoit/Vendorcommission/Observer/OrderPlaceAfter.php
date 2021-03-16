<?php
    namespace Mangoit\Vendorcommission\Observer;

    use Magento\Framework\Event\ObserverInterface;
    use Magento\Framework\Event\Observer;
    use Magento\Framework\HTTP\Client\Curl;
    use Magento\Marketplace\Helper\Cache;
    use Magento\Backend\Model\UrlInterface;

    class OrderPlaceAfter implements ObserverInterface
    {

    /**
     * @var Curl
     */
    protected $curlClient;

    /**
     * @var string
     */
    protected $urlPrefix = 'https://';

    /**
     * @var string
     */
    protected $apiUrl = 'account.mygermany.com/web/content/marketplacews';

    /**
     * @var \Magento\Marketplace\Helper\Cache
     */
    protected $cache;

    /**
     * @var \Mangoit\LifeRayConnect\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mangoit\LifeRayConnect\Helper\Data
     */
    protected $customerRepository;

    /**
     * @var \Mangoit\LifeRayConnect\Helper\Data
     */
    protected $resource;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    protected $orderHelper;

    protected $customerSession;

    /**
     * @param Curl $curl
     * @param Cache $cache
     * @param UrlInterface $backendUrl
     */
    public function __construct(
        Curl $curl,
        Cache $cache,
        UrlInterface $backendUrl,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request,
        \Mangoit\LifeRayConnect\Helper\Data $helper,
        \Magento\Customer\Model\Session $customer,
        \Mangoit\Vendorcommission\Helper\OrderHelper $orderHelper
    ) {
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->resource = $resource;
        $this->_request = $request;
        $this->orderHelper = $orderHelper;
        $this->customerSession = $customer;
    }

    /**
     * @return string
     */
    public function getApiUrl()
    {
        return $this->urlPrefix . $this->apiUrl;
    }

    /**
     * Execute observer
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer ) {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/OrderPlaceAfter_06_10.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('##--- OrderPlaceAfter observer ---##');

        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getId();
        $customer = $observer->getEvent()->getCustomer();
        $logger->info(print_r($this->customerSession->getCustomer()->getData('liferay_customer_id'), true));

        if ($this->customerSession->getCustomer()->getData('liferay_customer_id')) {
            $connection = $this->resource->getConnection();
            // // https://account.mygermany.com/web/content/marketplacews?p_p_id=marketplacews_WAR_MarketplaceWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&function=createOrder
            $liferayId = $this->customerSession->getCustomer()->getData('liferay_customer_id');
            $post = $this->orderHelper->createPostToLifeRay($order, $lastOrderId, $liferayId);
            try {
                $apiEmail = $this->helper->getConfigValue(
                    'liferayconnect/liferaycredentials/email',
                    $this->helper->getStore()->getId()
                );
                $apiPass = $this->helper->getConfigValue(
                    'liferayconnect/liferaycredentials/password',
                    $this->helper->getStore()->getId()
                );
                
                $this->orderHelper->createOrderToLifeRay($post,$apiEmail,$apiPass,$order);
                
            } catch (Exception $e) {
                
                $logger->info('## Exception 1: '.$e->getMessage());
            }
            # code...
        } else {
            $logger->info(' =========== ## The liferay customer id is not available for this customer ## =========== ');
        }
        
    }
}
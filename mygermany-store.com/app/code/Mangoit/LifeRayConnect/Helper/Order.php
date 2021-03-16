<?php
/**
 * A Magento 2 module named Mangoit/LifeRayConnect
 * Copyright (C) 2017  
 * 
 * This file is part of Mangoit/LifeRayConnect.
 * 
 * Mangoit/LifeRayConnect is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mangoit\LifeRayConnect\Helper;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Marketplace\Helper\Cache;
use Magento\Backend\Model\UrlInterface;

class Order extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */

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

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        Curl $curl,
        Cache $cache,
        UrlInterface $backendUrl,
        \Magento\Customer\Model\ResourceModel\CustomerRepository $customerRepository,
        \Magento\Framework\App\ResourceConnection $resource,
        \Magento\Framework\App\RequestInterface $request,
        \Mangoit\LifeRayConnect\Helper\Data $helper,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->_storeManager = $storeManager;
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->resource = $resource;
        $this->_request = $request;
        parent::__construct($context);
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
    public function executeLiferayOperation() {
        $logger = $this->getLogger();
        $customer = $observer->getEvent()->getCustomer();
        $password = $this->_request->getParam('password');
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('customer_entity'); 
        /*$sql = "Select `password_hash` FROM " . $tableName." Where entity_id = ".$customer->getId();
        $result = $connection->fetchAll($sql); 
        $password = $result[0]['password_hash'];*/
        $post = [];
        /*$post['p_p_id'] = 'marketplacews_WAR_MarketplaceWSportlet';
        $post['p_p_lifecycle'] = 2;
        $post['p_p_state'] = 'normal';
        $post['p_p_mode'] = 'view';
        $post['p_p_cacheability'] = 'cacheLevelPage';
        $post['p_p_col_id'] = 'column-1';
        $post['function'] = 'adduser';
        $post['firstname'] = $customer->getFirstname();
        $post['lastname'] = $customer->getLastname();
        $post['email'] = $customer->getEmail();*/

$post = [];
$post['p_p_id'] = 'marketplacews_WAR_MarketplaceWSportlet';
$post['p_p_lifecycle'] = 2;
$post['p_p_state'] = 'normal';
$post['p_p_mode'] = 'view';
$post['p_p_cacheability'] = 'cacheLevelPage';
$post['p_p_col_id'] = 'column-1';
$post['function'] = 'createOrder';
$post['magentoorderid'] = '123';
$post['magentoordertype'] = 'dropship';
$post['customeremail'] = 'test@mygermany.com';
$post['customerid'] = '12';
$post['articlecount'] = '2';
$post['articles'][0] = [
    'articlename'=> 'Product Name 1',
    'articlecount'=> '1',
    'articlevalue'=> '150',
    'articlelength'=> '20',
    'articlewidth'=> '20',
    'articleheight'=> '20',
    'articleweight'=> '1',
    'articlelithium'=> '0',
];
$post['articles'][1] = [
    'articlename'=> 'Product Name 2',
    'articlecount'=> '1',
    'articlevalue'=> '50',
    'articlelength'=> '10',
    'articlewidth'=> '10',
    'articleheight'=> '10',
    'articleweight'=> '1',
    'articlelithium'=> '0',
];
$post['shippingSex'] = 'male';
$post['shippingFirstName'] = 'Test';
$post['shippingMiddleName'] = '';
$post['shippingLastName'] = 'User';
$post['shippingAddressLine1'] = 'Address Line 1';
$post['shippingAddressLine2'] = '';
$post['shippingZipcode'] = '452010';
$post['shippingCity'] = 'Indore';
$post['shippingProvince'] = 'Madhya Pradesh';
$post['shippingCountry'] = 'India';
$post['shippingPhone'] = '07312550843';
$post['shippingprice'] = '163';
$post['paymentmethod'] = 'banktransfer';
$post['shippingshipper'] = 'DHL';
$post['shippingcomments'] = 'Test Comment';



        /*$post['passwordhash'] = str_replace(":2",null,$password);*/
        $post['passwordhash'] = $password;

        $logger->info('Customer Email: '.$customer->getEmail());
        $logger->info('Customer Password: '.$password);

        try {
            $this->registerCutomerToLifeRay($post);
            
        } catch (Exception $e) {
            
            $logger->info('## Exception 1: '.$e->getMessage());
        }
    }

    /**
     * Gets partners json
     *
     * @return array
     */
    public function createOrderToLifeRay($fields = [])
    {
        $logger = $this->getLogger();

        $apiUrl = $this->getApiUrl();
        // print_r($fields);
        try {
            $apiEmail = $this->helper->getConfigValue(
                'liferayconnect/liferaycredentials/email',
                $this->helper->getStore()->getId()
            );
            $apiPass = $this->helper->getConfigValue(
                'liferayconnect/liferaycredentials/password',
                $this->helper->getStore()->getId()
            );
            $logger->info('# Authentication Email: '.$apiEmail);
            $logger->info('# Authentication Password: '.$apiPass);
            $fields_string = http_build_query($fields);

            $process = curl_init('https://account.mygermany.com/web/content/marketplacews');
            https://account.mygermany.com/web/content/marketplacews?
            curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($process, CURLOPT_USERPWD, $apiEmail . ":" . $apiPass);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($process);
            $info = curl_getinfo($process);
            $logger->info('## registerCutomerToLifeRay process : '.json_encode($process));
            $logger->info('## registerCutomerToLifeRay return : '.$return);
            $logger->info('## registerCutomerToLifeRay info : '.json_encode($info));
            curl_close($process);

            $response = $return;

            $logger->info('## registerCutomerToLifeRay Response : '.json_encode($response));
            if ($response['partners']) {
                $this->getCache()->savePartnersToCache($response['partners']);
                return $response['partners'];
            } else {
                return $this->getCache()->loadPartnersFromCache();
            }
        } catch (\Exception $e) {
            $logger->info('## registerCutomerToLifeRay exception : '.$e->getMessage());
            return $this->getCache()->loadPartnersFromCache();
        }
    }

    /**
     * @return Curl
     */
    public function getCurlClient()
    {
        return $this->curlClient;
    }

    /**
     * @return cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    /**
     * @return string
     */
    public function getReferer()
    {
        return \Magento\Framework\App\Request\Http::getUrlNoScript($this->backendUrl->getBaseUrl())
        . 'admin/marketplace/index/index';
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/life-ray.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        $logger->info('## registerCutomerToLifeRay ##');
        return $logger;
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

}
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

namespace Mangoit\LifeRayConnect\Observer\Customer;

use Magento\Framework\HTTP\Client\Curl;
use Magento\Marketplace\Helper\Cache;
use Magento\Backend\Model\UrlInterface;

class RegisterSuccess implements \Magento\Framework\Event\ObserverInterface
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

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_customerFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_customerRepositoryInterface;

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
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Mangoit\LifeRayConnect\Helper\Data $helper
    ) {
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
        $this->helper = $helper;
        $this->customerRepository = $customerRepository;
        $this->resource = $resource;
        $this->_request = $request;
        $this->_customerFactory = $customerFactory;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
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
    public function execute(
        \Magento\Framework\Event\Observer $observer
    ) {
        $logger = $this->getLogger();
        $customer = $observer->getEvent()->getCustomer();
        $password = $this->_request->getParam('password');
        $connection = $this->resource->getConnection();
        $tableName = $this->resource->getTableName('customer_entity'); 
        /*$sql = "Select `password_hash` FROM " . $tableName." Where entity_id = ".$customer->getId();
        $result = $connection->fetchAll($sql); 
        $password = $result[0]['password_hash'];*/
        $post = [];
        $post['p_p_id'] = 'marketplacews_WAR_MarketplaceWSportlet';
        $post['p_p_lifecycle'] = 2;
        $post['p_p_state'] = 'normal';
        $post['p_p_mode'] = 'view';
        $post['p_p_cacheability'] = 'cacheLevelPage';
        $post['p_p_col_id'] = 'column-1';
        $post['function'] = 'adduser';
        $post['firstname'] = $customer->getFirstname();
        $post['lastname'] = $customer->getLastname();
        $post['email'] = $customer->getEmail();
        /*$post['passwordhash'] = str_replace(":2",null,$password);*/
        $post['passwordhash'] = $password;

        $logger->info('Customer Email: '.$customer->getEmail());
        $logger->info('Customer Password: '.$password);

        try {
            $this->registerCutomerToLifeRay($post, $customer);
            
        } catch (Exception $e) {
            
            $logger->info('## Exception 1: '.$e->getMessage());
        }
    }

    /**
     * Gets partners json
     *
     * @return array
     */
    public function registerCutomerToLifeRay($fields = [], $customer)
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
            $logger->info('## registerCutomerToLifeRay Response TYPE :  '.gettype($return));           
            /*$logger->info('## registerCutomerToLifeRay Response : '.print_r($response, true));*/
            if (strpos($return,'"success"')) {
                $logger->info(' -------- API Success -------');
                $response =  str_replace('"}','',str_replace('{"success":"','', $return) );
                $logger->info(' -------- response :'.$response.' -------');
                $logger->info('## Customer number is available ##');
                $this->getCache()->savePartnersToCache($response);

                try {
                    $this->getCustomer($customer->getId(), str_replace('{}','', $response));                    
                } catch (Exception $e) {
                    $logger->info('## Customer not saved : '.$e->getMessage());
                }
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

    public function getCustomer($customerId, $liferayId)
    {
        $customer = $this->_customerFactory->create()->load($customerId)->getDataModel();
        $customer->setCustomAttribute('liferay_customer_id', $liferayId);
        $this->_customerRepositoryInterface->save($customer);
    }
}

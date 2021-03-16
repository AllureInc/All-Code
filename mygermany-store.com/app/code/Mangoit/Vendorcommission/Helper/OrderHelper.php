<?php

namespace Mangoit\Vendorcommission\Helper;

use Magento\Directory\Model\Country;
use Magento\Directory\Model\CountryFactory;
use Magento\Marketplace\Helper\Cache;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Backend\Model\UrlInterface;
use Magento\Framework\Serialize\SerializerInterface;

class OrderHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Country
     */
    public $countryFactory;
    /**
     * @var Curl
     */
    protected $curlClient;
    /**
     * @var \Magento\Marketplace\Helper\Cache
     */
    protected $cache;

    public $addressCollection;

    public $backendUrl;

    public $logger;

    protected $_productRepository;
    /**
     * @var SerializerInterface
     */
    protected $serializer;
    /**
     * @param Curl $curl
     * @param Cache $cache
     * @param UrlInterface $backendUrl
     */
    public function __construct(CountryFactory $countryFactory,
        \Magento\Sales\Model\ResourceModel\Order\Address\CollectionFactory $addressCollection,
        Curl $curl,
        Cache $cache,
        UrlInterface $backendUrl,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        SerializerInterface $serializer
    ) {
        $this->countryFactory = $countryFactory;
        $this->addressCollection = $addressCollection;
        $this->curlClient = $curl;
        $this->cache = $cache;
        $this->backendUrl = $backendUrl;
        $this->logger = $this->getLogger();
        $this->_productRepository = $productRepository;
        $this->serializer = $serializer;
    }
    public function getCountryName($countryId): string
    {
        $countryName = '';
        $country = $this->countryFactory->create()->loadByCode($countryId);
        if ($country) {
            $countryName = $country->getName();
        }
        return $countryName;
    }
    public function getShippingAddress($order) {
        /* check order is not virtual */
        if(!$order->getIsVirtual()) {
            $orderShippingId = $order->getShippingAddressId();
            $address = $this->addressCollection->create()->addFieldToFilter('entity_id',array($orderShippingId))->getFirstItem();
            return $address;
        }
        return null;
    }
    public function createPostToLifeRay($order, $lastOrderId, $liferayCustomerId) {
        $this->logger->info(' ------------- function : createPostToLifeRay ---------------');
        $post = [];
        $post['p_p_id'] = 'marketplacews_WAR_MarketplaceWSportlet';
        $post['p_p_lifecycle'] = 2;
        $post['p_p_state'] = 'normal';
        $post['p_p_mode'] = 'view';
        $post['p_p_cacheability'] = 'cacheLevelPage';
        $post['p_p_col_id'] = 'column-1';
        $post['function'] = 'createOrder';
        $post['magentoorderid'] = $lastOrderId ;
        $post['magentoordertype'] = "simple"; 
        $post['customeremail'] = $order->getCustomerEmail();
        $post['customerid'] = $liferayCustomerId;
        $post['articlecount'] = $order->getData('total_qty_ordered');
        $count = 0;
        foreach ($order->getAllItems() as $item) {
            $id = $item->getData('product_id');
            $product = $this->getProductById($id);;
            $mygmbh_shipping_product_length = $product->getResource()->getAttribute('mygmbh_shipping_product_length')->getFrontend()->getValue($product);
            $mygmbh_shipping_product_width = $product->getResource()->getAttribute('mygmbh_shipping_product_width')->getFrontend()->getValue($product);
            $mygmbh_shipping_product_height = $product->getResource()->getAttribute('mygmbh_shipping_product_height')->getFrontend()->getValue($product);
            
            $qty = $item->getQtyOrdered() ? $item->getQtyOrdered() : 0;
            for($i = 0; $i < $qty; $i++) {
                $post['articlename_'.$count] = $item->getName() ? $item->getName() : 'N/A'; 
                $post['articlecount_'.$count] = $item->getQtyOrdered() ? $item->getQtyOrdered() : 0;
                $post['articlevalue_'.$count] = $item->getPrice() ? $item->getPrice() : 0;
                $post['articlelength_'.$count] = ($mygmbh_shipping_product_length) ? $mygmbh_shipping_product_length : 1;
                $post['articlewidth_'.$count] = ($mygmbh_shipping_product_width) ? $mygmbh_shipping_product_width : 1;
                $post['articleheight_'.$count] = ($mygmbh_shipping_product_height) ? $mygmbh_shipping_product_height : 1;
                $post['articleweight_'.$count] = $item->getWeight() ? $item->getWeight() : 1 ;
                $post['articlelithium_'.$count] = false; 
                $this->logger->info("count == ".$count);
                $count++;
            }
        }
        if ($order->getShippingMethod() == 'dropship_dropship') {
            $shipAddressObj = $this->getShippingAddress($order);
            $post['shippingSex'] = $order->getCustomerGender();
            $post['shippingFirstName'] =  $shipAddressObj->getFirstname() ? $shipAddressObj->getFirstname() : 'N/A';
            $post['shippingLastName'] =  $shipAddressObj->getLastname() ? $shipAddressObj->getLastname() : 'N/A';
            // $post['shippingMiddleName'] =  $shipAddressObj->getFirstname() ? $shipAddressObj->getName() : 'N/A';
            $post['shippingAddressLine1'] = $shipAddressObj->getStreet() ? $shipAddressObj->getStreet() : 'N/A'; 
            $post['shippingZipcode'] = $shipAddressObj->getPostcode() ? $shipAddressObj->getPostcode() : 'N/A';
            $post['shippingCity'] = $shipAddressObj->getCity() ? $shipAddressObj->getCity() : 'N/A'; 
            // $post['shippingProvince'] = $shipAddressObj->getLastname() ? $shipAddressObj->getLastname() : 'N/A';//landmatrk 
            $post['shippingCountry'] = $shipAddressObj->getCountryId() ? $this->getCountryName($shipAddressObj->getCountryId()) : 'N/A'; ; 
            $post['shippingPhone'] = $shipAddressObj->getTelephone() ? $shipAddressObj->getTelephone() : 'N/A'; 
            $post['shippingprice'] = $order->getShippingAmount() ? $order->getShippingAmount() : 'N/A';
            $post['shippingshipper'] = $order->getShippingMethod() ? $order->getShippingMethod() : 'dropship_dropship';
            $payment = $order->getPayment();
            $method = $payment->getMethodInstance();
            $post['paymentmethod'] = $method ? $method->getTitle() : 'N/A';
        }
        return $post;
    }
    public function createOrderToLifeRay($fields = [],$apiEmail,$apiPass,$order)
    {
        $this->logger->info(' ------------- function : createOrderToLifeRay ---------------');
        $this->logger->info('## --fields-- ##'.print_r($fields,true).'# --fields-- ##');
        try {
            $this->logger->info('# Authentication Email: '.$apiEmail);
            $this->logger->info('# Authentication Password: '.$apiPass);
            $fields_string = http_build_query($fields);
            $this->logger->info('');
            $this->logger->info('## fields_string ##'.print_r($fields_string,true).'## fields_string ##');

            $process = curl_init('https://account.mygermany.com/web/content/marketplacews');
            curl_setopt($process, CURLOPT_HTTPAUTH, CURLAUTH_BASIC);
            curl_setopt($process, CURLOPT_USERPWD, $apiEmail . ":" . $apiPass);
            curl_setopt($process, CURLOPT_POST, 1);
            curl_setopt($process, CURLOPT_POSTFIELDS, $fields_string);
            curl_setopt($process, CURLOPT_RETURNTRANSFER, TRUE);
            $return = curl_exec($process);
            $info = curl_getinfo($process);
            $this->logger->info('curl reytun type = '.gettype($return));
            $this->logger->info('## createOrderToLifeRay process : '.json_encode($process));
            $this->logger->info('## createOrderToLifeRay return : '.$return);
            $this->logger->info('## createOrderToLifeRay info : '.json_encode($info));
            curl_close($process);

            $response = $return;
            $this->logger->info('');
            if (strpos($response,'"success"')) {
                $response =  str_replace('"}','',str_replace('{"success":"','', $return));
                $response = str_replace('{}','',$response);
                $order->setLiferayOrderId($response);
                $order->save();
                $this->logger->info('## liferay_order_id : '. $order->getLiferayOrderId());
            } 
        } catch (\Exception $e) {
            $this->logger->info('## registerCutomerToLifeRay exception : '.$e->getMessage());
            $this->logger->info('## loadPartnersFromCache  : '.$this->getCache()->loadPartnersFromCache());
            return $this->getCache()->loadPartnersFromCache();
        }
    }
    /**
     * @return cache
     */
    public function getCache()
    {
        return $this->cache;
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/LifeRayOrderAPI.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
    public function getProductById($id)
    {
        return $this->_productRepository->getById($id);
    }
}

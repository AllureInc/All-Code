<?php
/**
 * @author Mangoit Team
 * @copyright Copyright (c) 2018 Mangoit
 * @package Mangoit_Geoiptax
 */


namespace Mangoit\Vendorcommission\Plugin;

// use Amasty\GeoipRedirect\Model\Source\Logic;
use Magento\Store\Api\StoreResolverInterface;
use Magento\Store\Model\ScopeInterface;

class Action // extends \Amasty\GeoipRedirect\Plugin\Action
{
    const LOCAL_IP = '127.0.0.1'; 
    protected $redirectAllowed = false;

    protected $addressPath = [
        'HTTP_X_REAL_IP',
        'HTTP_CLIENT_IP',
        'HTTP_X_FORWARDED_FOR'
    ];
    
    /**
     * @var \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress
     */
    protected $remoteAddress;

    protected $taxRate;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Amasty\GeoipRedirect\Helper\Data
     */
    protected $geoipHelper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Amasty\Geoip\Model\Geolocation
     */
    protected $geolocation;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Store\Api\StoreCookieManagerInterface
     */
    protected $storeCookieManager;

    /**
     * @var \Magento\Backend\Model\View\Result\RedirectFactory
     */
    protected $resultRedirectFactory;

    /**
     * @var \Magento\Framework\App\Response\Http
     */
    private $response;

    /**
     * @var \Magento\Framework\Session\SessionManagerInterface $sessionManager
     */
    protected $sessionManager;

    /**
     * @var \Magento\Framework\Locale\ResolverInterface
     */
    protected $resolver;
    protected $countryCode;
    protected $requested;

    /**
     * @var \Magento\Framework\HTTP\Client\Curl
     */
    protected $_curl;

    public function __construct(
        \Magento\Framework\HTTP\PhpEnvironment\RemoteAddress $remoteAddress,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        // \Amasty\GeoipRedirect\Helper\Data $geoipHelper,
        \Magento\Framework\UrlInterface $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        // \Amasty\Geoip\Model\Geolocation $geolocation,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Api\StoreCookieManagerInterface $storeCookieManager,
        \Magento\Backend\Model\View\Result\RedirectFactory $resultRedirectFactory,
        \Magento\Framework\App\Response\Http $response,
        \Magento\Framework\Session\SessionManagerInterface $sessionManager,
        \Magento\Framework\Locale\ResolverInterface $resolver,
        \Magento\Framework\App\RequestInterface $requested,
        \Magento\Framework\HTTP\Client\Curl $curl
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->scopeConfig = $scopeConfig;
        // $this->geoipHelper = $geoipHelper;
        $this->urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        // $this->geolocation = $geolocation;
        $this->customerSession = $customerSession;
        $this->storeCookieManager = $storeCookieManager;
        $this->resultRedirectFactory = $resultRedirectFactory;
        $this->response = $response;
        $this->sessionManager = $sessionManager;
        $this->resolver = $resolver;
        $this->requested = $requested;
        $this->_curl = $curl;
    }

    public function getUserIp()
    {
        $currentIp = $this->getCurrentIp($this->requested);
        return $currentIp;
    }

    public function getCountry()
    {
        
        // updated code 12-july-2018
        $currentIp = $this->getCurrentIp($this->requested);
       // $ip_data = @json_decode(file_get_contents("https://www.geoplugin.net/json.gp?ip=".$currentIp));    

        $apiUrl = 'http://www.geoplugin.net/json.gp?ip='.$currentIp;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiUrl);
           // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = json_decode(curl_exec($ch),true);
        curl_close ($ch);
        if (isset($server_output['geoplugin_countryCode'])) {
            return $server_output['geoplugin_countryCode'];
        }
        return 'DE';
    }



    public function getCountryCode()
    {
        $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $taxObject = $newObjectManager->get('Magento\Tax\Model\Calculation\Rate');
        $taxObject = $taxObject->getCollection()->addFieldToFilter('tax_country_id', $this->getCountry())->getData();
        // $taxObject = $taxObject->getCollection()->addFieldToFilter('tax_country_id', $this->countryCode)->getData();
        foreach ($taxObject as $key ) {            
            $this->taxRate= " ".$key['rate'];
        }
        return $this->taxRate;
    }

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     * @return string
     */
    private function getCurrentIp($request)
    {
        foreach ($this->addressPath as $path) {
            $ip = $request->getServer($path);
            if ($ip) {
                if (strpos($ip, ',') !== false) {
                    $addresses = explode(',', $ip);
                    foreach ($addresses as $address) {
                        if (trim($address) != self::LOCAL_IP) {
                            return trim($address);
                        }
                    }
                } else {
                    if ($ip != self::LOCAL_IP) {
                        return $ip;
                    }
                }
            }

        }

        return $this->remoteAddress->getRemoteAddress();
    }

    public function test()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $taxClassObj = $objectManager->create('Magento\Tax\Model\TaxClass\Source\Product');
        return $taxClassObj;
    }
}

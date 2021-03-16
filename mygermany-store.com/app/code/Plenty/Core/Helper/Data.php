<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Encryption\EncryptorInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Plenty\Core\Helper\Rest\RouteInterface;

/**
 * Class Data
 * @package Plenty\Core\Helper
 */
class Data extends AbstractHelper implements RouteInterface
{
    // GENERAL
    const XML_PATH_CONFIG_LICENSE                   = 'plenty_core/general/license';
    const XML_PATH_CONFIG_LICENSE_VALIDATE          = 'plenty_core/general/validate';

    // APP CONFIG
    const XML_PATH_AUTH_APP_NAME                    = 'plenty_core/auth/app_name';
    const XML_PATH_AUTH_APP_URL                     = 'plenty_core/auth/app_url';
    const XML_PATH_AUTH_APP_USERNAME                = 'plenty_core/auth/app_username';
    const XML_PATH_AUTH_APP_PASSWORD                = 'plenty_core/auth/app_password';
    const XML_PATH_AUTH_PLENTY_ID                   = 'plenty_core/auth/plenty_id';
    const XML_PATH_AUTH_OWNER_ID                    = 'plenty_core/auth/owner_id';
    const XML_PATH_AUTH_STORE_ID                    = 'plenty_core/auth/store_id';

    // API CONFIG
    const XML_PATH_API_RETRIES                      = 'plenty_core/api_config/api_retry';
    const XML_PATH_API_CONNECTION_TIMEOUT           = 'plenty_core/api_config/api_connection_timeout';
    const XML_PATH_API_TIMEOUT                      = 'plenty_core/api_config/api_timeout';

    // DEVELOPER
    const XML_PATH_DEV_LOG_DIRECTORY                = 'log/plenty/';
    const XML_PATH_DEV_DEBUG                        = 'plenty_core/dev/debug_enabled';
    const XML_PATH_DEV_DEBUG_DIRECTORY_NAME         = 'plenty_core/dev/debug_directory';
    const XML_PATH_DEV_DEBUG_LEVEL                  = 'plenty_core/dev/debug_level';

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var
     */
    protected $_scopeConfig;

    /**
     * @var EncryptorInterface
     */
    protected $_encryptor;

    /**
     * Contains all log information
     *
     * @var string[]
     */
    protected $_logTrace = [];

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var TimezoneInterface
     */
    private $_timezone;

    /**
     * Data constructor.
     * @param Context $context
     * @param EncryptorInterface $encryptor
     * @param StoreManagerInterface $storeManager
     * @param DateTime $date
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        EncryptorInterface $encryptor,
        StoreManagerInterface $storeManager,
        DateTime $date,
        TimezoneInterface $timezone
    ) {
        $this->_storeManager = $storeManager;
        $this->_encryptor = $encryptor;
        $this->_dateTime = $date;
        $this->_timezone = $timezone;
        parent::__construct($context);
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    protected function _getStore()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * @param $path
     * @param null $store
     * @return mixed
     * @throws NoSuchEntityException
     */
    protected function _getConfig($path, $store = null)
    {
        if (null === $store) {
            $store = $this->_getStore();
        }

        return $this->scopeConfig
            ->getValue($path, ScopeInterface::SCOPE_STORE, $store);
    }

    /**
     * @param string $scope
     * @return bool
     */
    public function isEnabled($scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT)
    {
        return $this->scopeConfig->isSetFlag(
            'plenty_core/general/enabled',
            $scope
        );
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getRegLicense()
    {
        return $this->_getConfig(self::XML_PATH_CONFIG_LICENSE);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getAppName()
    {
        return $this->_getConfig(self::XML_PATH_AUTH_APP_NAME);
    }

    /**
     * @param $route
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAppUrl($route)
    {
        return $this->_getConfig(self::XML_PATH_AUTH_APP_URL).$route;
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getUserName()
    {
        return $this->_getConfig(self::XML_PATH_AUTH_APP_USERNAME);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getPassword()
    {
        $pass = $this->_getConfig(self::XML_PATH_AUTH_APP_PASSWORD);
        $secret = $this->_encryptor->decrypt($pass);
        return $secret;
    }

    /**
     * @return int
     * @throws NoSuchEntityException
     */
    public function getPlentyId()
    {
        return (int) $this->_getConfig(self::XML_PATH_AUTH_PLENTY_ID);
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getOwnerId() : ?int
    {
        return $this->_getConfig(self::XML_PATH_AUTH_OWNER_ID);
    }

    /**
     * @return int|null
     * @throws NoSuchEntityException
     */
    public function getPlentyStoreId() : ?int
    {
        return $this->_getConfig(self::XML_PATH_AUTH_STORE_ID);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getApiRetries()
    {
        return $this->_getConfig(self::XML_PATH_API_RETRIES);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getApiConnectionTimeout()
    {
        return $this->_getConfig(self::XML_PATH_API_CONNECTION_TIMEOUT);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getApiTimeout()
    {
        return $this->_getConfig(self::XML_PATH_API_TIMEOUT);
    }

    /**
     * @return string
     */
    public function getAuthUrl()
    {
        return 'https://mage2plenty.com/api/rest/m2p/service';
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getLoginUrl()
    {
        return $this->getAppUrl(self::LOGIN_URL);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getLogoutUrl()
    {
        return $this->getAppUrl(self::LOGOUT_URL);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getAccessTokenUrl()
    {
        return $this->getAppUrl(self::ACCESS_TOKEN_URL);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getRefreshTokenUrl()
    {
        return $this->getAppUrl(self::REFRESH_TOKEN_URL);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getWebStoresUrl()
    {
        return $this->getAppUrl(self::LIST_STORES_URL);
    }

    /**
     * @param null $vatId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getVatConfigUrl($vatId = null)
    {
        if (null === $vatId) {
            return $this->getAppUrl(self::VAT_CONFIG_URL);
        }
        return $this->getAppUrl(self::VAT_CONFIG_URL. '/'.$vatId);
    }

    /**
     * @param null $warehouseId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getWarehousesUrl($warehouseId = null)
    {
        if (null === $warehouseId) {
            return $this->getAppUrl(self::LIST_WAREHOUSES_URL);
        }
        return $this->getAppUrl(self::LIST_WAREHOUSES_URL. '/' .$warehouseId);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getBatchUrl()
    {
        return $this->getAppUrl(self::BATCH_URL);
    }

    /**
     * @param bool $UTC
     * @return bool
     * @throws NoSuchEntityException
     */
    public function setTimeZone2Local($UTC = false)
    {
        if ($UTC) {
            return date_default_timezone_set($UTC);
        }

        return date_default_timezone_set(
            $this->_getConfig('general/locale/timezone'));
    }

    /**
     * @param $input
     * @return string
     * @throws \Exception
     */
    public function getDateTimeLocale($input)
    {
        if (is_numeric($input)) {
            $result = $this->_dateTime
                ->gmtDate(null, $input);
        } else {
            $result = $input;
        }

        $dateTime = (new \DateTime($result))
            ->setTimezone(new \DateTimeZone($this->scopeConfig->getValue('general/locale/timezone')));
        $result = $dateTime->format(\DateTime::W3C);

        return $result;
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isDebugOn()
    {
        return (bool) $this->_getConfig(self::XML_PATH_DEV_DEBUG);
    }

    /**
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getLogPath()
    {
        return $this->_getConfig(self::XML_PATH_DEV_DEBUG_DIRECTORY_NAME) ?
            $this->_getConfig(self::XML_PATH_DEV_DEBUG_DIRECTORY_NAME) : 'core';
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDebugLevel()
    {
        return explode(',', $this->_getConfig(self::XML_PATH_DEV_DEBUG_LEVEL));
    }

    /**
     * @param $needle
     * @param array $haystack
     * @param $columnName
     * @param null $columnId
     * @return false|int|string
     */
    public function getSearchArrayMatch(
        $needle, array $haystack, $columnName, $columnId = null
    ) {
        return array_search($needle, array_column($haystack, $columnName, $columnId));
    }

    /**
     * Encrypt data
     *
     * @param $key
     * @param $string
     * @return string
     */
    public function encrypt($key, $string)
    {
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = openssl_random_pseudo_bytes($ivlen);
        $ciphertext_raw = openssl_encrypt($string, $cipher, $key, $options=OPENSSL_RAW_DATA, $iv);
        $hmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        $ciphertext = base64_encode($iv . $hmac .$ciphertext_raw);

        return $ciphertext;
    }

    /**
     * Decrypt Data
     *
     * @param $key
     * @param $string
     * @return string
     */
    public function decrypt($key, $string)
    {
        $c = base64_decode($string);
        $ivlen = openssl_cipher_iv_length($cipher = "AES-128-CBC");
        $iv = substr($c, 0, $ivlen);
        $hmac = substr($c, $ivlen, $sha2len = 32);
        $ciphertext_raw = substr($c, $ivlen + $sha2len);
        $original_string = openssl_decrypt($ciphertext_raw, $cipher, $key, $options = OPENSSL_RAW_DATA, $iv);
        $calcmac = hash_hmac('sha256', $ciphertext_raw, $key, $as_binary = true);
        if (hash_equals($hmac, $calcmac)) { //PHP 5.6+ timing attack safe comparison{
            return $original_string;
        }

        return $string;
    }
}

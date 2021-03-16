<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest;

use Plenty\Core\Model\Auth;
use Plenty\Core\Model\AuthFactory;
use Plenty\Core\Helper\Data as Helper;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Client
 * @package Plenty\Core\Rest
 */
class Client implements ClientInterface, PlentyServerInterface
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var Auth
     */
    protected $_authFactory;

    /**
     * Max supported protocol by curl CURL_SSLVERSION_TLSv1_2
     * @var int
     */
    private $sslVersion;

    /**
     * Request headers
     * @var array
     */
    protected $_headers             = [];

    /**
     * Fields for POST method - hash
     * @var array
     */
    protected $_postFields          = [];

    /**
     * Request cookies
     * @var array
     */
    protected $_cookies             = [];

    /**
     * Request timeout
     * @var int type
     */
    protected $_timeout             = 300;

    /**
     * TODO
     * @var int
     */
    protected $_redirectCount       = 0;

    /**
     * @var int
     */
    protected $_noRetry             = 0;

    /**
     * Curl
     * @var resource
     */
    protected $_curl;

    /**
     * User overrides options hash
     * Are applied before curl_exec
     *
     * @var array
     */
    protected $_curlUserOptions     = [];

    /**
     * Header count, used while parsing headers
     * in CURL callback function
     * @var int
     */
    protected $_headerCount         = 0;

    /**
     * @var array
     */
    protected $_errors              = [];

    /**
     * @var array
     */
    protected $_request             = [];

    /**
     * @var array
     */
    protected $_response            = [];

    /**
     * Response headers
     * @var array
     */
    protected $_responseHeaders     = [];

    /**
     * Response code
     * @var int
     */
    protected $_responseCode        = null;

    /**
     * Response status
     * @var int
     */
    protected $_responseStatus      = null;

    /**
     * @var null
     */
    protected $_responseMessage       = null;

    /**
     * @var array
     */
    protected $_curlInfo            = [];

    /**
     * @var array
     */
    protected $_log                 = [];

    /**
     * @var array
     */
    protected $_logLevel            = [];

    /**
     * @var array
     */
    protected $_unAuthenticatedResponse = [
        self::HTTP_MESSAGE_UNAUTHENTICATED,
        self::HTTP_INVALID_REFRESH_TOKEN,
        self::HTTP_ACCESS_DENIED_EXCEPTION,
        self::HTTP_AUTHENTICATION_EXCEPTION,
        self::HTTP_INVALID_REFRESH_EXCEPTION,
        self::HTTP_INVALID_REQUEST_EXCEPTION,
        self::HTTP_VALIDATION_INCORRECT_CREDENTIALS
    ];

    /**
     * Client constructor.
     * @param Helper $helper
     * @param AuthFactory $authFactory
     * @param null $sslVersion
     */
    public function __construct(
        Helper $helper,
        AuthFactory $authFactory,
        $sslVersion = null
    ) {
        $this->_helper = $helper;
        $this->sslVersion = $sslVersion;
        $this->_authFactory = $authFactory->create();
        $this->_authFactory->getResource()
            ->load($this->_authFactory, self::AUTH_BEARER, 'token_type');
    }

    /**
     * @return Helper
     */
    public function _helper()
    {
        return $this->_helper;
    }

    /**
     * @return Auth
     */
    public function getAuthFactory()
    {
        return $this->_authFactory;
    }

    /**
     * Set request timeout, msec
     *
     * @param int $value
     * @return void
     */
    public function setTimeout($value)
    {
        $this->_timeout = (int)$value;
    }

    /**
     * Set headers from hash
     *
     * @param array $headers
     * @return void
     */
    public function setHeaders($headers)
    {
        $this->_headers = $headers;
    }

    /**
     * Add header
     *
     * @param string $name name, ex. "Location"
     * @param string $value value ex. "http://google.com"
     * @return void
     */
    public function addHeader($name, $value)
    {
        $this->_headers[$name] = $value;
    }

    /**
     * Remove specified header
     *
     * @param string $name
     * @return void
     */
    public function removeHeader($name)
    {
        unset($this->_headers[$name]);
    }

    /**
     * Authorization: Basic header
     * Login credentials support
     *
     * @param string $login username
     * @param string $pass password
     * @return void
     */
    public function setCredentials($login, $pass)
    {
        $val = base64_encode("{$login}:{$pass}");
        $this->addHeader("Authorization", "Basic {$val}");
    }

    /**
     * Add cookie
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function addCookie($name, $value)
    {
        $this->_cookies[$name] = $value;
    }

    /**
     * Remove cookie
     *
     * @param string $name
     * @return void
     */
    public function removeCookie($name)
    {
        unset($this->_cookies[$name]);
    }

    /**
     * Set cookies array
     *
     * @param array $cookies
     * @return void
     */
    public function setCookies($cookies)
    {
        $this->_cookies = $cookies;
    }

    /**
     * Clear cookies
     * @return void
     */
    public function removeCookies()
    {
        $this->setCookies([]);
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }
    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders()
    {
        return $this->_responseHeaders;
    }
    /**
     * Get response body
     *
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @return array
     */
    public function getCurlInfo()
    {
        return $this->_curlInfo;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * Get cookies response hash
     *
     * @return array
     */
    public function getCookies()
    {
        if (empty($this->_responseHeaders['Set-Cookie'])) {
            return [];
        }
        $out = [];
        foreach ($this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row);
            $c = count($values);
            if (!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if ($val === null) {
                continue;
            }
            $out[trim($key)] = trim($val);
        }
        return $out;
    }

    /**
     * Get cookies array with details
     * (domain, expire time etc)
     * @return array
     */
    public function getCookiesFull()
    {
        if (empty($this->_responseHeaders['Set-Cookie'])) {
            return [];
        }
        $out = [];
        foreach ($this->_responseHeaders['Set-Cookie'] as $row) {
            $values = explode("; ", $row);
            $c = count($values);
            if (!$c) {
                continue;
            }
            list($key, $val) = explode("=", $values[0]);
            if ($val === null) {
                continue;
            }
            $out[trim($key)] = ['value' => trim($val)];
            array_shift($values);
            $c--;
            if (!$c) {
                continue;
            }
            for ($i = 0; $i < $c; $i++) {
                list($subkey, $val) = explode("=", $values[$i]);
                $out[trim($key)][trim($subkey)] = trim($val);
            }
        }
        return $out;
    }

    /**
     * Get response status code
     * @see lib\Magento\Framework\HTTP\Client#getStatus()
     *
     * @return int
     */
    public function getStatus()
    {
        return $this->_responseStatus;
    }

    /**
     * @param $uri
     * @param array $params
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function get($uri, array $params = []): array
    {
        return $this->makeRequest(self::GET, $uri, $params);
    }

    /**
     * @param string $uri
     * @param array|string $params
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function post($uri, array $params = []): array
    {
        return $this->makeRequest(self::POST, $uri, $params);
    }

    /**
     * @param $uri
     * @param $params
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function put($uri, array $params = []): array
    {
        return $this->makeRequest(self::PUT, $uri, $params);
    }

    /**
     * @param $uri
     * @param array $params
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function delete($uri, array $params = [])
    {
        return $this->makeRequest(self::DELETE, $uri, $params);
    }

    /**
     * @param $method
     * @param $uri
     * @param array $params
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function makeRequest($method, $uri, $params = []): array
    {
        try {
            $this->_noRetry += 1;
            $this->_call($method, $uri, $params);
            return $this->getResponse();
        } catch (\Exception $e) {
        }

        if (empty($this->_errors)) {
            $this->doError(__('Something has gone wrong. Please check logs for details.'));
        }

        if (null === $this->_responseMessage) {
            $this->extractMessage('error');
        }

        if (null === $this->_responseMessage) {
            $this->doError(__('Could not extract response message. Please check logs for details.'));
        }

        /**
         * Pause on retry if requests are throttled
         * @see https://developers.plentymarkets.com/rest-doc/introduction#rest-throttling
         */
        if (isset($this->_responseHeaders['retry-after'])) {
            usleep(((float)$this->_responseHeaders['retry-after'] * 1000000) + 500000);
            $this->_call($method, $uri, $params);
            return $this->getResponse();
        }

        if (in_array($this->_responseMessage, $this->_unAuthenticatedResponse)) {
            $this->_auth();
        }

        if (empty($this->getErrors()) && $this->_noRetry < 2) {
            $this->makeRequest($method, $uri, $params);
        }

        if ($this->getErrors()) {
            $this->doError($this->_responseMessage);
        }

        if (empty($this->getResponse())) {
            $this->doError(__('Response is empty.'));
        }

        return $this->getResponse();
    }

    /**
     * @return bool|mixed
     * @throws LocalizedException
     */
    protected function _auth()
    {
        try {
            return $this->_refreshToken();
        } catch (\Exception $e) {
        }

        if (!in_array($this->_responseMessage, $this->_unAuthenticatedResponse)) {
            $this->doError(__('Could not connect to PlentyMarkets server'));
        }

        try {
            return $this->_login();
        } catch (\Exception $e) {
        }

        return false;
    }

    /**
     * @return bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getAccess()
    {
        $data = [
            'uri' => $this->_helper()->getAppUrl(''),
            'usr' => $this->_helper()->getUserName(),
            'pwd' => $this->_helper()->getPassword(),
            'lic' => $this->_helper()->getRegLicense(),
            'dn' => isset($_SERVER['HTTP_HOST']) ? $_SERVER['HTTP_HOST'] : null,
            'ip' => isset($_SERVER['SERVER_ADDR']) ? $_SERVER['SERVER_ADDR'] : null
        ];

        $this->_call(self::POST, $this->_helper()->getAuthUrl(), $data);
        if ($this->getErrors()) {
            $this->getAuthFactory()->setMessage($this->getErrors());
            return false;
        }

        $this->getAuthFactory()->setMessage(__('Credentials have been updated.'));

        return true;
    }

    /**
     * @return bool|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _refreshToken()
    {
        if (!$refreshToken = $this->getAuthFactory()->getRefreshToken()) {
            $this->doError('Refresh token does not exist.');
        }

        $params = [
            'grant_type'    => 'refresh_token',
            'refresh_token' => $refreshToken
        ];

        $response = $this->_call(self::OAUTH, $this->_helper()->getRefreshTokenUrl(), $params);
        return $this->_setAuth($response);
    }

    /**
     * @return bool|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _login()
    {
        $params = [
            'grant_type'    => 'password',
            'username'      => $this->_helper()->getUserName(),
            'password'      => $this->_helper()->getPassword()
        ];

        $response = $this->_call(self::OAUTH, $this->_helper()->getLoginUrl(), $params);

        return $this->_setAuth($response);
    }

    /**
     * @param array $data
     * @return bool|mixed
     */
    protected function _setAuth(array $data)
    {
        if (empty($data)) {
            return false;
        }

        if (isset($data['access_token'])) {
            $this->getAuthFactory()->setAccessToken($data['access_token']);
        }
        if (isset($data['refresh_token'])) {
            $this->getAuthFactory()->setRefreshToken($data['refresh_token']);
        }
        if (isset($data['token_type'])) {
            $this->getAuthFactory()->setTokenType($data['token_type']);
        }
        if (isset($data['expires_in'])) {
            $this->getAuthFactory()->setExpiresIn($data['expires_in']);
        }

        try {
            $this->getAuthFactory()->getResource()->save($this->getAuthFactory());
        } catch (\Exception $e) {
        }

        return $this->getAuthFactory()->getId();
    }

    /**
     * @param $method
     * @param $uri
     * @param array $params
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _call($method, $uri, $params = [])
    {
        $httpVersion = 1.1;
        $this->_request = $params;
        $this->_response =
        $this->_errors =
        $this->_responseHeaders =
        $xPlentyInfo = [];
        $this->_responseCode = null;
        $this->_curl = curl_init();
        $curl_options = [
            CURLOPT_CONNECTTIMEOUT  => $this->_helper()->getApiConnectionTimeout(),
            CURLOPT_RETURNTRANSFER  => true,
            CURLOPT_TIMEOUT         => $this->_helper()->getApiTimeout(),
            CURLOPT_USERAGENT       => $this->_helper()->getAppName(),
            CURLOPT_VERBOSE         => true,
            CURLOPT_HTTP_VERSION    => ($httpVersion == 1.1) ? CURL_HTTP_VERSION_1_1 : CURL_HTTP_VERSION_1_0,
            // CURLOPT_SSLVERSION      => $this->sslVersion
            // CURLOPT_HEADERFUNCTION  => [$this, 'parseHeaders']
        ];

        $splitBody = false;
        if ($method == self::OAUTH) {
            $curlURL = $uri;
            $curl_options += [CURLOPT_HEADER => false];
        } else {
            $curlURL = $uri;
            $curl_options += [
                CURLOPT_HTTPHEADER => [
                    'Accept: application/json',
                    'Content-Type: application/json',
                    'Authorization: '. self::AUTH_BEARER . ' ' . $this->getAuthFactory()->getAccessToken()
                ],
                CURLOPT_HEADER => true
            ];
            $splitBody = true;
        }

        switch ($method) {
            case self::GET :
                $curl_options = $curl_options + [CURLOPT_HTTPGET => true];
                break;
            case self::POST :
                $curl_options = $curl_options + [
                        CURLOPT_POST        => true,
                        CURLOPT_POSTFIELDS  => is_array($params) ? json_encode($params) : $params
                    ];
                break;
            case self::PUT:
                $curl_options = $curl_options + [
                        CURLOPT_CUSTOMREQUEST   => 'PUT',
                        CURLOPT_POSTFIELDS      => is_array($params) ? json_encode($params) : $params
                    ];
                break;
            case self::DELETE :
                $curl_options = $curl_options + [
                        CURLOPT_CUSTOMREQUEST   => 'DELETE',
                        CURLOPT_POSTFIELDS      => $params
                    ];
                break;
            case self::OAUTH :
                $curl_options = $curl_options + [
                        CURLOPT_POST       => true,
                        CURLOPT_POSTFIELDS => http_build_query($params)
                    ];
                break;
            default :
                $this->doError(__('Method "%s" currently not supported', $method));
        }

        $curl_options = $curl_options + [CURLOPT_URL => $curlURL];

        $this->curlOptions($curl_options);

        $response       = curl_exec($this->_curl);
        $request        = curl_getinfo($this->_curl, CURLINFO_HEADER_OUT);
        $header_size        = curl_getinfo($this->_curl, CURLINFO_HEADER_SIZE);
        $this->_responseCode   = curl_getinfo($this->_curl, CURLINFO_HTTP_CODE);
        $responseUrl    = curl_getinfo($this->_curl, CURLINFO_EFFECTIVE_URL);
        $resTotTime     = curl_getinfo($this->_curl, CURLINFO_TOTAL_TIME);
        $info           = curl_getinfo($this->_curl);
        $error          = curl_error($this->_curl);
        $errNo          = curl_errno($this->_curl);
        $this->_close();

        // $info .= $params;

        $this->_responseHeaders['CURLINFO_EFFECTIVE_URL']  = $responseUrl;
        $this->_responseHeaders['CURLINFO_HTTP_CODE']      = $this->_responseCode;
        $this->_responseHeaders['CURLINFO_TOTAL_TIME']     = $resTotTime;

        $headers = explode("\n", $response);
        foreach($headers as $header) {
            $parts = explode(':', $header);
            if (isset($parts[0]) && isset($parts[1])) {
                $xPlentyInfo[trim(strtolower($parts[0]))] = trim($parts[1]);
            }
        }

        $this->_responseHeaders['retry-after'] = isset($xPlentyInfo['retry-after']) ? $xPlentyInfo['retry-after'] : null;
        $this->_responseHeaders['x-plenty-global-long-period-limit'] = isset($xPlentyInfo['x-plenty-global-long-period-limit']) ?
            $xPlentyInfo['x-plenty-global-long-period-limit'] : null;
        $this->_responseHeaders['x-plenty-global-long-period-decay'] = isset($xPlentyInfo['x-plenty-global-long-period-decay']) ?
            $xPlentyInfo['x-plenty-global-long-period-decay'] : null;
        $this->_responseHeaders['x-plenty-global-long-period-calls-left'] = isset($xPlentyInfo['x-plenty-global-long-period-calls-left']) ?
            $xPlentyInfo['x-plenty-global-long-period-calls-left'] : null;
        $this->_responseHeaders['x-plenty-global-short-period-limit'] = isset($xPlentyInfo['x-plenty-global-short-period-limit']) ?
            $xPlentyInfo['x-plenty-global-short-period-limit'] : null;
        $this->_responseHeaders['x-plenty-global-short-period-decay'] = isset($xPlentyInfo['x-plenty-global-short-period-decay']) ?
            $xPlentyInfo['x-plenty-global-short-period-decay'] : null;
        $this->_responseHeaders['x-plenty-global-short-period-calls-left'] = isset($xPlentyInfo['x-plenty-global-short-period-calls-left']) ?
            $xPlentyInfo['x-plenty-global-short-period-calls-left'] : null;

        if ($splitBody) {
            $this->_response = json_decode(substr($response, $header_size), true);
        } else {
            $this->_response = json_decode($response, true);
        }

        if ($this->_responseCode >= 400) {
            $this->_errors = $this->_response;
            $this->doError($this->extractMessage('error'));
        }

        if ($errNo) {
            $this->_errors = $this->_response;
            $this->doError($this->extractMessage('error'));
        }

        return $this->getResponse();
    }

    /**
     * Close connection.
     */
    protected function _close()
    {
        if (is_resource($this->_curl)) {
            curl_close($this->_curl);
        }
        $this->_curl = null;
    }

    /**
     * @param string $messageType
     * @return mixed|null
     */
    protected function extractMessage($messageType = 'success')
    {
        $this->_responseMessage = null;
        if (empty($this->_response)) {
            return $this->_responseMessage;
        }

        if (isset($this->_response['validation_errors'])) {
            return $this->_responseMessage = json_encode($this->_response);
        }

        if (isset($this->_response[$messageType])
            && !is_array($this->_response[$messageType])
            && isset($this->_response['message'])
        ) {
            return $this->_responseMessage = $this->_response['message'];
        }

        if (isset($this->_response[$messageType]['message'])) {
            return $this->_responseMessage = $this->_response[$messageType]['message'];
        }

        if (!is_array($this->_response[$messageType])) {
            return $this->_responseMessage;
        }

        foreach ($this->_response[$messageType] as $response) {
            foreach ($response as $key => $item) {
                if (!is_array($item) && $key == 'message') {
                    $this->_responseMessage = $item;
                    break;
                } else {
                    foreach ($item as $i => $value) {
                        if ($i == 'message') {
                            $this->_responseMessage = $value;
                            break;
                        }
                    }
                }
            }
        }

        return $this->_responseMessage;
    }

    /**
     * @param $string
     * @throws LocalizedException
     */
    protected function doError($string)
    {
        throw new LocalizedException(__($string));
    }

    /**
     * @param $ch
     * @param $data
     * @return int
     * @throws LocalizedException
     */
    protected function parseHeaders($ch, $data)
    {
        if ($this->_headerCount == 0) {
            $line = explode(" ", trim($data), 3);
            if (count($line) != 3) {
                $this->doError("Invalid response line returned from server: " . $data);
            }
            $this->_responseStatus = intval($line[1]);
        } else {
            $name = $value = '';
            $out = explode(": ", trim($data), 2);
            if (count($out) == 2) {
                $name = $out[0];
                $value = $out[1];
            }

            if (strlen($name)) {
                if ("Set-Cookie" == $name) {
                    if (!isset($this->_responseHeaders[$name])) {
                        $this->_responseHeaders[$name] = [];
                    }
                    $this->_responseHeaders[$name][] = $value;
                } else {
                    $this->_responseHeaders[$name] = $value;
                }
            }
        }
        $this->_headerCount++;

        return strlen($data);
    }

    /**
     * Set curl option directly
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    protected function curlOption($name, $value)
    {
        curl_setopt($this->_curl, $name, $value);
    }

    /**
     * Set curl options array directly
     * @param array $arr
     * @return void
     */
    protected function curlOptions($arr)
    {
        curl_setopt_array($this->_curl, $arr);
    }

    /**
     * Set CURL options overrides array
     * @param array $arr
     * @return void
     */
    public function setOptions($arr)
    {
        $this->_curlUserOptions = $arr;
    }

    /**
     * Set curl option
     *
     * @param string $name
     * @param string $value
     * @return void
     */
    public function setOption($name, $value)
    {
        $this->_curlUserOptions[$name] = $value;
    }
}

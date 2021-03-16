<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Rest;

/**
 * Interface ClientInterface
 * @package Plenty\Core\Model\Http
 */
interface ClientInterface
{
    /**
     * HTTP request methods
     */
    const GET                   = 'GET';
    const POST                  = 'POST';
    const PUT                   = 'PUT';
    const DELETE                = 'DELETE';
    const OAUTH                 = 'OAUTH';
    const HEAD                  = 'HEAD';
    const TRACE                 = 'TRACE';
    const OPTIONS               = 'OPTIONS';
    const CONNECT               = 'CONNECT';
    const MERGE                 = 'MERGE';
    const PATCH                 = 'PATCH';

    /**
     * Supported HTTP Authentication methods
     */
    const AUTH_BASIC            = 'basic';
    const AUTH_BEARER           = 'Bearer';
    //const AUTH_DIGEST         = 'digest'; <-- not implemented yet

    /**
     * HTTP protocol versions
     */
    const HTTP_1                = '1.1';
    const HTTP_0                = '1.0';

    /**
     * Content attributes
     */
    const CONTENT_TYPE          = 'Content-Type';
    const CONTENT_LENGTH        = 'Content-Length';

    /**
     * POST data encoding methods
     */
    const ENC_URLENCODED        = 'application/x-www-form-urlencoded';
    const ENC_FORMDATA          = 'multipart/form-data';

    /**
     * Value types for Body key/value pairs
     */
    const VTYPE_SCALAR          = 'SCALAR';
    const VTYPE_FILE            = 'FILE';

    /**
     * Set request timeout
     *
     * @param int $value value in seconds
     * @return void
     */
    public function setTimeout($value);

    /**
     * Set request headers from hash
     *
     * @param array $headers an array of header names as keys and header values as values
     * @return void
     */
    public function setHeaders($headers);

    /**
     * Add header to request
     *
     * @param string $name name of the HTTP header
     * @param string $value value of the HTTP header
     * @return void
     */
    public function addHeader($name, $value);

    /**
     * Remove header from request
     *
     * @param string $name name of the HTTP header
     * @return void
     */
    public function removeHeader($name);

    /**
     * Set login credentials for basic authentication.
     *
     * @param string $login user identity/name
     * @param string $pass user password
     * @return void
     */
    public function setCredentials($login, $pass);

    /**
     * Add cookie to request
     *
     * @param string $name name of the cookie
     * @param string $value value of the cookie
     * @return void
     */
    public function addCookie($name, $value);

    /**
     * Remove cookie from request
     *
     * @param string $name name of the cookie
     * @return void
     */
    public function removeCookie($name);

    /**
     * Set request cookies from hash
     *
     * @param array $cookies an array of cookies with cookie names as keys and cookie values as value
     * @return void
     */
    public function setCookies($cookies);

    /**
     * Remove cookies from request
     *
     * @return void
     */
    public function removeCookies();

    /**
     * Make GET request
     *
     * @param $uri
     * @param $params
     * @return mixed
     */
    public function get($uri, array $params);

    /**
     * Make POST request
     *
     * @param string $uri full uri
     * @param array|string $params POST fields array or string in case of JSON or XML data
     * @return void
     */
    public function post($uri, array $params);

    /**
     * @param $endpoint
     * @param $data
     * @return mixed
     */
    public function put($endpoint, array $data);

    /**
     * @param $endpoint
     * @param $data
     * @return mixed
     */
    public function delete($endpoint, array $data);

    /**
     * Get response headers
     *
     * @return array
     */
    public function getHeaders();

    /**
     * Get response body
     *
     * @return string
     */
    public function getResponse();

    /**
     * Get response status code
     *
     * @return int
     */
    public function getStatus();

    /**
     * Get response cookies (k=>v)
     *
     * @return array
     */
    public function getCookies();

    /**
     * Set additional option
     *
     * @param string $key
     * @param string $value
     * @return void
     */
    public function setOption($key, $value);

    /**
     * Set additional options
     *
     * @param array $arr
     * @return void
     */
    public function setOptions($arr);
}

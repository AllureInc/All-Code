<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Model\Profile\Config\Source\DebugLevel;

/**
 * Class Attribute
 * @package Plenty\Core\Rest
 */
class AbstractClient
{
    /**
     * @var Client
     */
    protected $_api;

    /**
     * @var \Plenty\Core\Model\Profile\Type\AbstractType
     */
    protected $_profileEntity;

    /**
     * @var null
     */
	protected $_helper;

    /**
     * @var Client
     */
	protected $_httpClientFactory;

    /**
     * @var null
     */
	protected $_responseParser;

    /**
     * @var null
     */
	protected $_logLevel;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var array
     */
    protected $_responseFull                = [];

    /**
     * @var
     */
    protected $_responseError;

    /**
     * @var
     */
    protected $_debugLevel;

    /**
     * @var bool
     */
    protected $_isParseResponseToObject     = false;

    /**
     * AbstractClient constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     */
	public function __construct(
	    Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory
    ) {
	    $this->_httpClientFactory = $httpClientFactory;
        $this->_dataCollectionFactory = $dataCollectionFactory;
    }

    /**
     * @return Client
     */
    public function _api()
    {
        return $this->_httpClientFactory;
    }

    /**
     * @return \Plenty\Core\Model\Profile\Type\AbstractType
     */
    public function getProfileEntity()
    {
        return $this->_profileEntity;
    }

    /**
     * @param $profileEntity
     * @return $this
     */
    public function setProfileEntity($profileEntity)
    {
        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_api()->getErrors();
    }

    /**
     * @return null
     */
    public function getLogLevel()
    {
        return $this->_logLevel;
    }

    /**
     * Logs Rest response inc. errors
     *
     * @param array $level
     * @param $method
     *
     * @return array|bool
     */
    public function getResponseFull($level, $method)
    {
        $this->_responseFull = [];
        $errors = $this->_api()->getErrors();
        $response = $this->_api()->getResponse();
        $headers = $this->_api()->getHeaders();
        $cookies = $this->_api()->getCookies();
        $cookiesFull = $this->_api()->getCookiesFull();
        $status = $this->_api()->getStatus();
        $request = $this->_api()->getRequest();

        if (in_array(DebugLevel::API_ERROR, $level)) {
            $errors ? $this->_responseFull[$method]['error'] = $errors : null;
        }

        if (in_array(DebugLevel::API_RESPONSE_HEADER, $level)) {
            $headers ? $this->_responseFull[$method]['headers'] = $headers : ['Empty response'];
        }

        if (in_array(DebugLevel::API_RESPONSE_BODY_FULL, $level)) {
            $this->_responseFull[$method]['response_body_full'] = json_encode($response);
        } elseif (in_array(DebugLevel::API_RESPONSE_BODY_SHORT, $level)) {
            if (!isset($response['entries'])) {
                $this->_responseFull[$method]['response_body_short'] = json_encode($response);
            } else {
                isset($response['page'])
                    ? $this->_responseFull[$method]['response_body_short']['page'] = $response['page']
                    : null;
                isset($response['totalsCount'])
                    ? $this->_responseFull[$method]['response_body_short']['totalsCount'] = $response['totalsCount']
                    : null;
                isset($response['isLastPage'])
                    ? $this->_responseFull[$method]['response_body_short']['isLastPage'] = $response['isLastPage']
                    : null;
                isset($response['lastPageNumber'])
                    ? $this->_responseFull[$method]['response_body_short']['lastPageNumber'] = $response['lastPageNumber']
                    : null;
                isset($response['pagfirstOnPagee'])
                    ? $this->_responseFull[$method]['response_body_short']['firstOnPage'] = $response['firstOnPage']
                    : null;
                isset($response['lastOnPage'])
                    ? $this->_responseFull[$method]['response_body_short']['lastOnPage'] = $response['lastOnPage']
                    : null;
                isset($response['itemsPerPage'])
                    ? $this->_responseFull[$method]['response_body_short']['itemsPerPage'] = $response['itemsPerPage']
                    : null;
                $ids = [];
                foreach ($response['entries'] as $entry) {
                    if (isset($entry['id'])) {
                        $ids[] = $entry['id'];
                    } elseif (isset($entry['variationId'])) {
                        $ids[] = $entry['variationId'];
                    }
                }
                $this->_responseFull[$method]['response_body_short']['entries'] = implode(',', $ids);
            }
        }

        if (in_array(DebugLevel::API_REQUEST_BODY, $level)) {
            $request ? $this->_responseFull[$method]['request_body'] = json_encode($request) : null;
        }

        return $this->_responseFull;
    }

    /**
     * @param bool $bool
     * @return $this
     */
    public function setIsParseResponseToObject($bool = true)
    {
        $this->_isParseResponseToObject = $bool;
        return $this;
    }

    /**
     * @return mixed
     */
    public function getIsParseResponseToObject()
    {
        return $this->_isParseResponseToObject;
    }
}
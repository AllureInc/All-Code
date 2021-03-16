<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Rest\AbstractClient;
use Plenty\Core\Rest\Client;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class AbstractItem
 * @package Plenty\Item\Rest
 */
class AbstractItem extends AbstractClient
{
    /**
     * @var \Plenty\Core\Model\Profile\Type\AbstractType
     */
    protected $_profileEntity;

    /**
     * @var null
     */
	protected $_helper;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var null
     */
	protected $_responseParser;

    /**
     * @var null
     */
	protected $_logLevel;

    /**
     * @var
     */
    protected $_debugLevel;

    /**
     * AbstractItem constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger
    ) {
        $this->_helper = $helper;
        $this->_logger = $logger;
        parent::__construct($httpClientFactory, $dataCollectionFactory);
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

    public function getLogLevel()
    {
        if (empty($this->_logLevel)) {
            $this->_logLevel = $this->_helper()->getDebugLevel();
        }
        return $this->_logLevel;
    }

    /**
     * @param array $payloads
     * @return array|bool
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getBatchProcess(array $payloads)
    {
        if (empty($payloads)) {
            return false;
        }

        try {
            $response = $this->_api()->post($this->_helper()->getBatchUrl(), $payloads);
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @return Helper
     */
    protected function _helper()
    {
        return $this->_helper;
    }

    /**
     * @return Logger
     */
    protected function _logger()
    {
        return $this->_logger;
    }

    /**
     * @return array
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _getDebugLevel()
    {
        return $this->_helper()->getDebugLevel();
    }

    /**
     * @param $message
     * @param array $context
     * @param bool $forceDebug
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logResponse($message, array $context = [], $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return $this;
        }

        $this->_logger()->debug($message, $context);
        return $this;
    }

    /**
     * @param $message
     * @param $method
     * @param bool $forceDebug
     * @return $this
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logError($message, $method, $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return $this;
        }

        $this->_logger()->error($method, is_array($message) ? $message : [$message]);
        return $this;
    }
}
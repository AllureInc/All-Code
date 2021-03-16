<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Rest;

use Magento\Framework\Data\CollectionFactory;
use Plenty\Core\Model\Logger;
use Plenty\Core\Helper\Data as Helper;

/**
 * Class AbstractCore
 * @package Plenty\Core\Rest
 */
class AbstractCore extends AbstractClient
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
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logResponse($message, array $context = [], $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return false;
        }

        $this->_logger()->debug($message, $context);
        return $this;
    }

    /**
     * @param $message
     * @param $method
     * @param bool $forceDebug
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    protected function _logError($message, $method, $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return false;
        }

        $this->_logger()->error([$method => $message]);
        return $this;
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

}
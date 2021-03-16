<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

use Plenty\Order\Model\Logger;
use Plenty\Order\Helper\Data as Helper;

/**
 * Class ImportExportAbstract
 * @package Plenty\Order\Model
 * @since 0.1.0
 */
class AbstractManagement extends DataObject
{
    /**
     * @var null
     */
    protected $_api;

    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var bool
     */
    protected $_behaviourUpdate;

    /**
     * @var array
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_exportResponse;

    /**
     * @var array
     */
    protected $_error;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * AbstractManagement constructor.
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_dateTime = $dateTime;
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($data);
    }

    /**
     * Search multi-dimensional array
     *
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
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        $key
            ? $this->_response[$key] = $data
            : $this->_response = $data;
        return $this;
    }

    /**
     * @param array|string $data
     * @param null $key
     * @return $this
     */
    public function addResponse($data, $key = null)
    {
        $key
            ? $this->_response[$key][] = $data
            : $this->_response[] = $data;
        return $this;
    }

    /**
     * @return array
     */
    public function getExportResponse()
    {
        return $this->_exportResponse;
    }

    /**
     * @param $message
     * @param array $context
     * @param bool $forceDebug
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _logResponse($message, array $context = [], $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper()->isDebugOn()) {
            return false;
        }

        $this->_logger()->debug($message, $context);
        return $this;
    }


}
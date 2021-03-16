<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\DataObject;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Plenty\Core\Api\Data\ProfileInterface;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Item\Model\Logger;
use Plenty\Item\Helper;

/**
 * Class AbstractManagement
 * @package Plenty\Item\Profile
 * @since 0.1.1
 */
class AbstractManagement extends DataObject
{
    /**
     * @var ProfileInterface
     */
    protected $_profile;

    /**
     * @var null
     */
    protected $_profileEntity;

    /**
     * @var HistoryInterface
     */
    protected $_profileHistory;

    /**
     * @var null
     */
    protected $_client;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var Helper\Data
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_response;

    /**
     * @var array
     */
    protected $_error;

    /**
     * @var Logger
     */
    protected $_logger;

    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * AbstractManagement constructor.
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_dateTime = $dateTime;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($data);
    }

    /**
     * @return ProfileInterface
     * @throws \Exception
     */
    public function getProfile() : ProfileInterface
    {
        if (!$this->_profile) {
            throw new \Exception(__('Profile is not set.'));
        }
        return $this->_profile;
    }

    /**
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile(ProfileInterface $profile)
    {
        $this->_profile = $profile;
        return $this;
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
     * @param null $key
     * @return array|mixed
     */
    public function getErrors($key = null)
    {
        return $key
            ? $this->_error[$key]
            : $this->_error;
    }

    /**
     * @param null $key
     * @return array|mixed
     */
    public function getResponse($key = null)
    {
        return $key
            ? $this->_response[$key]
            : $this->_response;
    }

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        if ($key) {
            $this->_response[$key] = $data;
        } else {
            $this->_response = $data;
        }

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
     * @param $message
     * @param array $context
     * @param bool $forceDebug
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function _logResponse($message, array $context = [], $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper->isDebugOn()) {
            return false;
        }

        $this->_logger->debug($message, $context);
        return $this;
    }
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model;

use Magento\Framework\Model\AbstractModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;

use Plenty\Item\Helper\Data as Helper;

/**
 * Class ImportExportAbstract
 * @package Plenty\Item\Model
 *
 * @method boolean getBehaviourUpdate()
 * @method ImportExportAbstract setBehaviourUpdate(boolean $value)
 * @method string getBehaviour()
 * @method ImportExportAbstract setBehaviour(string $value)
 */
class ImportExportAbstract extends AbstractModel
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
     * ImportExportAbstract constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_dateTime = $dateTime;
        $this->_helper = $helper;
        $this->_logger = $logger;
        $this->_serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
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
        if ($columnId) {
            return array_search($needle, array_column($haystack, $columnName, $columnId));
        }

        return array_search($needle, array_column($haystack, $columnName));
    }

    /**
     * @return Helper
     */
    protected function _helper()
    {
        return $this->_helper;
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

        $this->_logger->debug($message, $context);
        return $this;
    }
}
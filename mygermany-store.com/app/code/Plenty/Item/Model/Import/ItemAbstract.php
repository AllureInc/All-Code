<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\Model\AbstractExtensibleModel;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\Api\ExtensionAttributesFactory;
use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Item\Helper\Data as Helper;

/**
 * Class ItemAbstract
 * @package Plenty\Item\Model\Import
 *
 * @method boolean getBehaviourUpdate()
 * @method ItemAbstract setBehaviourUpdate(boolean $value)
 * @method string getBehaviour()
 * @method ItemAbstract setBehaviour(string $value)
 */
class ItemAbstract extends AbstractExtensibleModel
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

    public function __construct(
        Context $context,
        Registry $registry,
        ExtensionAttributesFactory $extensionFactory,
        AttributeValueFactory $customAttributeFactory,
        DateTime $dateTime,
        Helper $helper,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dateTime = $dateTime;
        $this->_helper = $helper;
        parent::__construct($context, $registry, $extensionFactory, $customAttributeFactory, $resource, $resourceCollection, $data);
    }

    /**
     * @return Helper
     */
    protected function _helper()
    {
        return $this->_helper;
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
}
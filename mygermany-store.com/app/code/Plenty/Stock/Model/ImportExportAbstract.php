<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

/**
 * Class ImportExportAbstract
 * @package Plenty\Stock\Model
 *
 * @method boolean getBehaviourUpdate()
 * @method ImportExportAbstract setBehaviourUpdate(boolean $value)
 * @method string getBehaviour()
 * @method ImportExportAbstract setBehaviour(string $value)
 */
class ImportExportAbstract extends \Magento\Framework\Model\AbstractModel
{
    /**
     * @var null
     */
    protected $_api                 = null;

    /**
     * @var null
     */
    protected $_coreHelper          = null;

    /**
     * @var bool
     */
    protected $_behaviourUpdate     = false;

    /**
     * @var array
     */
    protected $_response            = [];

    /**
     * @var DateTime
     */
    protected $_dateTime;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        DateTime $dateTime,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_dateTime = $dateTime;
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
    }
}
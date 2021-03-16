<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Filter;

use Magento\Backend\Block\Context;
use Magento\Framework\DB\Helper;
use Magento\Backend\Block\Widget\Grid\Column\Filter\Select;
use Plenty\Core\Model\Source\Status as StatusSource;

/**
 * Class Status
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Filter
 */
class Status extends Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @var StatusSource
     */
    protected $_statusSource;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = array_merge(
            [null => null],
            $this->_statusSource->toOptionHashScheduleStatuses()
        );
        parent::_construct();
    }

    /**
     * Status constructor.
     * @param Context $context
     * @param Helper $resourceHelper
     * @param StatusSource $statusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        Helper $resourceHelper,
        StatusSource $statusSource,
        array $data = []
    ) {
        $this->_statusSource = $statusSource;
        parent::__construct($context, $resourceHelper, $data);
    }

    /**
     * @return array
     */
    protected function _getOptions()
    {
        $options = [];
        foreach (self::$_statuses as $status => $label) {
            $options[] = ['value' => $status, 'label' => __($label)];
        }

        return $options;
    }

    /**
     * @return array|null
     */
    public function getCondition()
    {
        return $this->getValue() === null
            ? null
            : ['eq' => $this->getValue()];
    }
}

<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product\Grid\Filter;

use Plenty\Core\Model\Source\Status as HistoryStatus;

/**
 * Class Status
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product\Grid\Filter
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Filter\Select
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * @return void
     */
    protected function _construct()
    {
        self::$_statuses = [
            null => null,
            HistoryStatus::PENDING => __('Pending'),
            HistoryStatus::COMPLETE => __('Complete'),
            HistoryStatus::RUNNING => __('Processing'),
            HistoryStatus::ERROR => __('Error'),
            HistoryStatus::STOPPED => __('Stopped')
        ];
        parent::_construct();
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
        return $this->getValue() === null ? null : ['eq' => $this->getValue()];
    }
}

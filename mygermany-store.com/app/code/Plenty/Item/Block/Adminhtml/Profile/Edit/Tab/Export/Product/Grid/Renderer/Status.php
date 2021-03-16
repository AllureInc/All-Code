<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product\Grid\Renderer;

use Plenty\Core\Model\Source\Status as HistoryStatus;

/**
 * Class Status
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\History\Grid\Renderer
 */
class Status extends \Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer
{
    /**
     * @var array
     */
    protected static $_statuses;

    /**
     * Constructor for Grid Renderer Status
     *
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
     * @param \Magento\Framework\DataObject $row
     * @return \Magento\Framework\Phrase
     */
    public function render(\Magento\Framework\DataObject $row)
    {
        return __($this->getStatus($row->getStatus()));
    }

    /**
     * @param string $status
     * @return \Magento\Framework\Phrase
     */
    public static function getStatus($status)
    {
        if (!isset(self::$_statuses[$status])) {
            return __('Unknown');
        }

        return self::$_statuses[$status];
    }
}

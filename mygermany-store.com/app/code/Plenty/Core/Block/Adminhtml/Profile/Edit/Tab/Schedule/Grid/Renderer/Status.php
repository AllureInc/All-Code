<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Renderer;

use Magento\Backend\Block\Widget\Grid\Column\Renderer\AbstractRenderer;
use Magento\Backend\Block\Context;
use Plenty\Core\Model\Source\Status as StatusSource;

/**
 * Class Status
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Renderer
 */
class Status extends AbstractRenderer
{
    /**
     * @var StatusSource
     */
    protected $_statusSource;

    /**
     * Status constructor.
     * @param Context $context
     * @param StatusSource $statusSource
     * @param array $data
     */
    public function __construct(
        Context $context,
        StatusSource $statusSource,
        array $data = []
    ) {
        $this->_statusSource = $statusSource;
        parent::__construct($context, $data);
    }

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
        self::$_statuses = array_merge(
            [null => null],
            $this->_statusSource->toOptionHashScheduleStatuses()
        );

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

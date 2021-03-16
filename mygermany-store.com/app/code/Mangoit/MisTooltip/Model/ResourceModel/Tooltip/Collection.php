<?php
namespace Mangoit\MisTooltip\Model\ResourceModel\Tooltip;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = 'id';
    protected $_eventPrefix = 'mis_tooltip';
    protected $_eventObject = 'mis_tooltip';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Mangoit\MisTooltip\Model\Tooltip', 'Mangoit\MisTooltip\Model\ResourceModel\Tooltip');
    }

}
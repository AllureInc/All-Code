<?php
namespace Mangoit\MisTooltip\Model\ResourceModel;


class Tooltip extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context
    )
    {
        parent::__construct($context);
    }
    
    protected function _construct()
    {
        $this->_init('mis_tooltip', 'id');
    }
    
}
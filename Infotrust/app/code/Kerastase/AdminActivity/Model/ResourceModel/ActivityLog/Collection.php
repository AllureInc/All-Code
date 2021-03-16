<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Model\ResourceModel\ActivityLog;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Kerastase\AdminActivity\Model\ResourceModel\ActivityLog
 */
class Collection extends AbstractCollection
{
    /**
     * Define resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init(
            'Kerastase\AdminActivity\Model\ActivityLog',
            'Kerastase\AdminActivity\Model\ResourceModel\ActivityLog'
        );
    }
}

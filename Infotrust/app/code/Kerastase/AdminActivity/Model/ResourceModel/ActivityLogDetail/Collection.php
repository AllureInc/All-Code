<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Model\ResourceModel\ActivityLogDetail;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Kerastase\AdminActivity\Model\ResourceModel\ActivityLogDetail
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
            'Kerastase\AdminActivity\Model\ActivityLogDetail',
            'Kerastase\AdminActivity\Model\ResourceModel\ActivityLogDetail'
        );
    }
}

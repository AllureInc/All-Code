<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Model\ResourceModel\Login;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;

/**
 * Class Collection
 * @package Kerastase\AdminActivity\Model\ResourceModel\Login
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
            'Kerastase\AdminActivity\Model\Login',
            'Kerastase\AdminActivity\Model\ResourceModel\Login'
        );
    }
}

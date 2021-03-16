<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class Login
 * @package Kerastase\Activity\Model
 */
class Login extends AbstractModel
{
    /**
     * @var string
     */
    const LOGIN_ACTIVITY_ID = 'entity_id'; // We define the id field name

    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('Kerastase\AdminActivity\Model\ResourceModel\Login');
    }
}

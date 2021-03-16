<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Model;

use \Magento\Framework\Model\AbstractModel;

/**
 * Class Activity
 * @package Kerastase\Activity\Model
 */
class ActivityLog extends AbstractModel
{
    /**
     * @var string
     */
    const ACTIVITYLOG_ID = 'entity_id'; // We define the id fieldname

    /**
     * Initialize resource model
     * @return void
     */
    public function _construct()
    {
        $this->_init('Kerastase\AdminActivity\Model\ResourceModel\ActivityLog');
    }
}

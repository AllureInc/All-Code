<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Collection class
 */
namespace Cnnb\WhatsappApi\Model\ResourceModel\AllowedDigits;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var $_idFieldName
     */
    protected $_idFieldName = 'id';

    /**
     * @var $_idFieldName
     */
    protected $_eventPrefix = 'config_changed_tbl';

    /**
     * @var $_eventObject
     */
    protected $_eventObject = 'config_changed_tbl';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cnnb\WhatsappApi\Model\AllowedDigits', 'Cnnb\WhatsappApi\Model\ResourceModel\AllowedDigits');
    }
}

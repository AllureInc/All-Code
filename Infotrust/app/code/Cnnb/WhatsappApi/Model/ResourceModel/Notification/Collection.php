<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Collection class
 */
namespace Cnnb\WhatsappApi\Model\ResourceModel\Notification;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var $_idFieldName
     */
    protected $_idFieldName = 'id';

    /**
     * @var $_idFieldName
     */
    protected $_eventPrefix = 'whatsapp_notification';

    /**
     * @var $_eventObject
     */
    protected $_eventObject = 'whatsapp_notification';

    /**
     * Define resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Cnnb\WhatsappApi\Model\Notification', 'Cnnb\WhatsappApi\Model\ResourceModel\Notification');
    }
}

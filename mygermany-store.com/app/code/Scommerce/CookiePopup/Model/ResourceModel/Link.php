<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\ResourceModel;

use Scommerce\CookiePopup\Api\Data\LinkInterface;

/**
 * Class Link
 * @package Scommerce\CookiePopup\Model\ResourceModel
 */
class Link extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /** @var \Magento\Store\Model\StoreManagerInterface */
    private $storeManager;

    /** @var \Magento\Framework\Stdlib\DateTime\DateTime */
    private $date;

    /**
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param string $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        $connectionName = null
    ) {
        $this->storeManager  = $storeManager;
        $this->date          = $date;
        parent::__construct($context, $connectionName);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init(LinkInterface::TABLE, LinkInterface::LINK_ID);
        //$this->_isPkAutoIncrement = false;
    }

    /**
     * Initialize unique fields
     * Main table unique keys field names
     * could array(
     *   array('field' => 'db_field_name1', 'title' => 'Field 1 should be unique')
     *   array('field' => 'db_field_name2', 'title' => 'Field 2 should be unique')
     *   array(
     *      'field' => array('db_field_name3', 'db_field_name3'),
     *      'title' => 'Field 3 and Field 4 combination should be unique'
     *   )
     * )
     * or string 'my_field_name' - will be autoconverted to
     *      array( array( 'field' => 'my_field_name', 'title' => 'my_field_name' ) )
     *
     * @return $this
     */
    protected function _initUniqueFields()
    {
        $this->_uniqueFields = [
            [
                'field' => [LinkInterface::CUSTOMER_ID, LinkInterface::STORE_ID, LinkInterface::CHOICE_ID],
                'title' => 'Customer, store and choice combination should be unique'
            ]
        ];

        return $this;
    }

    /**
     * Before save callback
     *
     * @param \Magento\Framework\Model\AbstractModel|LinkInterface $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb|$this
     */
    public function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        $object->setUpdatedAt($this->date->gmtDate());
        return parent::_beforeSave($object);
    }

    /**
     * Load link by specified params
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @param int $customerId
     * @param int $storeId
     * @param int $choiceId
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function loadByCompound(\Magento\Framework\Model\AbstractModel $object, $customerId, $storeId, $choiceId)
    {
        $select = $this->getConnection()->select()->from($this->getMainTable())
            ->where('customer_id =:customer_id')
            ->where('store_id = :store_id')
            ->where('choice_id = :choice_id')
        ;
        $bind = [':store_id' => $storeId, ':customer_id' => $customerId, ':choice_id' => $choiceId];
        $data = $this->getConnection()->fetchRow($select, $bind);
        if ($data) {
            $object->setData($data);
        }
        $this->unserializeFields($object);
        $this->_afterLoad($object);
        return $this;
    }
}

<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile;

/**
 * Class Config
 * @package Plenty\Core\Model\ResourceModel\Profile
 */
class Config extends \Magento\Framework\Model\ResourceModel\Db\AbstractDb
{
    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init('plenty_core_profile_config', 'entity_id');
    }

    /**
     * Convert array to comma separated value
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return \Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeSave(\Magento\Framework\Model\AbstractModel $object)
    {
        if (!$object->getId()) {
            $this->_checkUnique($object);
        }

        if (is_array($object->getValue())) {
            $object->setValue(join(',', $object->getValue()));
        }
        return parent::_beforeSave($object);
    }

    /**
     * Validate unique configuration data before save
     * Set id to object if exists configuration instead of throw exception
     *
     * @param \Magento\Framework\Model\AbstractModel $object
     * @return $this|\Magento\Framework\Model\ResourceModel\Db\AbstractDb
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _checkUnique(\Magento\Framework\Model\AbstractModel $object)
    {
        $select = $this->getConnection()->select()->from(
            $this->getMainTable(),
            [$this->getIdFieldName()]
        )->where(
            'scope = :scope'
        )->where(
            'scope_id = :scope_id'
        )->where(
            'path = :path'
        );
        $bind = [
            'scope' => $object->getScope(),
            'scope_id' => $object->getScopeId(),
            'path' => $object->getPath(),
        ];

        $configId = $this->getConnection()->fetchOne($select, $bind);
        if ($configId) {
            $object->setId($configId);
        }

        return $this;
    }

    /**
     * Clear Scope data
     *
     * @param $scopeCode
     * @param $scopeIds
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function clearScopeData($scopeCode, $scopeIds)
    {
        if (!is_array($scopeIds)) {
            $scopeIds = [$scopeIds];
        }
        $this->getConnection()->delete(
            $this->getMainTable(),
            ['scope = ?' => $scopeCode, 'scope_id IN (?)' => $scopeIds]
        );
    }

}
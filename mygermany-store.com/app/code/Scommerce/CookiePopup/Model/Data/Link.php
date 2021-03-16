<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\Data;

use Magento\Framework\DataObject\IdentityInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;

/**
 * Class Link
 * @package Scommerce\CookiePopup\Model\Data
 *
 * @method \Scommerce\CookiePopup\Model\ResourceModel\Link getResource()
 */
class Link extends \Magento\Framework\Model\AbstractModel implements LinkInterface, IdentityInterface
{
    const CACHE_TAG = 'scommerce_cookie_popup_link'; // Cache tag

    /** @var string Prefix of model events names */
    protected $_eventPrefix = 'scommerce_cookie_popup_link';

    /** @var string Parameter name in event. Use $observer->getEvent()->getObject() in observe method */
    protected $_eventObject = 'scommerce_cookie_popup_link';

    /** @var string Name of object id field */
    protected $_idFieldName = self::LINK_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Scommerce\CookiePopup\Model\ResourceModel\Link::class);
        $this->setIdFieldName(self::LINK_ID);
    }

    /**
     * Load by compound key
     *
     * @param int $customerId
     * @param int $storeId
     * @param int $choiceId
     * @return $this
     * @throws \Exception
     */
    public function loadByCompound($customerId, $storeId, $choiceId)
    {
        $modelId = [
            LinkInterface::CUSTOMER_ID  => $customerId,
            LinkInterface::STORE_ID     => $storeId,
            LinkInterface::CHOICE_ID    => $choiceId,
        ];
        $field = sprintf('%s_%s_%s', $customerId, $storeId, $choiceId);
        $this->_beforeLoad($modelId, $field);
        $this->getResource()->loadByCompound($this, $customerId, $storeId, $choiceId);
        $this->_afterLoad();
        $this->setOrigData();
        $this->_hasDataChanges = false;
        $this->updateStoredData();
        return $this;
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getCustomerId() . '_' . $this->getStoreId() . '_' . $this->getChoiceId()];
    }

    /**
     * @inheritdoc
     */
    public function getLinkId()
    {
        return $this->_getData(self::LINK_ID);
    }

    /**
     * @inheritdoc
     */
    public function setLinkId($value)
    {
        return $this->setData(self::LINK_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getCustomerId()
    {
        return $this->_getData(self::CUSTOMER_ID);
    }

    /**
     * @inheritdoc
     */
    public function setCustomerId($value)
    {
        return $this->setData(self::CUSTOMER_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getChoiceId()
    {
        return $this->_getData(self::CHOICE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setChoiceId($value)
    {
        return $this->setData(self::CHOICE_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getStoreId()
    {
        return $this->_getData(self::STORE_ID);
    }

    /**
     * @inheritdoc
     */
    public function setStoreId($value)
    {
        return $this->setData(self::STORE_ID, $value);
    }

    /**
     * @inheritdoc
     */
    public function getStatus()
    {
        return $this->_getData(self::STATUS);
    }

    /**
     * @inheritdoc
     */
    public function setStatus($value)
    {
        return $this->setData(self::STATUS, $value);
    }

    /**
     * @inheritdoc
     */
    public function getCreatedAt()
    {
        return $this->_getData(self::CREATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setCreatedAt($value)
    {
        return $this->setData(self::CREATED_AT, $value);
    }

    /**
     * @inheritdoc
     */
    public function getUpdatedAt()
    {
        return $this->_getData(self::UPDATED_AT);
    }

    /**
     * @inheritdoc
     */
    public function setUpdatedAt($value)
    {
        return $this->setData(self::UPDATED_AT, $value);
    }

    /**
     * Synchronize object's stored data with the actual data
     *
     * @return $this
     */
    private function updateStoredData()
    {
        $this->storedData = isset($this->_data) ? $this->_data : [];
        return $this;
    }
}

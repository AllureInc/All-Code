<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\Data;

use Magento\Framework\DataObject\IdentityInterface;
use Scommerce\CookiePopup\Api\Data\ChoiceInterface;

/**
 * Class Choice
 * @package Scommerce\CookiePopup\Model\Data
 */
class Choice extends \Magento\Framework\Model\AbstractModel implements ChoiceInterface, IdentityInterface
{
    const CACHE_TAG = 'scommerce_cookie_popup_choice'; // Cache tag

    /** @var string Prefix of model events names */
    protected $_eventPrefix = 'scommerce_cookie_popup_choice';

    /** @var string Parameter name in event. Use $observer->getEvent()->getObject() in observe method */
    protected $_eventObject = 'scommerce_cookie_popup_choice';

    /** @var string Name of object id field */
    protected $_idFieldName = self::CHOICE_ID;

    protected $_usedByList = null;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Scommerce\CookiePopup\Model\ResourceModel\Choice');
        $this->setIdFieldName(self::CHOICE_ID);
    }

    /**
     * @inheritdoc
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
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
    public function getChoiceName()
    {
        return $this->_getData(self::CHOICE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setChoiceName($value)
    {
        return $this->setData(self::CHOICE_NAME, $value);
    }

    /**
     * @inheritdoc
     */
    public function getChoiceDescription()
    {
        return $this->_getData(self::CHOICE_DESCRIPTION);
    }

    /**
     * @inheritdoc
     */
    public function setChoiceDescription($value)
    {
        return $this->setData(self::CHOICE_DESCRIPTION, $value);
    }

    /**
     * @inheritdoc
     */
    public function getCookieName()
    {
        return $this->_getData(self::COOKIE_NAME);
    }

    /**
     * @inheritdoc
     */
    public function setCookieName($value)
    {
        return $this->setData(self::COOKIE_NAME, $value);
    }

    /**
     * @inheritdoc
     */
    public function getList()
    {
        return $this->_getData(self::CHOICE_LIST);
    }

    /**
     * @inheritdoc
     */
    public function setList($value)
    {
        return $this->setData(self::CHOICE_LIST, $value);
    }

    /**
     * @inheritdoc
     */
    public function getIsRequired()
    {
        return $this->_getData(self::REQUIRED);
    }

    /**
     * @inheritdoc
     */
    public function setIsRequired($value)
    {
        $value = (bool) $value ? 1 : 0;
        return $this->setData(self::REQUIRED, $value);
    }

    /**
     * @inheritdoc
     */
    public function isRequired()
    {
        return (bool) $this->getIsRequired();
    }

    /**
     * @inheritdoc
     */
    public function getDefaultState()
    {
        return $this->_getData(self::DEFAULT_STATE);
    }

    /**  */
    public function setDefaultState($value)
    {
        $value = (bool) $value ? 1 : 0;
        return $this->setData(self::DEFAULT_STATE, $value);
    }

    /**
     * Receive choice store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * @return mixed | array
     */
    public function getUsedByList()
    {
        if ($this->_usedByList == null) {
            $this->_usedByList = array();
            if ($this->getList()) {
                $this->_usedByList = explode("\n", $this->getList());
            }
        }
        return $this->_usedByList;
    }

    /** @inheritdoc */
    public function getStoreId()
    {
        return $this->_getData('store_id');
    }

    /** @inheritdoc */
    public function getStatus()
    {
        return $this->_getData('status');
    }
}

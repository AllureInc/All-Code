<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model;

use Webkul\Walletsystem\Api\Data\WalletCreditRuleInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Walletcreditrules extends AbstractModel implements WalletCreditRuleInterface, IdentityInterface
{
   
    const CACHE_TAG = 'walletsystem_walletcreditrules';
    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_walletcreditrules';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_walletcreditrules';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Walletsystem\Model\ResourceModel\Walletcreditrules');
    }
    /**
     * Return unique ID(s) for each object in system
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getEntityId()];
    }
    /**
     * Get ID
     *
     * @return int|null
     */
    public function getEntityId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    
    public function setEntityId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    public function setAmount($amount)
    {
        return $this->setData(self::AMOUNT, $amount);
    }
    public function getAmount()
    {
        return $this->getData(self::AMOUNT);
    }

    public function setProductIds($productIds)
    {
        return $this->setData(self::PRODUCT_IDS, $productIds);
    }
    public function getProductIds()
    {
        return $this->getData(self::PRODUCT_IDS);
    }
    public function setBasedOn($basedOn)
    {
        return $this->setData(self::BASED_ON, $basedOn);
    }

    public function getBasedOn()
    {
        return $this->getData(self::BASED_ON);
    }

    public function setMinimumAmount($minimimAmount)
    {
        return $this->setData(self::MINIMUM_AMOUNT, $minimimAmount);
    }
    public function getMinimumAmount()
    {
        return $this->getData(self::MINIMUM_AMOUNT);
    }

    public function setStartDate($startDate)
    {
        return $this->setData(self::START_DATE, $startDate);
    }
    public function getStartDate()
    {
        return $this->getData(self::START_DATE);
    }

    public function setEndDate($endDate)
    {
        return $this->setData(self::END_DATE, $endDate);
    }
    public function getEndDate()
    {
        return $this->getData(self::END_DATE);
    }

    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }
}

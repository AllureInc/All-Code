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

use Webkul\Walletsystem\Api\Data\WalletCreditAmountInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Walletcreditamount extends AbstractModel implements WalletCreditAmountInterface, IdentityInterface
{
   
    const CACHE_TAG = 'walletsystem_walletcreditamount';
    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_walletcreditamount';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_walletcreditamount';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Walletsystem\Model\ResourceModel\Walletcreditamount');
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
    public function setOrderId($orderId)
    {
        return $this->setData(self::ORDER_ID, $orderId);
    }
    public function getOrderId()
    {
        return $this->getData(self::ORDER_ID);
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

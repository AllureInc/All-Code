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

use Webkul\Walletsystem\Api\Data\WallettransactionInterface;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

class Wallettransaction extends AbstractModel implements WallettransactionInterface, IdentityInterface
{
    const CACHE_TAG = 'walletsystem_wallettransaction';
    const Order_PLACE_TYPE = 0;
    const CASH_BACK_TYPE = 1;
    const REFUND_TYPE = 2;
    const ADMIN_TRANSFER_TYPE = 3;
    const CUSTOMER_TRANSFER_TYPE = 4;

    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_wallettransaction';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_wallettransaction';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Walletsystem\Model\ResourceModel\Wallettransaction');
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
}

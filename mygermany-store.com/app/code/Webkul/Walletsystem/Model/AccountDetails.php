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

use Webkul\Walletsystem\Api\Data\AccountDetailsInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class AccountDetails extends AbstractModel implements AccountDetailsInterface, IdentityInterface
{
    const CACHE_TAG = 'wk_ws_wallet_account_details';
    /**
     * @var string
     */
    protected $_cacheTag = 'wk_ws_wallet_account_details';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'wk_ws_wallet_account_details';
    /**
     * Initialize resource model
     *
     * @return void
     */

    protected function _construct()
    {
        $this->_init('Webkul\Walletsystem\Model\ResourceModel\AccountDetails');
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

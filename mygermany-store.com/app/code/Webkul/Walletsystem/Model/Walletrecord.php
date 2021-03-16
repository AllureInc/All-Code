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

use Webkul\Walletsystem\Api\Data\WalletrecordInterface;
use Magento\Framework\DataObject\IdentityInterface;
use \Magento\Framework\Model\AbstractModel;

class Walletrecord extends AbstractModel implements WalletrecordInterface, IdentityInterface
{
    const CACHE_TAG = 'walletsystem_walletrecord';
    /**
     * @var string
     */
    protected $_cacheTag = 'walletsystem_walletrecord';
    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'walletsystem_walletrecord';
    /**
     * Initialize resource model
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\Walletsystem\Model\ResourceModel\Walletrecord');
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

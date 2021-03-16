<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Model;

use Webkul\MpSellerVacation\Api\Data\VacationInterface;
use Magento\Framework\DataObject\IdentityInterface;

/**
 * SellerVacation Model.
 *
 * @method \Webkul\MpSellerVacation\Model\ResourceModel\Vacation _getResource()
 * @method \Webkul\MpSellerVacation\Model\ResourceModel\Vacation getResource()
 */
class Vacation extends \Magento\Framework\Model\AbstractModel implements VacationInterface, IdentityInterface
{
    /**
     * No route page id.
     */
    const NOROUTE_ENTITY_ID = 'no-route';

    /**#@+
     * Feedback's Statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**
     * Marketplace SellerVacation cache tag.
     */
    const CACHE_TAG = 'marketplace_seller_vacation';

    /**
     * @var string
     */
    protected $_cacheTag = 'marketplace_seller_vacation';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'marketplace_seller_vacation';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpSellerVacation\Model\ResourceModel\Vacation');
    }

    /**
     * Load object data.
     *
     * @param int|null $id
     * @param string   $field
     *
     * @return $this
     */
    public function load($id, $field = null)
    {
        if ($id === null) {
            return $this->noRouteSellerVacation();
        }

        return parent::load($id, $field);
    }

    /**
     * Load No-Route SellerVacation.
     *
     * @return \Webkul\MpSellerVacation\Model\Vacation
     */
    public function noRouteSellerVacation()
    {
        return $this->load(self::NOROUTE_ENTITY_ID, $this->getIdFieldName());
    }

    /**
     * Prepare vacation statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }

    /**
     * Get identities.
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG.'_'.$this->getId()];
    }

    /**
     * Get ID.
     *
     * @return int
     */
    public function getId()
    {
        return parent::getData(self::ENTITY_ID);
    }

    /**
     * Set ID.
     *
     * @param int $id
     *
     * @return \Webkul\MpSellerVacation\Api\Data\VacationInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }
}

<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Model;

use Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller as ResourcePreorderSeller;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\AbstractModel;

/**
 * Proerder Seller Model
 *
 */
class PreorderSeller extends AbstractModel implements PreorderSellerInterface, IdentityInterface
{
    /**
     * Proerder Seller cache tag
     */
    const CACHE_TAG = 'preorder_seller';

    /**#@+
     * Proerder Seller's statuses
     */
    const STATUS_ENABLED = 1;
    const STATUS_DISABLED = 0;

    /**#@-*/
    /**
     * @var string
     */
    protected $_cacheTag = 'preorder_seller';

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'preorder_seller';

    /**
     * @return void
     */
    protected function _construct()
    {
        $this->_init('Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller');
    }

    /**
     * Get identities
     *
     * @return array
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId(), self::CACHE_TAG . '_' . $this->getIdentifier()];
    }

    /**
     * Retrieve item id
     *
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * Retrieve Seller ID
     *
     * @return int|null
     */
    public function getSellerId()
    {
        return $this->getData(self::SELLER_ID);
    }

    /**
     * Retrieve Item ID
     *
     * @return int|null
     */
    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    /**
     * Get Type
     *
     * @return int|null
     */
    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    /**
     * Get PreorderPrecent
     *
     * @return int|null
     */
    public function getPreorderPrecent()
    {
        return $this->getData(self::PREORDER_PERCENT);
    }

    /**
     * Get PreorderAction
     *
     * @return int|null
     */
    public function getPreorderAction()
    {
        return $this->getData(self::PREORDER_ACTION);
    }

    /**
     * Get FewProducts
     *
     * @return int|null
     */
    public function getFewProducts()
    {
        return $this->getData(self::FEW_PRODUCTS);
    }

    /**
     * Get DisableProducts
     *
     * @return int|null
     */
    public function getDisableProducts()
    {
        return $this->getData(self::DISABLE_PRODUCTS);
    }

    /**
     * Get CustomMessage
     *
     * @return int|null
     */
    public function getCustomMessage()
    {
        return $this->getData(self::CUSTOM_MESSAGE);
    }

    /**
     * Get OrderMode
     *
     * @return string|null
     */
    public function getEmailType()
    {
        return $this->getData(self::EMAIL_TYPE);
    }

    /**
     * Get completed preorder qty
     *
     * @return int|null
     */
    public function getMppreorderQty()
    {
        return $this->getData(self::MPPREORDER_QTY);
    }

    /**
     * Get Tax Class
     *
     * @return string|null
     */
    public function getPreorderSpecific()
    {
        return $this->getData(self::PREORDER_SPECIFIC);
    }

    /**
     * Get Time
     *
     * @return string|null
     */
    public function getTime()
    {
        return $this->getData(self::TIME);
    }

    /**
     * Set ID
     *
     * @param int $id
     * @return PreorderSellerInterface
     */
    public function setId($id)
    {
        return $this->setData(self::ENTITY_ID, $id);
    }

    /**
     * Set Seller ID
     *
     * @return PreorderSellerInterface
     */
    public function setSellerId($sellerId)
    {
        return $this->setData(self::SELLER_ID, $sellerId);
    }

    /**
     * Set Item ID
     *
     * @return PreorderSellerInterface
     */
    public function setItemId($itemId)
    {
        return $this->setData(self::ITEM_ID, $itemId);
    }

    /**
     * Set Type
     *
     * @return PreorderSellerInterface
     */
    public function setType($type)
    {
        return $this->setData(self::TYPE, $type);
    }

    /**
     * Set Produt ID
     *
     * @return PreorderSellerInterface
     */
    public function setPreorderPrecent($percent)
    {
        return $this->setData(self::PREORDER_PERCENT, $percent);
    }

    /**
     * Set Parent ID
     *
     * @return PreorderSellerInterface
     */
    public function setPreorderAction($action)
    {
        return $this->setData(self::PREORDER_ACTION, $action);
    }

    /**
     * Set Customer ID
     *
     * @return PreorderSellerInterface
     */
    public function setFewProducts($products)
    {
        return $this->setData(self::FEW_PRODUCTS, $products);
    }

    /**
     * Set CustomerEmail
     *
     * @return PreorderSellerInterface
     */
    public function setDisableProducts($disable)
    {
        return $this->setData(self::DISABLE_PRODUCTS, $disable);
    }

    /**
     * Set PaidAmount
     *
     * @return PreorderSellerInterface
     */
    public function setCustomMessage($message)
    {
        return $this->setData(self::CUSTOM_MESSAGE, $message);
    }

    /**
     * Set RemainingAmount
     *
     * @return PreorderSellerInterface
     */
    public function setEmailType($emailType)
    {
        return $this->setData(self::EMAIL_TYPE, $emailType);
    }

    /**
     * Set qty
     *
     * @return PreorderSellerInterface
     */
    public function setMppreorderQty($qty)
    {
        return $this->setData(self::MPPREORDER_QTY, $qty);
    }

    /**
     * Set Status
     *
     * @return PreorderSellerInterface
     */
    public function setPreorderSpecific($specific)
    {
        return $this->setData(self::PREORDER_SPECIFIC, $specific);
    }

    /**
     * Set Time
     *
     * @return PreorderSellerInterface
     */
    public function setTime($time)
    {
        return $this->setData(self::TIME, $time);
    }

    /**
     * Receive page store ids
     *
     * @return int[]
     */
    public function getStores()
    {
        return $this->hasData('stores') ? $this->getData('stores') : $this->getData('store_id');
    }

    /**
     * Prepare block's statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}

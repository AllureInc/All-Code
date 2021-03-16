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
namespace Webkul\MarketplacePreorder\Api\Data;

interface PreorderSellerInterface
{
    const ENTITY_ID         = 'id';
    const SELLER_ID         = 'seller_id';
    const TYPE              = 'type';
    const PREORDER_PERCENT  = 'preorder_percent';
    const PREORDER_ACTION   = 'preorder_action';
    const FEW_PRODUCTS      = 'few_products';
    const DISABLE_PRODUCTS  = 'disable_products';
    const CUSTOM_MESSAGE    = 'custom_message';
    const EMAIL_TYPE        = 'email_type';
    const MPPREORDER_QTY    = 'mppreorder_qty';
    const PREORDER_SPECIFIC = 'preorder_specific';
    const TIME              = 'time';

    /**
     * Get ID
     *
     * @return int|null
     */
    public function getId();

    /**
     * Get Seller ID
     *
     * @return int|null
     */
    public function getSellerId();

    /**
     * Get Type
     *
     * @return int|null
     */
    public function getType();
    /**
     * Get PreorderPrecent
     *
     * @return int|null
     */
    public function getPreorderPrecent();

    /**
     * Get Preorder Action
     *
     * @return int|null
     */
    public function getPreorderAction();

    /**
     * Get Few Products
     *
     * @return int|null
     */
    public function getFewProducts();

    /**
     * Get disable products
     *
     * @return int|null
     */
    public function getDisableProducts();

    /**
     * Get custom message
     *
     * @return int|null
     */
    public function getCustomMessage();

    /**
     * Get email type
     *
     * @return int|null
     */
    public function getEmailType();

    /**
     * Get mp preorder qty
     *
     * @return int|null
     */
    public function getMppreorderQty();

    /**
     * Get preorder specific
     *
     * @return int|null
     */
    public function getPreorderSpecific();

    /**
     * Get Time
     *
     * @return string|null
     */
    public function getTime();

    /**
     * Set ID
     *
     * @return int|null
     */
    public function setId($id);

    /**
     * Set Seller ID
     *
     * @return int|null
     */
    public function setSellerId($sellerId);

    /**
     * Set Type
     *
     * @return int|null
     */
    public function setType($type);
    /**
     * Set PreorderPrecent
     *
     * @return int|null
     */
    public function setPreorderPrecent($percent);

    /**
     * Set Preorder Action
     *
     * @return int|null
     */
    public function setPreorderAction($action);

    /**
     * Set Few Products
     *
     * @return int|null
     */
    public function setFewProducts($products);

    /**
     * Set disable products
     *
     * @return int|null
     */
    public function setDisableProducts($disable);

    /**
     * Set custom message
     *
     * @return int|null
     */
    public function setCustomMessage($message);

    /**
     * Set email type
     *
     * @return int|null
     */
    public function setEmailType($emailType);

    /**
     * Set mp preorder qty
     *
     * @return int|null
     */
    public function setMppreorderQty($qty);

    /**
     * Set preorder specific
     *
     * @return int|null
     */
    public function setPreorderSpecific($specific);

    /**
     * Set Time
     *
     * @return string|null
     */
    public function setTime($time);
}

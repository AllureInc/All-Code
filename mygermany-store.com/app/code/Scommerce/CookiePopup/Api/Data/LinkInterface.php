<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Api\Data;

/**
 * Interface LinkInterface
 * @package Scommerce\CookiePopup\Api\Data
 */
interface LinkInterface
{
    const TABLE = 'scommerce_cookie_popup_link'; // Db table

    const LINK_ID       = 'link_id';
    const CUSTOMER_ID   = 'customer_id';
    const CHOICE_ID     = 'choice_id';
    const STORE_ID      = 'store_id';
    const STATUS        = 'status';
    const CREATED_AT    = 'created_at';
    const UPDATED_AT    = 'updated_at';

    /** @return int */
    public function getLinkId();

    /**
     * @param int $value
     * @return LinkInterface
     */
    public function setLinkId($value);

    /**
     * @return int
     */
    public function getCustomerId();

    /**
     * @param int $value
     * @return LinkInterface
     */
    public function setCustomerId($value);

    /**
     * @return int
     */
    public function getChoiceId();

    /**
     * @param int $value
     * @return LinkInterface
     */
    public function setChoiceId($value);

    /**
     * @return int
     */
    public function getStoreId();

    /**
     * @param int $value
     * @return LinkInterface
     */
    public function setStoreId($value);

    /**
     * @return int
     */
    public function getStatus();

    /**
     * @param int $value
     * @return LinkInterface
     */
    public function setStatus($value);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $value
     * @return LinkInterface
     */
    public function setCreatedAt($value);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $value
     * @return LinkInterface
     */
    public function setUpdatedAt($value);
}

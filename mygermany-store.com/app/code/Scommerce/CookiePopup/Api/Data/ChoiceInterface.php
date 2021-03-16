<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Api\Data;

/**
 * Interface ChoiceInterface
 * @package Scommerce\CookiePopup\Api\Data
 */
interface ChoiceInterface
{
    const TABLE = 'scommerce_cookie_popup_choice'; // Db table
    const TABLE_STORE = 'scommerce_cookie_popup_choice_store'; // Store link

    const CHOICE_ID = 'choice_id';
    const CHOICE_NAME = 'choice_name';
    const CHOICE_DESCRIPTION = 'choice_description';
    const COOKIE_NAME = 'cookie_name';
    const CHOICE_LIST = 'list';
    const REQUIRED = 'required';
    const DEFAULT_STATE = 'default_state';

    /**
     * @return int
     */
    public function getChoiceId();

    /**
     * @param int $value
     * @return ChoiceInterface
     */
    public function setChoiceId($value);

    /**
     * @return string
     */
    public function getChoiceName();

    /**
     * @param string $value
     * @return ChoiceInterface
     */
    public function setChoiceName($value);

    /**
     * @return string
     */
    public function getChoiceDescription();

    /**
     * @param string $value
     * @return ChoiceInterface
     */
    public function setChoiceDescription($value);

    /**
     * @return string
     */
    public function getCookieName();

    /**
     * @param string $value
     * @return ChoiceInterface
     */
    public function setCookieName($value);

    /**
     * @return string
     */
    public function getList();

    /**
     * @param string $value
     * @return ChoiceInterface
     */
    public function setList($value);

    /**
     * @return int
     */
    public function getIsRequired();

    /**
     * @param int $value
     * @return ChoiceInterface
     */
    public function setIsRequired($value);

    /**
     * @return bool
     */
    public function isRequired();

    /**
     * @return mixed | array
     */
    public function getUsedByList();

    /** @return int */
    public function getStoreId();

    /** return int */
    public function getStatus();
}

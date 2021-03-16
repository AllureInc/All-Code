<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Api;

/**
 * Interface ChoiceRegistryInterface
 * @package Scommerce\CookiePopup\Api
 */
interface ChoiceRegistryInterface
{
    const KEY = 'scommerce_cookie_popup_choice'; // Key for storing in registry

    /**
     * Get choice from registry
     *
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceInterface | null
     */
    public function get();

    /**
     * Set choice to registry
     *
     * @param \Scommerce\CookiePopup\Api\Data\ChoiceInterface $model
     * @param bool $graceful
     * @return void
     */
    public function set(\Scommerce\CookiePopup\Api\Data\ChoiceInterface $model, $graceful = false);

    /**
     * Remove current choice from registry
     *
     * @return void
     */
    public function remove();
}

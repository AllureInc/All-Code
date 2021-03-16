<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Api;

use Scommerce\CookiePopup\Api\Data\LinkInterface;

/**
 * Interface LinkRepositoryInterface
 * @package Scommerce\CookiePopup\Api
 */
interface LinkRepositoryInterface
{
    /**
     * @param LinkInterface $model
     * @return mixed
     */
    function save(LinkInterface $model);
}
<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

use \Scommerce\CookiePopup\Api\Data\ChoiceInterface;

/**
 * Class MassRequired
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
class MassRequired extends AbstractMass
{
    /**
     * @inheritdoc
     */
    protected function getRequestKey()
    {
        return ChoiceInterface::REQUIRED;
    }

    /**
     * @inheritdoc
     */
    protected function getValues()
    {
        return [0, 1];
    }

    /**
     * @inheritdoc
     */
    protected function getWrongMessage($key)
    {
        return __('Wrong param required %1', $key);
    }

    /**
     * @inheritdoc
     */
    protected function modify($item, $key)
    {
        return $item->setIsRequired($key);
    }
}

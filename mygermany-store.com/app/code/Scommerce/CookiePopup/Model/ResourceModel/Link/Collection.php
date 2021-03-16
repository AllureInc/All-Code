<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\ResourceModel\Link;

use Scommerce\CookiePopup\Api\Data\LinkInterface;

/**
 * Class Collection
 * @package Scommerce\CookiePopup\Model\ResourceModel\Link
 */
class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    protected $_idFieldName = LinkInterface::LINK_ID;

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init(\Scommerce\CookiePopup\Model\Data\Link::class, \Scommerce\CookiePopup\Model\ResourceModel\Link::class);
    }
}
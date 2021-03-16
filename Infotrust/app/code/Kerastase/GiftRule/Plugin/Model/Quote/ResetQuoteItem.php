<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Kerastase\GiftRule\Plugin\Model\Quote;

use Magento\Quote\Model\Quote;

/**
 * Clear quote addresses after all items were removed.
 */
class ResetQuoteItem
{
    /**
     * Clears the quote addresses when all the items are removed from the cart
     *
     * @param Quote $quote
     * @param Quote $result
     * @param mixed $itemId
     * @return Quote
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected $_customerSession;
    /**
     * @var \Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Kerastase\GiftRule\Helper\Data $giftruleHelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_giftruleHelper          = $giftruleHelper;
    }

    public function beforeRemoveItem($itemId)
    {
        //  $item = $this->getItemById($itemId);

        // if ($item) {
        //     $item->setQuote($this);
        //     /**
        //      * If we remove item from quote - we can't use multishipping mode
        //      */
        //     $this->setIsMultiShipping(false);
        //     $item->isDeleted(true);
        //     if ($item->getHasChildren()) {
        //         foreach ($item->getChildren() as $child) {
        //             $child->isDeleted(true);
        //         }
        //     }

        //     $parent = $item->getParentItem();
        //     if ($parent) {
        //         $parent->isDeleted(true);
        //     }

        //     $this->_eventManager->dispatch('sales_quote_remove_item', ['quote_item' => $item]);
        // }
        $this->_giftruleHelper->log('Is item remove condition check');
        $this->_customerSession->setIsItemRemoved(true);
        return $this;
    }
}

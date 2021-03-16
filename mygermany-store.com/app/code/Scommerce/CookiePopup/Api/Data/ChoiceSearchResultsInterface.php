<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Api\Data;

/**
 * Interface ChoiceSearchResultsInterface
 * @package Scommerce\CookiePopup\Api\Data
 */
interface ChoiceSearchResultsInterface extends \Magento\Framework\Api\SearchResultsInterface
{
    /**
     * Get choice list
     *
     * @return ChoiceInterface[]
     */
    public function getItems();

    /**
     * Set choice list
     *
     * @param ChoiceInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

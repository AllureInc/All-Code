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

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface for preorder items search results.
 * @api
 */
interface PreorderItemsSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get preorder items list.
     *
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface[]
     */
    public function getItems();

    /**
     * Set preorder items list.
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

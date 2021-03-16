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
 * Interface for preorder Complete search results.
 * @api
 */
interface PreorderCompleteSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get preorder Complete list.
     *
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface[]
     */
    public function getItems();

    /**
     * Set preorder Complete list.
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

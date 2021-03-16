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
interface PreorderSellerSearchResultsInterface extends SearchResultsInterface
{
    /**
     * Get preorder seller list.
     *
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface[]
     */
    public function getItems();

    /**
     * Set preorder seller list.
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface[] $items
     * @return $this
     */
    public function setItems(array $items);
}

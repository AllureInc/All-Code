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
namespace Webkul\MarketplacePreorder\Api;

use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Preorder Items block CRUD interface.
 * @api
 */
interface PreorderItemsRepositoryInterface
{
    /**
     * Save Preorder Items.
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface $items
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\PreorderItemsInterface $items);

    /**
     * Retrieve Preorder Items.
     *
     * @param int $id
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Preorder Items matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderItemsSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Preorder Items.
     *
     * @param \Magento\Cms\Api\Data\PreorderItemsInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\PreorderItemsInterface $item);

    /**
     * Delete Preorder Items by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}

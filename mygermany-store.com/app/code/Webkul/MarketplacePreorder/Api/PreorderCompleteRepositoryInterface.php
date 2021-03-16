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
interface PreorderCompleteRepositoryInterface
{
    /**
     * Save Preorder Complete.
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface $items
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\PreorderCompleteInterface $items);

    /**
     * Retrieve Preorder Complete.
     *
     * @param int $id
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Preorder Complete matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderCompleteSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Preorder Complete.
     *
     * @param \Magento\Cms\Api\Data\PreorderItemsInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\PreorderCompleteInterface $item);

    /**
     * Delete Preorder Complete by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}

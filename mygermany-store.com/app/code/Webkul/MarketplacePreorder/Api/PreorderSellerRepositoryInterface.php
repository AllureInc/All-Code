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
 * Preorder Seller block CRUD interface.
 * @api
 */
interface PreorderSellerRepositoryInterface
{
    /**
     * Save Preorder Seller.
     *
     * @param \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface $items
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(Data\PreorderSellerInterface $items);

    /**
     * Retrieve Preorder Seller.
     *
     * @param int $id
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderSellerInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($id);

    /**
     * Retrieve Preorder Seller matching the specified criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MarketplacePreorder\Api\Data\PreorderSellerSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);

    /**
     * Delete Preorder Seller.
     *
     * @param \Magento\Cms\Api\Data\PreorderSellerInterface $item
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(Data\PreorderSellerInterface $item);

    /**
     * Delete Preorder Seller by ID.
     *
     * @param int $id
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($id);
}

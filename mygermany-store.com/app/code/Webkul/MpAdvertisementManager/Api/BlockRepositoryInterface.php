<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Webkul\MpAdvertisementManager\Api\Data\BlockInterface;

/**
 * Block CRUD interface.
 *
 * @api
 */
interface BlockRepositoryInterface
{
    /**
     * Create or update a Block.
     *
     * @param  BlockInterface $block
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(BlockInterface $block);

    /**
     * Get $block by $block ID.
     *
     * @param  int $blockId
     * @return BlockInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If $block with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($blockId);

    /**
     * Retrieve $blocks which match a specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MpAdvertisementManager\Api\Data\BlockSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete $block.
     *
     * @param  BlockInterface $block
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(BlockInterface $block);

    /**
     * Delete $block by ID.
     *
     * @param  int $blockId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($blockId);
}

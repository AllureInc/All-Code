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
use Webkul\MpAdvertisementManager\Api\Data\PricingInterface;

/**
 * Pricing CRUD interface.
 *
 * @api
 */
interface PricingRepositoryInterface
{
    /**
     * Create or update a Pricing.
     *
     * @param  PricingInterface $pricing
     * @return PricingInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(PricingInterface $pricing);

    /**
     * Get $pricing by $pricing ID.
     *
     * @param  int $pricingId
     * @return PricingInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * If $pricing with the specified ID does not exist.
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($pricingId);

    /**
     * Retrieve $pricings which match a specified criteria.
     *
     * @param  \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Webkul\MpAdvertisementManager\Api\Data\PricingSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete $pricing.
     *
     * @param  PricingInterface $pricing
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(PricingInterface $pricing);

    /**
     * Delete $pricing by ID.
     *
     * @param  int $pricingId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($pricingId);
}

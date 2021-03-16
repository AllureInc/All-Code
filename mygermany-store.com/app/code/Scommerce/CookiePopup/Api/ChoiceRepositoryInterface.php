<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Api;

use Magento\Framework\Api\SearchCriteriaInterface;
use Scommerce\CookiePopup\Api\Data\ChoiceInterface;

/**
 * Interface ChoiceRepositoryInterface
 * @package Scommerce\CookiePopup\Api
 */
interface ChoiceRepositoryInterface
{
    /**
     * @param ChoiceInterface|\Magento\Framework\Model\AbstractModel $model
     * @return ChoiceInterface|\Magento\Framework\Model\AbstractModel
     */
    public function save(ChoiceInterface $model);

    /**
     * @param int $id
     * @return ChoiceInterface|\Magento\Framework\Model\AbstractModel
     */
    public function get($id);

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return Data\ChoiceSearchResultsInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param ChoiceInterface|\Magento\Framework\Model\AbstractModel $model
     */
    public function delete(ChoiceInterface $model);

    /**
     * @param int $id
     * @return bool
     */
    public function deleteById($id);
}

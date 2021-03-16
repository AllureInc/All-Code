<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Export;

use Magento\Framework\Api\SearchResultsInterface;

/**
 * Interface CategorySearchResultsInterface
 * @package Plenty\Item\Api\Data\Export
 */
interface CategorySearchResultsInterface extends SearchResultsInterface
{
    /**
     * @return \Magento\Framework\Api\ExtensibleDataInterface[]
     */
    public function getItems();

    /**
     * @param array $items
     * @return \Magento\Framework\Api\SearchResultsInterface
     */
    public function setItems(array $items);
}
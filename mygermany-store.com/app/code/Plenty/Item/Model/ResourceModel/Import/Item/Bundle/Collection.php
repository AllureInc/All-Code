<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item\Bundle;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Item\Model\Import\Item\Bundle;
use Plenty\Item\Model\ResourceModel;
use Plenty\Item\Api\Data\Import\Item\BundleInterface;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Import\Item\Bundle
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'plenty_item_import_item_bundle_id';

    protected function _construct()
    {
        $this->_init(Bundle::class, ResourceModel\Import\Item\Bundle::class);
    }

    /**
     * @param $variationId
     * @return $this
     */
    public function addVariationFilter($variationId)
    {
        $this->addFieldToFilter('main_table.'.BundleInterface::VARIATION_ID, $variationId);
        return $this;
    }

    /**
     * @param $itemId
     * @return $this
     */
    public function addItemFilter($itemId)
    {
        $this->addFieldToFilter('main_table.'.BundleInterface::ITEM_ID, $itemId);
        return $this;
    }

    /**
     * @param $variationId
     * @return $this
     */
    public function joinVariationEntries()
    {
        $this->getSelect()
            ->join(
                [
                    'variation_tb' => $this->getTable('plenty_item_import_item_variation')
                ],
                'variation_tb.variation_id = main_table.component_variation_id',
                [
                    BundleInterface::COMPONENT_SKU => 'sku',
                    BundleInterface::COMPONENT_NAME => 'name',
                ]
            );

        return $this;
    }
}
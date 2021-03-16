<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Item;

use \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Item\Api\Data\Import\ItemInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Import\Item
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = ItemInterface::ENTITY_ID;

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\Import\Item::class, \Plenty\Item\Model\ResourceModel\Import\Item::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(ItemInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(ItemInterface::STATUS, ['eq' => Status::PENDING]);
        return $this;
    }

    /**
     * @param $itemId
     * @return $this
     */
    public function addItemFilter($itemId)
    {
        $this->addFieldToFilter(ItemInterface::ITEM_ID, (int) $itemId);
        return $this;
    }

    /**
     * @param $variationId
     * @return $this
     */
    public function addVariationFilter($variationId)
    {
        $this->addFieldToFilter(ItemInterface::VARIATION_ID, (int) $variationId);
        return $this;
    }

    /**
     * @param $flagKey
     * @param $flagValue
     * @return $this
     */
    public function addFlagFilter($flagKey, $flagValue)
    {
        $this->addFieldToFilter($flagKey, $flagValue);
        return $this;
    }
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import\Category;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Item\Model\Import\Category as CategoryModel;
use Plenty\Item\Model\ResourceModel\Import\Category as CategoryResource;
use Plenty\Item\Api\Data\Import\CategoryInterface;

/**
 * Class Collection
 * @package Plenty\Item\Model\ResourceModel\Import\Category
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = CategoryInterface::ENTITY_ID;

    protected function _construct()
    {
        $this->_init(CategoryModel::class, CategoryResource::class);
    }

    /**
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(CategoryInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }

    /**
     * @return $this
     */
    public function addPendingFilter()
    {
        $this->addFieldToFilter(CategoryInterface::STATUS, ['eq' => 'pending']);
        return $this;
    }

    /**
     * @param $spec
     * @return $this
     */
    public function addUniquePathFilter($spec)
    {
        $this->getSelect()->group($spec);
        return $this;
    }
}
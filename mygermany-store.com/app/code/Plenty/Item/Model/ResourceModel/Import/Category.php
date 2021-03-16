<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import;

use Plenty\Core\Model\Profile;
use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\CategoryInterface;
use Plenty\Core\Model\Source\Status;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;

/**
 * Class Category
 * @package Plenty\Item\Model\ResourceModel\Import
 */
class Category extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init('plenty_item_import_category', 'entity_id');
    }

    /**
     * @param Profile $profile
     * @param Collection $data
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    public function saveCategories(
        Profile $profile, Collection $data, array $fields = []
    ) {
        $categories = [];
        /** @var DataObject $item */
        foreach ($data as $item) {
            $item->setData(CategoryInterface::PROFILE_ID, $profile->getId());
            $categories[] = $this->_prepareCategoryDataForSave($item);
        }

        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $categories, $fields);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param DataObject $category
     * @return array
     */
    private function _prepareCategoryDataForSave(DataObject $category): array
    {
        if (!$profileId = $category->getData(CategoryInterface::PROFILE_ID)) {
            return [];
        }

        return [
            CategoryInterface::PROFILE_ID => $profileId,
            CategoryInterface::CATEGORY_ID => $category->getData(CategoryInterface::CATEGORY_ID),
            CategoryInterface::PARENT_ID => $category->getData(CategoryInterface::PARENT_ID),
            CategoryInterface::LEVEL => $category->getData(CategoryInterface::LEVEL),
            CategoryInterface::TYPE => $category->getData(CategoryInterface::TYPE),
            CategoryInterface::NAME => $category->getData(CategoryInterface::NAME),
            CategoryInterface::PATH => $category->getData(CategoryInterface::PATH),
            CategoryInterface::ORIGINAL_PATH => $category->getData(CategoryInterface::ORIGINAL_PATH),
            CategoryInterface::PREVIEW_URL => $category->getData(CategoryInterface::PREVIEW_URL),
            CategoryInterface::HAS_CHILDREN => $category->getData(CategoryInterface::HAS_CHILDREN),
            CategoryInterface::DETAILS => $this->_serializer->serialize($category->getData(CategoryInterface::DETAILS)),
            CategoryInterface::STATUS => Status::PENDING,
            CategoryInterface::CREATED_AT => $this->_date->gmtDate(),
            CategoryInterface::UPDATED_AT => $category->getData(CategoryInterface::UPDATED_AT),
            CategoryInterface::COLLECTED_AT => $this->_date->gmtDate(),
            CategoryInterface::MESSAGE => __('Collected.')
        ];
    }
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Item\Api\CategoryImportManagementInterface;
use Plenty\Item\Model\ResourceModel\Import\Category\CollectionFactory;
use Plenty\Item\Model\Import\Category as CategoryModel;
use Plenty\Core\Plugin\ImportExport\Model\ImportFactory;
use Plenty\Item\Model\Logger;
use Plenty\Item\Helper;

/**
 * Class CategoryImportManagement
 * @package Plenty\Item\Profile
 */
class CategoryImportManagement extends AbstractManagement
    implements CategoryImportManagementInterface
{
    /**
     * @var CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var ImportFactory
     */
    private $_importFactory;

    /**
     * CategoryImportManagement constructor.
     * @param ImportFactory $importFactory
     * @param CollectionFactory $collectionFactory
     * @param Helper\Data $helper
     * @param Logger $logger
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ImportFactory $importFactory,
        CollectionFactory $collectionFactory,
        Helper\Data $helper,
        Logger $logger,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_importFactory = $importFactory;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($helper, $logger, $dateTime, $serializer, $data);
    }

    /**
     * @param |null $defaultLang
     * @param array $categoryIds
     * @return $this|CategoryImportManagementInterface
     * @throws \Exception
     */
    public function execute(
        $defaultLang,
        array $categoryIds = []
    ) {
        $this->_initResponseData();

        $collection = $this->_collectionFactory->create();
        $collection->addProfileFilter($this->getProfile()->getId());

        if (!empty($categoryIds)) {
            $collection->addFieldToFilter('category_id', ['in' => $categoryIds]);
        } else {
            $collection->addPendingFilter();
        }

        $collection->addUniquePathFilter('path');

        if (!$collection->getSize()) {
            $this->setResponse(__('No categories found for import'));
            return $this;
        }

        $categories = [];
        /** @var CategoryModel $category */
        foreach ($collection as $category) {
            if (!$categoryPath = $category->getPath()) {
                continue;
            }

            $categoryPath = explode('/', $categoryPath);
            $rootCategory = array_shift($categoryPath);
            $categoryPath = implode('/', $categoryPath);

            if (!$categoryPath) {
                continue;
            }

            $categoryDetails = $category->getDetails();

            $index = $this->getSearchArrayMatch($defaultLang, $categoryDetails, 'lang');
            if (false === $index) {
                $index = 0;
            }

            array_push($categories, [
                '_root' => $rootCategory,
                '_category' => $categoryPath,
                'plenty_category_id' => $category->getCategoryId(),
                'profile_id' => $this->getProfile()->getId(),
                'name' => $category->getName(),
                'description' => $categoryDetails[$index]['description'] ?? $category->getName(),
                'is_active' => '0',
                'include_in_menu' => '0',
                'meta_description' => $categoryDetails[$index]['metaDescription'] ?? '',
                'available_sort_by' => 'position',
                'default_sort_by' => 'position'
            ]);
        }

        $this->_importCategories($categories);

        return $this;
    }

    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_response = [];
        return $this;
    }

    /**
     * @param $categoryData
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function _importCategories($categoryData)
    {
        $model = $this->_importFactory->create();
        $model->setEntityCode('catalog_category')
            ->setBehavior('append')
            ->setProfile($this->getProfile())
            ->setRequest($categoryData)
            ->execute();
        return $this;
    }
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;

use Plenty\Item\Api\CategoryImportRepositoryInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class CatalogCategoryImportAfter
 * @package Plenty\Item\Observer
 */
class CatalogCategoryImportAfter implements ObserverInterface
{
    /**
     * @var CategoryImportRepositoryInterface
     */
    private $_categoryImportRepository;

    /**
     * @var DateTime
     */
    private $_date;

    /**
     * CatalogCategoryImportAfter constructor.
     * @param CategoryImportRepositoryInterface $categoryImportRepository
     * @param DateTime $dateTime
     */
    public function __construct(
        CategoryImportRepositoryInterface $categoryImportRepository,
        DateTime $dateTime
    ) {
        $this->_categoryImportRepository = $categoryImportRepository;
        $this->_date = $dateTime;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        if ($importedItems = $observer->getEvent()->getData('bunch')) {
           $this->_registerImportedCategories($importedItems);
        }

        if ($failedCategories = $observer->getEvent()->getData('bunch_failed')) {
            $this->_registerFailedCategories($failedCategories);
        }

        return $this;
    }

    /**
     * @param $importedCategories
     * @return $this
     * @throws \Magento\Framework\Exception\CouldNotSaveException
     */
    private function _registerImportedCategories($importedCategories)
    {
        if (empty($importedItems)) {
            return $this;
        }

        foreach ($importedCategories as $category) {
            $category['status'] = Status::COMPLETE;
            $category['message'] = __('Category has been imported.');
        }

        $this->_categoryImportRepository->saveMultiple($importedCategories, ['status', 'message']);

        return $this;
    }

    /**
     * @param $failedCategories
     * @return $this
     */
    private function _registerFailedCategories($failedCategories)
    {
        if (empty($failedItems)
            || !$profileId = current(array_column($failedCategories, 'profile_id'))
        ) {
            return $this;
        }

        try {
            $this->_categoryImportRepository->saveMultiple($failedCategories, ['status', 'message']);
        } catch (\Exception $e) {}

        return $this;
    }
}
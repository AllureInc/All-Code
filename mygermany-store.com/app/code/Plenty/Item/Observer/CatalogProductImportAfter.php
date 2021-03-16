<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\CatalogImportExport\Model\Import\Product as ImportProductModel;
use Magento\Framework\Exception\LocalizedException;

use Plenty\Item\Model\ResourceModel\Import\Item as ItemResourceModel;
use Plenty\Item\Model\ResourceModel\Import\Item\Variation as VariationResourceModel;
use Plenty\Core\Model\Source\Status;

/**
 * Class CatalogProductImportAfter
 * @package Plenty\Item\Observer
 */
class CatalogProductImportAfter implements ObserverInterface
{
    /**
     * @var ItemResourceModel
     */
    private $_itemResourceFactory;

    /**
     * @var VariationResourceModel
     */
    private $_variationResourceFactory;

    /**
     * @var DateTime
     */
    private $_date;

    /**
     * CatalogProductImportBunchSaveAfter constructor.
     * @param ItemResourceModel $itemResourceFactory
     * @param VariationResourceModel $variationResourceFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        ItemResourceModel $itemResourceFactory,
        VariationResourceModel $variationResourceFactory,
        DateTime $dateTime
    ) {
        $this->_itemResourceFactory = $itemResourceFactory;
        $this->_variationResourceFactory = $variationResourceFactory;
        $this->_date = $dateTime;
    }

    /**
     * @param EventObserver $observer
     * @return $this|void
     * @throws LocalizedException
     */
    public function execute(EventObserver $observer)
    {
        // event: catalog_product_import_bunch_save_after :: bunch
        // event: catalog_product_import_bunch_delete_after :: bunch

        /** @var ImportProductModel $adapter */
        $adapter = $observer->getEvent()->getData('adapter');

        if ($importedItems = $observer->getEvent()->getData('bunch')) {
            $this->_registerImportedItems($importedItems);
        }

        if ($failedItems = $observer->getEvent()->getData('bunch_failed')) {
            $this->_registerFailedItems($failedItems);
        }

        return $this;
    }

    /**
     * @param $importedItems
     * @return $this
     * @throws LocalizedException
     */
    private function _registerImportedItems($importedItems)
    {
        if (empty($importedItems)) {
            return $this;
        }

        $profileId = current(array_column($importedItems, 'profile_id'));
        $variationIds = array_column($importedItems, 'plenty_variation_id');

        $bind = [
            'status' => Status::COMPLETE,
            'processed_at' => $this->_date->gmtDate(),
            'message' => __('Product has been imported.')
        ];

        $this->_itemResourceFactory->update($bind,
            [
                'variation_id IN (?)' => array_values($variationIds),
                'profile_id = ?' => (int) $profileId
            ]
        );
        $this->_variationResourceFactory
            ->update($bind, ['variation_id IN (?)' => array_values($variationIds)]);

        return $this;
    }

    /**
     * @param $failedItems
     * @return $this
     */
    private function _registerFailedItems($failedItems)
    {
        if (empty($failedItems)
            || !$profileId = current(array_column($failedItems, 'profile_id'))
        ) {
            return $this;
        }

        $bind = [
            'status' => Status::FAILED,
            'processed_at' => $this->_date->gmtDate()
        ];

        $variationIds = array_column($failedItems, 'variation_id');

        try {
            $this->_itemResourceFactory
                ->addMultiple($failedItems, ['status', 'message', 'processed_at']);
            $this->_variationResourceFactory
                ->update($bind, ['variation_id IN (?)' => array_values($variationIds)]);
        } catch (\Exception $e) {}

        return $this;
    }
}
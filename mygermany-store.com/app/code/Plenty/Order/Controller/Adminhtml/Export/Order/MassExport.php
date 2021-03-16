<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Controller\Adminhtml\Export\Order;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

use Plenty\Core\Controller\Adminhtml\Profile as ProfileAction;
use Plenty\Core\Api\ProfileRepositoryInterface;
use Plenty\Core\Model\Profile;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Model\Source\Status;

/**
 * Class MassExport
 * @package Plenty\Order\Controller\Adminhtml\Export\Order
 */
class MassExport extends ProfileAction
{
    private $_orderExportFactory;

    /**
     * MassExport constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StringUtils $string
     * @param Profile\Config\Structure $configStructure
     * @param Profile\ConfigFactory $configFactory
     * @param Profile\Type $profileEntityType
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param Profile\HistoryFactory $profileHistoryFactory
     * @param DateTime $dateTime
     */
    public function __construct(
        Action\Context $context,
        Registry $coreRegistry,
        LayoutFactory $resultLayoutFactory,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        JsonFactory $resultJsonFactory,
        DataPersistorInterface $dataPersistor,
        StringUtils $string,
        Profile\Config\Structure $configStructure,
        Profile\ConfigFactory $configFactory,
        Profile\Type $profileEntityType,
        ProfileFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        Profile\HistoryFactory $profileHistoryFactory,
        DateTime $dateTime
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $resultJsonFactory, $dataPersistor, $string, $configStructure, $configFactory, $profileEntityType, $profileFactory, $profileRepository, $profileHistoryFactory, $dateTime);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getParam('export_ids');
        if (empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select products.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        if (!$profileId = $this->getRequest()->getParam('id')) {
            $this->messageManager->addErrorMessage(__('Could not find profile.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        $profile = $this->_initCurrentProfile();
        $typeInstance = $profile->getTypeInstance();

        /** @var \Plenty\Item\Model\ResourceModel\Export\Product\Collection $products */
        $products = $this->_collectionFactory->create();
        $products->addFieldToFilter('entity_id', ['in' => $ids]);

        if (!$products->getSize()) {
            $this->messageManager->addErrorMessage(__('Could not find products. [Ids: %1]', implode(', ', $ids)));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        $exportResult = [];
        /** @var \Plenty\Item\Model\Export\Product $product */
        foreach ($products as $product) {
            try {
                $product->setProfileEntity($typeInstance);
                $product->export();
                $exportResult['success'][] = $product->getVariationId();
            } catch (\Exception $e) {
                $exportResult['error'][] = [
                    'status'        => Status::ERROR,
                    'profile_id'    => $profileId,
                    'product_id'    => $product->getProductId(),
                    'message'       => $e->getMessage(),
                    'updated_at'    => $this->_dateTime->gmtDate()
                ];
                continue;
            }
        }

        $this->_handleExportResult($profile, $exportResult);

        if (isset($exportResult['success'])) {
            $message = __('Products have been exported. [Manual action]');
            $status = Status::SUCCESS;
            $this->messageManager->addSuccessMessage($message);
        } else {
            $message = __('Could not export products. [Manual action]');
            $status = Status::ERROR;
            $this->messageManager->addErrorMessage($message);
        }

        $this->_updateHistory($profileId, \Plenty\Item\Model\Profile\Export\Entity\Product::STAGE_EXPORT_PRODUCT, $status, $message);

        return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
    }

    /**
     * @param Profile $profile
     * @param array $exportResult
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _handleExportResult(Profile $profile, array $exportResult)
    {
        if (isset($exportResult['error']) && !empty($exportResult['error'])) {
            $this->_orderExportFactory
                ->updateMultipleRecords($exportResult['error'], ['status', 'message', 'updated_at']);
        }

        if (isset($exportResult['success']) && !empty($exportResult['success'])) {
            $this->_updateItemApiStatus($profile, $exportResult['success']);
        }

        return $this;
    }

    /**
     * @param Profile $profile
     * @param array $variationIds
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _updateItemApiStatus(Profile $profile, array $variationIds)
    {
        $bind = [
            'status' => Status::COMPLETE,
            'processed_at' => $this->_dateTime->gmtDate()
        ];

        $where = [
            'variation_id IN(?)' => $variationIds,
            'profile_id = ?' => (int) $profile->getId()
        ];

        $this->_itemFactory->updateItem($bind, $where);

        return $this;
    }
}

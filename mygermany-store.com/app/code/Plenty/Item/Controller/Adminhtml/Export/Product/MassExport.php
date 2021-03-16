<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Controller\Adminhtml\Export\Product;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Api\FilterBuilder;
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
use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Model\ResourceModel\Export\Product;
use Plenty\Item\Model\ResourceModel\Import\Item;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;

/**
 * Class MassExport
 * @package Plenty\Item\Controller\Adminhtml\Export\Product
 */
class MassExport extends ProfileAction
{
    private $_productResource;

    /**
     * @var Product\CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var Item
     */
    private $_itemFactory;

    /**
     * @var FilterBuilder
     */
    private $_filterBuilder;

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
     * @param Product $productResource
     * @param Product\CollectionFactory $collectionFactory
     * @param Item $itemFactory
     * @param FilterBuilder $filterBuilder
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
        DateTime $dateTime,
        Product $productResource,
        Product\CollectionFactory $collectionFactory,
        Item $itemFactory,
        FilterBuilder $filterBuilder
    ) {
        $this->_productResource = $productResource;
        $this->_collectionFactory = $collectionFactory;
        $this->_itemFactory = $itemFactory;
        $this->_filterBuilder = $filterBuilder;
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $resultJsonFactory, $dataPersistor, $string, $configStructure, $configFactory, $profileEntityType, $profileFactory, $profileRepository, $profileHistoryFactory, $dateTime);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Redirect|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$ids = $this->getRequest()->getParam('product_ids')) {
            $this->messageManager->addErrorMessage(__('Please select products.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        if (!$profileId = $this->getRequest()->getParam('id')) {
            $this->messageManager->addErrorMessage(__('Could not find profile.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        $profile = $this->_initCurrentProfile();
        /** @var ProductExportInterface $typeInstance */
        $typeInstance = $profile->getTypeInstance();
        $typeInstance->createHistory('product_export', 'processing');
        $filter = $this->_filterBuilder->setField('entity_id')
            ->setValue($ids)
            ->setConditionType('in')
            ->create();
        $typeInstance->setProductExportSearchCriteria([$filter]);

        try {
            $typeInstance->exportProducts();
            $message = __('Products have been exported. [Manual action]');
            $typeInstance->getHistory()->setStatus(Status::SUCCESS);
            $this->messageManager->addSuccessMessage($message);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $typeInstance->getHistory()->setStatus(Status::FAILED);
            $typeInstance->addResponse($e->getMessage(), 'error');
        }

        $typeInstance->handleHistory();

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
            $this->_productResource
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

        $this->_itemFactory->update($bind, $where);

        return $this;
    }
}

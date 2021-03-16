<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Controller\Adminhtml\Export\Product;

use Magento\Backend\App\Action;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\View\Result\PageFactory;

use Plenty\Core\Api\ProfileRepositoryInterface;
use Plenty\Core\Controller\Adminhtml\Profile;
use Plenty\Core\Model\Profile as ProfileModel;
use Plenty\Core\Model\Profile\Config\Structure;
use Plenty\Core\Model\Profile\ConfigFactory;
use Plenty\Core\Model\Profile\HistoryFactory;
use Plenty\Core\Model\Profile\Type;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Model\ResourceModel\Export\Product as ProductExportFactory;

/**
 * Class MassStopExport
 * @package Plenty\Item\Controller\Adminhtml\Export\Product
 */
class MassStopExport extends Profile
{
    /**
     * @var ProductExportFactory
     */
    private $_productExportFactory;

    /**
     * MassStopExport constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StringUtils $string
     * @param Structure $configStructure
     * @param ConfigFactory $configFactory
     * @param Type $profileEntityType
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param HistoryFactory $profileHistoryFactory
     * @param DateTime $dateTime
     * @param ProductExportFactory $productExportFactory
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
        ProfileModel\Config\Structure $configStructure,
        ProfileModel\ConfigFactory $configFactory,
        ProfileModel\Type $profileEntityType,
        ProfileFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        ProfileModel\HistoryFactory $profileHistoryFactory,
        DateTime $dateTime,
        ProductExportFactory $productExportFactory
    ) {
        $this->_productExportFactory = $productExportFactory;
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $resultJsonFactory, $dataPersistor, $string, $configStructure, $configFactory, $profileEntityType, $profileFactory, $profileRepository, $profileHistoryFactory, $dateTime);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        if (!$ids = $this->getRequest()->getParam('product_ids', [])) {
            $this->messageManager->addErrorMessage(__('Please select entries.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        if (!$profileId = $this->getRequest()->getParam('id')) {
            $this->messageManager->addErrorMessage(__('Could not find profile.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        try {
            $this->_productExportFactory->updateStatus(Status::STOPPED, $ids);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not stop product export. [Reason: %1]', $e->getMessage()));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        $message = __('Product(s) have been excluded from export.');
        $this->_updateHistory($profileId, 'stop_product_export', Status::SUCCESS, $message);
        $this->messageManager->addSuccessMessage($message);
        return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
    }
}

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

use Plenty\Core\Api\ProfileRepositoryInterface;
use Plenty\Core\Controller\Adminhtml\Profile;
use Plenty\Core\Model\Profile as ProfileModel;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Model\ResourceModel\Export\Product as ProductExportFactory;

/**
 * Class MassDelete
 * @package Plenty\Order\Controller\Adminhtml\Export\Order
 */
class MassDelete extends Profile
{
    /**
     * @var ProductExportFactory
     */
    private $__orderExportFactory;

    /**
     * MassDelete constructor.
     * @param Action\Context $context
     * @param Registry $coreRegistry
     * @param LayoutFactory $resultLayoutFactory
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param JsonFactory $resultJsonFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StringUtils $string
     * @param ProfileModel\Config\Structure $configStructure
     * @param ProfileModel\ConfigFactory $configFactory
     * @param ProfileModel\Type $profileEntityType
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileModel\HistoryFactory $profileHistoryFactory
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
        ProfileModel\Config\Structure $configStructure,
        ProfileModel\ConfigFactory $configFactory,
        ProfileModel\Type $profileEntityType,
        ProfileFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        ProfileModel\HistoryFactory $profileHistoryFactory,
        DateTime $dateTime
    ) {
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $resultJsonFactory, $dataPersistor, $string, $configStructure, $configFactory, $profileEntityType, $profileFactory, $profileRepository, $profileHistoryFactory, $dateTime);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $ids = $this->getRequest()->getParam('product', []);
        if (empty($ids)) {
            $this->messageManager->addErrorMessage(__('Please select entries.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        $profileId = $this->getRequest()->getParam('id');
        if (!$profileId) {
            $this->messageManager->addErrorMessage(__('Could not find profile.'));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        try {
            $this->__orderExportFactory->removeEntry('entity_id', $ids);
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('Could not delete products. [Reason: %1]', $e->getMessage()));
            return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
        }

        $message = __('Product(s) have been deleted from export list.');
        $this->_updateHistory($profileId, 'remove_product_listing', Status::SUCCESS, $message);
        $this->messageManager->addSuccessMessage($message);
        return $resultRedirect->setPath('*/*/edit', ['_current' => true]);
    }
}

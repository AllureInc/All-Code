<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Controller\Adminhtml\Profile\Config;

use Magento\Backend\App\Action;
use Magento\Framework\DataObject;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Api\ProfileRepositoryInterface;
use Plenty\Item\Model\Config\Source as ConfigSourceModel;
use Plenty\Item\Api\Data\Profile\ProductExportInterface;
use Plenty\Item\Api\Data\Profile\ProductImportInterface;

/**
 * Class CollectData
 * @package Plenty\Item\Controller\Adminhtml\Profile\Config
 */
class CollectData extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $_resultJsonFactory;

    /**
     * @var ProfileFactory
     */
    private $_profileFactory;

    /**
     * @var ProfileRepositoryInterface
     */
    private $_profileRepository;

    /**
     * @var ConfigSourceModel
     */
    private $_configSourceModel;

    /**
     * CollectData constructor.
     * @param Action\Context $context
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param JsonFactory $resultJsonFactory
     * @param ConfigSourceModel $configSourceModel
     */
    public function __construct(
        Action\Context $context,
        ProfileFactory $profileFactory,
        ProfileRepositoryInterface $profileRepository,
        JsonFactory $resultJsonFactory,
        ConfigSourceModel $configSourceModel
    ) {
        $this->_profileFactory = $profileFactory;
        $this->_profileRepository = $profileRepository;
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_configSourceModel = $configSourceModel;
        parent::__construct($context);
    }

    /**
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->_collectConfigData();

        /** @var Json $resultJson */
        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($result->getData());
        return $resultJson;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function _collectConfigData()
    {
        $eventResponse = new DataObject();

        try {
            /** @var ConfigSourceModel $configModel */
            $configModel = $this->_configSourceModel;
            $configModel->collectWebStoreConfigs();
            $configModel->collectItemSalesPrices();
            $configModel->collectVatConfigs();
            $configModel->collectItemBarcodeConfigs();
            $configModel->collectWarehouseConfigs();

            $profileTypeInstance = $this->_getProfileTypeInstance();

            /** @var ProductExportInterface|ProductImportInterface $profileTypeInstance */
            if ($profileTypeInstance instanceof ProductExportInterface
                || $profileTypeInstance instanceof ProductImportInterface
            ) {
                $profileTypeInstance->setApiBehaviour('replace')
                    ->collectAttributes()
                    ->collectCategories();
            }

            $msg =  __('Data has been collected.');
            $eventResponse->setData('success',$msg);
            $this->messageManager->addSuccessMessage($msg);
        } catch (\Exception $e) {
            $msg = __('Could not collect data. Reason: %1', $e->getMessage());
            $eventResponse->setData('error', $msg);
            $this->messageManager->addErrorMessage($msg);
        }

        return $eventResponse;
    }

    /**
     * @return bool|\Plenty\Core\Model\Profile\Type\AbstractType
     */
    private function _getProfileTypeInstance()
    {
        if (!$id = $this->getRequest()->getParam('id')) {
            return false;
        }

        try {
            $profile = $this->_profileRepository->getById($id);
            return $profile->getTypeInstance();
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('This profile no longer exists.'));
        }

        return false;
    }
}
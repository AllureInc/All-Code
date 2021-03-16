<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Controller\Adminhtml\Export\Product;

use Magento\Backend\App\Action;
use Magento\Framework\View\Result\LayoutFactory;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Stdlib\StringUtils;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Core\Api\ProfileRepositoryInterface;
use Plenty\Core\Controller\Adminhtml\Profile;
use Plenty\Core\Model\Profile as ProfileModel;
use Plenty\Core\Model\Profile\Type;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Item\Model\Export\Product;

/**
 * Class AddProduct
 * @package Plenty\Item\Controller\Adminhtml\Export\Product
 */
class AddProduct extends Profile
{
    /**
     * @var Product
     */
    private $_exportProductFactory;

    /**
     * AddProduct constructor.
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
     * @param Type $profileEntityType
     * @param ProfileFactory $profileFactory
     * @param ProfileRepositoryInterface $profileRepository
     * @param ProfileModel\HistoryFactory $profileHistoryFactory
     * @param DateTime $dateTime
     * @param Product $exportProductFactory
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
        Product $exportProductFactory
    ) {
        $this->_exportProductFactory = $exportProductFactory;
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $resultJsonFactory, $dataPersistor, $string, $configStructure, $configFactory, $profileEntityType, $profileFactory, $profileRepository, $profileHistoryFactory, $dateTime);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->_addProductsToExport();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($result->getData());
        return $resultJson;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function _addProductsToExport()
    {
        $eventResponse = new \Magento\Framework\DataObject();

        if (!$productIds = explode(',', $this->getRequest()->getParam('product_ids'))) {
            return $eventResponse->setData('error', __('Please select products.'));
        }

        try {
            $profile = $this->_initCurrentProfile();

            /** @var \Plenty\Item\Model\Export\Product $exportObj */
            $exportObj = $this->_exportProductFactory;
            $exportObj->setBehaviour($this->getRequest()->getParam('behaviour'));
            $exportObj->addProductsToExport($profile->getId(), $productIds);
            $eventResponse->setData('success', __('Products have been added to export list.'));
        } catch (\Exception $e) {
            $eventResponse->setData('error', __('Could not add products. Reason: %1', $e->getMessage()));
        }

        return $eventResponse;
    }
}
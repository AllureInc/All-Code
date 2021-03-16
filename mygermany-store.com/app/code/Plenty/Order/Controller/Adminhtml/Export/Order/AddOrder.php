<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Controller\Adminhtml\Export\Order;

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
use Plenty\Core\Model\ProfileFactory;
use Plenty\Order\Model\Export\Order;

/**
 * Class AddOrder
 * @package Plenty\Order\Controller\Adminhtml\Export\Order
 */
class AddOrder extends Profile
{
    /**
     * @var Order
     */
    private $_exportOrderFactory;

    /**
     * AddOrder constructor.
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
     * @param Order $exportOrderFactory
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
        Order $exportOrderFactory
    ) {
        $this->_exportOrderFactory = $exportOrderFactory;
        parent::__construct($context, $coreRegistry, $resultLayoutFactory, $resultPageFactory, $resultForwardFactory, $resultJsonFactory, $dataPersistor, $string, $configStructure, $configFactory, $profileEntityType, $profileFactory, $profileRepository, $profileHistoryFactory, $dateTime);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result = $this->_addOrdersToExport();

        /** @var \Magento\Framework\Controller\Result\Json $resultJson */
        $resultJson = $this->_resultJsonFactory->create();
        $resultJson->setHeader('Content-type', 'application/json', true);
        $resultJson->setData($result->getData());
        return $resultJson;
    }

    /**
     * @return \Magento\Framework\DataObject
     */
    private function _addOrdersToExport()
    {
        $eventResponse = new \Magento\Framework\DataObject();

        if (!$orderIds = explode(',', $this->getRequest()->getParam('order_ids'))) {
            return $eventResponse->setData('error', __('Please select orders.'));
        }

        try {
            $profile = $this->_initCurrentProfile();

            /** @var \Plenty\Order\Model\Export\Order $exportObj */
            $exportObj = $this->_exportOrderFactory;
            $exportObj->setProfileEntity($profile->getTypeInstance());
            $exportObj->addOrdersToExport($orderIds);
            $eventResponse->setData('success', __('Orders have been added to export list.'));
        } catch (\Exception $e) {
            $eventResponse->setData('success', __('Could not add orders. Reason: %1', $e->getMessage()));
        }

        return $eventResponse;
    }
}
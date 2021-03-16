<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Controller\Adminhtml\Profile\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Plenty\Stock\Model\Config\Source as ConfigSourceModel;

/**
 * Class CollectData
 * @package Plenty\Stock\Controller\Adminhtml\Profile\Config
 */
class CollectData extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var ConfigSourceModel
     */
    private $_configSourceModel;

    /**
     * CollectData constructor.
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigSourceModel $configSourceModel
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ConfigSourceModel $configSourceModel
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_configSourceModel = $configSourceModel;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
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
        $eventResponse = new \Magento\Framework\DataObject();

        try {
            /** @var ConfigSourceModel $configModel */
            $configModel = $this->_configSourceModel;
            $configModel->collectWarehouseConfigs();

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
}
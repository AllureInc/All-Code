<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Controller\Adminhtml\Profile\Config;

use Magento\Backend\App\Action;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Plenty\Order\Model\Config\Source as ConfigSource;
use Plenty\Stock\Model\Config\Source as StockConfigSource;

/**
 * Class CollectData
 * @package Plenty\Order\Controller\Adminhtml\Profile\Config
 */
class CollectData extends Action
{
    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    protected $_resultJsonFactory;

    /**
     * @var ConfigSource
     */
    private $_configSource;

    /**
     * @var ConfigSource
     */
    private $_stockConfigSource;

    /**
     * CollectData constructor.
     * @param Action\Context $context
     * @param JsonFactory $resultJsonFactory
     * @param ConfigSource $configSourceModel
     * @param StockConfigSource $stockConfigSource
     */
    public function __construct(
        Action\Context $context,
        JsonFactory $resultJsonFactory,
        ConfigSource $configSourceModel,
        StockConfigSource $stockConfigSource
    ) {
        $this->_resultJsonFactory = $resultJsonFactory;
        $this->_configSource = $configSourceModel;
        $this->_stockConfigSource = $stockConfigSource;
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
            $this->_configSource->collectWebStoreConfigs()
                ->collectOrderStatuses()
                ->collectPaymentMethods()
                ->collectShippingProfiles();

            $this->_stockConfigSource->collectWarehouseConfigs();

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
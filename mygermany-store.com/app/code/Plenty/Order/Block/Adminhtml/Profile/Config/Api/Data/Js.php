<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Api\Data;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Plenty\Order\Model\Config\Source;
use Plenty\Core\Model\ResourceModel\Config\Source\CollectionFactory;
use Plenty\Core\Block\Adminhtml\Profile\Config\Api\Data\Modal;

/**
 * Class CollectConfigData
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Api
 */
class Js extends Template
{
    /**
     * @var CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var Json|mixed
     */
    private $_serializer;

    /**
     * Js constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_serializer = $serializer
            ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $data);
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getCollectDataBlockJson()
    {
        $block = $this->_getBlock();
        return $this->_serializer->serialize(
            [
                'html' => $block->toHtml(),
                'url' => $this->_getCollectConfigDataUrl(),
                'trigger_btn' => 'collect-data-btn',
                'is_model_open' => !empty($this->_getModalMessages())
            ]
        );
    }

    /**
     * @return Modal
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _getBlock()
    {
        return $this->getLayout()->createBlock(Modal::class)
            ->setData('modal_messages', $this->_getModalMessages());
    }

    /**
     * @return string
     */
    private function _getCollectConfigDataUrl()
    {
        return $this->getUrl('plenty_order/profile_config/collectData', ['_current' => true]);
    }

    /**
     * @return array
     */
    private function _getModalMessages()
    {
        $messages = [];
        if ($storeData = $this->_getCollectWebStoreDataMessage()) {
            $messages[] = $storeData;
        }
        if ($orderStatusData = $this->_getCollectOrderStatusDataMessage()) {
            $messages[] = $orderStatusData;
        }
        if ($paymentMethodsData = $this->_getCollectPaymentMethodsDataMessage()) {
            $messages[] = $paymentMethodsData;
        }
        if ($shippingMethodsData = $this->_getCollectShippingMethodsDataMessage()) {
            $messages[] = $shippingMethodsData;
        }
        if ($warehouseData = $this->_getCollectWarehouseDataMessage()) {
            $messages[] = $warehouseData;
        }
        return $messages;
    }

    /**
     * @return string|null
     */
    private function _getCollectWebStoreDataMessage() : ?string
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_WEB_STORE);
        if ($collection->getSize()) {
            return false;
        }
        return __('Web store data.');
    }

    /**
     * @return string|null
     */
    private function _getCollectOrderStatusDataMessage() : ?string
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_ORDER_STATUS);
        if ($collection->getSize()) {
            return false;
        }
        return __('Order statuses data.');
    }

    /**
     * @return string|null
     */
    private function _getCollectPaymentMethodsDataMessage() : ?string
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_PAYMENT_METHOD);
        if ($collection->getSize()) {
            return false;
        }
        return __('Payment methods data.');
    }

    /**
     * @return string|null
     */
    private function _getCollectShippingMethodsDataMessage() : ?string
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_SHIPPING_PROFILE);
        if ($collection->getSize()) {
            return false;
        }
        return __('Shipping methods data.');
    }

    /**
     * @return string|null
     */
    private function _getCollectWarehouseDataMessage() : ?string
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addFieldToFilter('config_source', \Plenty\Stock\Model\Config\Source::CONFIG_SOURCE_WAREHOUSE);

        return $collection->getSize()
            ? null
            : __('Warehouse data.');
    }
}
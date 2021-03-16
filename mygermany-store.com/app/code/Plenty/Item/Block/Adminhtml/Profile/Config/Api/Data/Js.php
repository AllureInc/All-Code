<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Api\Data;

use Magento\Backend\Block\Template;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Plenty\Item\Model\Config\Source;
use Plenty\Item\Model\ResourceModel;
use Plenty\Core\Model\ResourceModel\Config\Source\CollectionFactory;
use Plenty\Core\Block\Adminhtml\Profile\Config\Api\Data\Modal;

/**
 * Class Js
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Api\Data
 */
class Js extends Template
{
    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $_requestInterface;

    /**
     * @var Json|mixed
     */
    protected $_serializer;

    /**
     * @var CollectionFactory
     */
    private $_configCollectionFactory;

    /**
     * @var ResourceModel\Import\Category\CollectionFactory
     */
    private $_categoryImportCollectionFactory;

    /**
     * @var ResourceModel\Import\Attribute\CollectionFactory
     */
    private $_attributeImportCollectionFactory;

    /**
     * Js constructor.
     * @param Template\Context $context
     * @param CollectionFactory $configCollectionFactory
     * @param ResourceModel\Import\Attribute\CollectionFactory $attributeImportCollectionFactory
     * @param ResourceModel\Import\Category\CollectionFactory $categoryImportCollectionFactory
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $configCollectionFactory,
        ResourceModel\Import\Attribute\CollectionFactory $attributeImportCollectionFactory,
        ResourceModel\Import\Category\CollectionFactory $categoryImportCollectionFactory,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_requestInterface = $context->getRequest();
        $this->_configCollectionFactory = $configCollectionFactory;
        $this->_attributeImportCollectionFactory = $attributeImportCollectionFactory;
        $this->_categoryImportCollectionFactory = $categoryImportCollectionFactory;
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
        return $this->getUrl('plenty_item/profile_config/collectData', ['_current' => true]);
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
        if ($priceData = $this->_getCollectSalesPriceDataMessage()) {
            $messages[] = $priceData;
        }
        if ($barcodeData = $this->_getCollectBarcodeDataMessage()) {
            $messages[] = $barcodeData;
        }
        if ($warehouseData = $this->_getCollectWarehouseDataMessage()) {
            $messages[] = $warehouseData;
        }
        if ($attributeData = $this->_getCollectAttributeDataMessage()) {
            $messages[] = $attributeData;
        }
        if ($categoryData = $this->_getCollectCategoryDataMessage()) {
            $messages[] = $categoryData;
        }
        return $messages;
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    private function _getCollectWebStoreDataMessage()
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_configCollectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_WEB_STORE);

        return $collection->getSize()
            ? null
            : __('Web store data.');
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    private function _getCollectSalesPriceDataMessage()
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_configCollectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_ITEM_SALES_PRICE);

        return $collection->getSize()
            ? null
            : __('Item sales price data.');
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    private function _getCollectBarcodeDataMessage()
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_configCollectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_ITEM_BARCODE);

        return $collection->getSize()
            ? null
            : __('Item barcode data.');
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    private function _getCollectWarehouseDataMessage()
    {
        /** @var \Plenty\Core\Model\ResourceModel\Config\Source\Collection $collection */
        $collection = $this->_configCollectionFactory->create();
        $collection->addFieldToFilter('config_source', Source::CONFIG_SOURCE_WAREHOUSE);

        return $collection->getSize()
            ? null
            : __('Stock and warehouse data.');
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    private function _getCollectAttributeDataMessage()
    {
        $collection = $this->_attributeImportCollectionFactory->create();
        $collection->addProfileFilter($this->_requestInterface->getParam('id'));

        return $collection->getSize()
            ? null
            : __('Attribute and property data.');
    }

    /**
     * @return \Magento\Framework\Phrase|null
     */
    private function _getCollectCategoryDataMessage()
    {
        $collection = $this->_categoryImportCollectionFactory->create();
        $collection->addProfileFilter($this->_requestInterface->getParam('id'));

        return $collection->getSize()
            ? null
            : __('Category data.');
    }
}
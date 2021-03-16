<?php
/**
 * Copyright © 2018-2019 Cor, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Cor\Pos\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Quote\Api\Data\CartItemExtensionFactory;

class CartItemInterface implements ObserverInterface
{   
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CartItemExtensionFactory
     */
    protected $extensionFactory;
    protected $_quoteItemFactory;
    protected $_itemResourceModel;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param CartItemExtensionFactory $extensionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        CartItemExtensionFactory $extensionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_quoteItemFactory = $quoteItemFactory;
        $this->_itemResourceModel = $itemResourceModel;
        $this->extensionFactory = $extensionFactory;
    }

    public function execute(\Magento\Framework\Event\Observer $observer, string $imageType = NULL)
    {
        $quote = $observer->getQuote();

       /**
         * Code to add the items attribute to extension_attributes
         */
        foreach ($quote->getAllItems() as $quoteItem) {
            $infoBuyRequest = array();

            if ($quoteItem->getProductType() == 'configurable') {
                $product = $quoteItem->getProduct();
                $productAttributes = $product->getTypeInstance()->getConfigurableAttributesAsArray($product);

                foreach ($quoteItem->getOptions() as $option) {
                    if ($option->getCode() == 'info_buyRequest') {
                        $info_buyRequest = json_decode($option->getValue(), true);
                        $super_attributes = $info_buyRequest['super_attribute'];
                        $attributeOptions = array();
                        foreach ($productAttributes as $productAttribute) {
                            $tempAttrOpt = array();
                            if (isset($super_attributes[$productAttribute['attribute_id']])) {
                                $tempAttrOpt['label'] = $productAttribute['frontend_label'];
                                foreach ($productAttribute['options'] as $attrValues) {
                                    if ($attrValues['value'] == $super_attributes[$productAttribute['attribute_id']]) {
                                        $tempAttrOpt['value'] = $attrValues['label'];
                                    }
                                }
                                $tempAttrOpt['option_id'] = $productAttribute['attribute_id'];
                                $tempAttrOpt['option_value'] = $super_attributes[$productAttribute['attribute_id']];
                            }
                            $attributeOptions[] = $tempAttrOpt;
                        }
                        $infoBuyRequest[] = ['info_buyRequest' => $info_buyRequest, 'attributes_info' => $attributeOptions];
                    }
                }
            }

            $itemExtAttr = $quoteItem->getExtensionAttributes();
            if ($itemExtAttr === null) {
                $itemExtAttr = $this->extensionFactory->create();
            }
            $itemExtAttr->setProductOptions($infoBuyRequest);
            $quoteItem->setExtensionAttributes($itemExtAttr);
        }
        return;
    }
}
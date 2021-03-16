<?php
namespace Mangoit\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete\CollectionFactory;
use Webkul\MarketplacePreorder\Model\PreorderSellerRepository as SellerRepository;
use Magento\Framework\Api\DataObjectHelper;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;

class AfterPlacePreOrder extends \Webkul\MarketplacePreorder\Observer\AfterPlaceOrder
{

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_preorderHelper;
        $orderIds = $observer->getEvent()->getData('order_ids');
        $orderId = $orderIds[0];
        $order = $helper->getOrder($orderId);
        $orderedItems = $order->getAllItems();
        foreach ($orderedItems as $item) {
            if ($item->canInvoice() == true) {
                $item->setStatus('pending');
                $item->save();
            }
            $this->setPreorderData($item, $order);
            $this->setPreorderCompleteData($item);
        }
    }
    
    /**
     * Set MarketplacePreorder Price and Data in Table
     *
     * @param object $item
     * @param object $order
     */
    public function setPreorderData($item, $order)
    {
        $helper = $this->_preorderHelper;

        $time = time();
        $customerId = (int) $order->getCustomerId();
        $customerEmail = $order->getCustomerEmail();
        $remainingAmount = 0;
        $preorderPercent = '';
        $parent = ($item->getParentItem() ? $item->getParentItem() : $item);
        $parentId = $parent->getProductId();
        $productId = $item->getProductId();
        $sellerId = $helper->getSellerIdByProductId($productId);
        $preorderType = $helper->getSellerPreorderType($sellerId);
        $quoteItemId = $item->getQuoteItemId();
        if ($parentId == $productId) {
            $parentId = 0;
        }
        if ($helper->isPreorder($productId)) {
            $orderItemId = $item->getId();
            $parentItemId = $item->getParentItemId();
            $qty = $item->getQtyOrdered();
            
            $product = $this->_productFactory->create()->load($productId);
            $price = $parent->getPrice();
            if ($helper->isPartialPreorder($productId)) {
                $preorderPercent = $helper->getPreorderPercent($sellerId);
                $totalPrice = ($price * 100) / $preorderPercent;
                $remainingAmount = $totalPrice - $price;
            }
            $preorderItemData = [
                'seller_id' => $sellerId,
                'order_id' => $order->getId(),
                'item_id' => $orderItemId,
                'product_id' => $productId,
                'parent_id' => $parentId,
                'customer_id' => $customerId,
                'customer_email' => $customerEmail,
                'preorder_percent' => $preorderPercent,
                'paid_amount' => $parent->getPrice(),
                'remaining_amount' => $remainingAmount,
                'qty' => $qty,
                'type' => $preorderType,
                'status' => 0,
                'time' => $this->_date->gmtDate(),
                'tax_class_id' => $product->getTaxClassId()
            ];

            $itemsDataObject = $this->_preorderItemsFactory->create();

            $this->dataObjectHelper->populateWithArray(
                $itemsDataObject,
                $preorderItemData,
                '\Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
            );
            try {
                $this->_itemsRepository->save($itemsDataObject);
                $helper->updatePreorderQty($item);
            } catch (\Exception $e) {
                throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
            }
        }
    }

    public function setParentPreorderStatus($parent_order_id, $order_id)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $order = $objectManager->create('Magento\Sales\Model\Order')->load($parent_order_id);
        $preOrderItem = $objectManager->create('Magento\Sales\Model\Order')->load($order_id);
        $preOrderItem->setShippingMethod($order->getShippingMethod());
        if ($preOrderItem->canInvoice() == true) {
            $preOrderItem->setStatus('pending');
        }
        $preOrderItem->save();
    }

    /**
     * Set Preorder Complete Price and Data in Table
     *
     * @param object $orderItem
     */
    public function setPreorderCompleteData($orderItem)
    {
        $helper = $this->_preorderHelper;
        $quoteItemId = $orderItem->getQuoteItemId();
        $productId = $orderItem->getProductId();
        /* Custom code */
        if (isset($orderItem->getProductOptions()['info_buyRequest']['order_id'])) {
            $order_id = $orderItem->getOrderId();
            $parent_order_id = $orderItem->getProductOptions()['info_buyRequest']['order_id'];
            
        }
        /* Custom code ends */

        $preorderCompleteProductId = $helper->getPreorderCompleteProductId();
        if ($productId == $preorderCompleteProductId) {
            //Set BuyerSpecification Price and Data in Table
            $wkSellerId = 0;            
            $sellerModel = $this->_preorderSellerCollection->create();
            $checkPreorderSpecific = 0;
            $collection = $this->_preorderItemCollection->create()
                        ->addFieldToFilter('status', ['eq' => 0])
                        ->addFieldToFilter('product_id', ['eq' => $productId]);
            
            foreach($collection as $recordPreorderProduct){                
                if(!$recordPreorderProduct->getStatus()){
                    $wkSellerId = $recordPreorderProduct->getSellerId();
                    $sellerModel->addFieldToFilter('seller_id', $wkSellerId);
                    foreach ($sellerModel as $sellerConfig) {
                        if(!$sellerConfig->getPreorderSpecific()){
                            $checkPreorderSpecific = 1;
                            break;
                        }
                    }
                }
            }
            
            if($checkPreorderSpecific == 0){                         
                $wkproduct = $this->productRepository->getById($productId);
                $wkproduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED);
                $this->productRepository->save($wkproduct); //  also save product    
            }
            
            $item = false;
            $id = 0;
            $item = $helper->getPreorderCompleteData('quote_item_id', $quoteItemId, 'eq');
            if ($item) {
                $itemId = $item['order_item_id'];
                $field = 'item_id';
                $item = $helper->getPreorderItemData('item_id', $itemId, 'eq');
                if ($item) {
                    $remainingAmount = $item['remaining_amount'];
                    $paidAmount = $item['paid_amount'];
                    $totalAmount = $paidAmount + $remainingAmount;
                    $updateData = [
                        'id' => $item['id'],
                        'status' => 1,
                        'remaining_amount' => 0,
                        'paid_amount' => $totalAmount
                    ];

                    $itemData = $this->_itemsRepository->getById($item['id']);
                    $savedData = (array) $itemData->getData();
                    $mergeData = array_merge(
                        $savedData,
                        $updateData
                    );
                    
                    $itemsDataObject = $this->_preorderItemsFactory->create();

                    $this->dataObjectHelper->populateWithArray(
                        $itemsDataObject,
                        $mergeData,
                        '\Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterface'
                    );
                    try {
                        $this->_itemsRepository->save($itemsDataObject);
                        /* Custom Code*/
                        if (isset($orderItem->getProductOptions()['info_buyRequest']['order_id'])) {
                            $this->setParentPreorderStatus($parent_order_id, $order_id);
                            # code...
                        }
                        /**/
                    } catch (\Exception $e) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                    }
                }
            }
        }
    }
}

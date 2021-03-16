<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderComplete\CollectionFactory;
use Webkul\MarketplacePreorder\Model\PreorderSellerRepository as SellerRepository;
use Magento\Framework\Api\DataObjectHelper;
use \Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;

class AfterPlaceOrder implements ObserverInterface
{
    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Webkul\MarketplacePreorder\Model\PreorderFactory
     */
    protected $_preorder;

    /**
     * @var Items
     */
    protected $_itemsRepository;

    /**
     * @var CollectionFactory
     */
    protected $_completeCollection;

    /**
     * @var MarketplacePreorder
     */
    protected $_sellerRepository;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var PreorderItemsInterfaceFactory  */

    protected $_preorderItemsFactory;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    protected $productRepository;
    /**
     * @var PreorderItemsCollection
     */
    protected $_preorderItemCollection;
    /**
     * @var PreorderSellerCollection
     */
    protected $_preorderSellerCollection;

    /**
     * @param \Webkul\MarketplacePreorder\Helper\Data $preorderHelper
     * @param ItemsRepository $itemsRepository
     * @param PreorderItemsInterfaceFactory $preorderItemsFactory
     * @param CollectionFactory $completeCollection
     * @param DataObjectHelper $dataObjectHelper
     * @param PreorderItemsCollection                   $preorderItemCollection
     * @param PreorderSellerCollection                  $preorderSellerCollection
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     */
    public function __construct(
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        ItemsRepository $itemsRepository,
        PreorderItemsInterfaceFactory $preorderItemsFactory,
        CollectionFactory $completeCollection,
        DataObjectHelper $dataObjectHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        PreorderItemsCollection $preorderItemCollection,
        PreorderSellerCollection $preorderSellerCollection,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_preorderHelper = $preorderHelper;
        $this->_itemsRepository = $itemsRepository;
        $this->_preorderItemsFactory = $preorderItemsFactory;
        $this->_completeCollection = $completeCollection;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_productFactory = $productFactory;
        $this->_date = $date;
        $this->productRepository = $productRepository;
        $this->_preorderItemCollection = $preorderItemCollection;
        $this->_preorderSellerCollection = $preorderSellerCollection;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_preorderHelper;
        $orderIds = $observer->getEvent()->getData('order_ids');
        $orderId = $orderIds[0];
        $order = $helper->getOrder($orderId);
        $orderedItems = $order->getAllItems();
        foreach ($orderedItems as $item) {
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
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger = $objectManager->create('Psr\Log\LoggerInterface');
        $logger->info('######## Preorder Checking ###########');

        if ($helper->isPreorder($productId)) {
            $logger->info('###### Product is available for preorder #######');
            $orderItemId = $item->getId();
            $logger->info('###### preorder: orderItemId:'.$orderItemId);
            $parentItemId = $item->getParentItemId();
            $qty = $item->getQtyOrdered();
            $logger->info('###### preorder: qty:'.$qty);
            
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
        } else {
            $logger->info('###### Product is available for preorder #######');
            return false;
        }
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
                    } catch (\Exception $e) {
                        throw new \Magento\Framework\Exception\LocalizedException(__($e->getMessage()));
                    }
                }
            }
        }
    }
}

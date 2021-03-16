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
use Magento\Framework\App\RequestInterface;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;
use Webkul\MarketplacePreorder\Model\PreorderItemsRepository as ItemsRepository;
use Webkul\MarketplacePreorder\Api\Data\PreorderItemsInterfaceFactory;
use Magento\Framework\Api\DataObjectHelper;
use \Magento\Catalog\Api\ProductRepositoryInterface;

class AfterSaveProduct implements ObserverInterface
{
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Email
     */
    protected $_emailHelper;

    /**
     * @var PreorderItemsCollection
     */
    protected $_preorderItemCollection;
    /**
     * @var PreorderSellerCollection
     */
    protected $_preorderSellerCollection;

    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    protected $_orderFactory;

    /**
     * @var Items
     */
    protected $_itemsRepository;

    /** @var DataObjectHelper  */
    protected $dataObjectHelper;

    /** @var PreorderItemsInterfaceFactory  */

    protected $_preorderItemsFactory;

    /**
     * @var  \Webkul\MarketplacePreorder\Model\Mapper
     */
    protected $_itemMapper;

    /**
     * @var  \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $_product;

     /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface 
     */
    protected $_stockRegistry; 
    
    protected $productRepository;

    /**
     *
     * @param RequestInterface                          $request
     * @param \Webkul\MarketplacePreorder\Helper\Data   $preorderHelper
     * @param \Webkul\MarketplacePreorder\Helper\Email  $emailHelper
     * @param PreorderItemsCollection                   $preorderItemCollection
     * @param PreorderSellerCollection                   $preorderSellerCollection
     * @param \Magento\Sales\Model\OrderFactory         $order
     * @param ItemsRepository                           $itemsRepository
     * @param PreorderItemsInterfaceFactory             $preorderItemsFactory
     * @param DataObjectHelper                          $dataObjectHelper
     * @param \Webkul\MarketplacePreorder\Model\Mapper  $mapper
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        \Webkul\MarketplacePreorder\Helper\Email $emailHelper,
        PreorderItemsCollection $preorderItemCollection,
        PreorderSellerCollection $preorderSellerCollection,
        \Magento\Sales\Model\OrderFactory $order,
        ItemsRepository $itemsRepository,
        PreorderItemsInterfaceFactory $preorderItemsFactory,
        DataObjectHelper $dataObjectHelper,
        \Webkul\MarketplacePreorder\Model\Mapper $mapper,
        \Magento\Catalog\Model\Product $product,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_request = $request;
        $this->_orderFactory = $order;
        $this->_preorderItemCollection = $preorderItemCollection;
        $this->_preorderSellerCollection = $preorderSellerCollection;
        $this->_preorderHelper = $preorderHelper;
        $this->_emailHelper = $emailHelper;
        $this->_itemsRepository = $itemsRepository;
        $this->_preorderItemsFactory = $preorderItemsFactory;
        $this->dataObjectHelper = $dataObjectHelper;
        $this->_itemMapper = $mapper;
        $this->_product = $product;
        $this->productRepository = $productRepository;
        $this->_stockRegistry = $stockRegistry;
        $this->_objectManager = $objectManager;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_preorderHelper;
        $emailArray = [];
        $notifyArray= [];
        $product = $observer->getEvent()->getProduct();
        if (!$product) {
            $data = $observer->getEvent()->getData();
            $product = $this->_preorderHelper->getProduct($data[0]['id']);
        }
        $storeId = $product->getStoreId();
        $stockDetail = $helper->getStockDetails($product->getId());
        $qty = $stockDetail['qty'];
        $productType = $product->getdata('type_id');
        $preorderAttribute = $product->getResource()->getAttribute('wk_marketplace_preorder');
        $preorderStatus = $preorderAttribute->getSource()->getOptionId('Disable');
    
        $sellerId = $helper->getSellerIdByProductId($product->getId());

        $data = $this->_request->getParams();
        $isInStock = 0;
        
        $wkSellerId = 0;
        
        $sellerModel = $this->_preorderSellerCollection->create();
        $checkPreorderSpecific = 0;
        $wkCollection = $this->_preorderItemCollection->create()
                    ->addFieldToFilter('status', ['eq' => 0])
                    ->addFieldToFilter('notify', ['eq' => 0])
                    ->addFieldToFilter('product_id', ['eq' => $product->getId()]);
        foreach($wkCollection as $recordPreorderProduct){            
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
        
        if($checkPreorderSpecific == 1){                         
                $wkproduct = $this->productRepository->getById($product->getId());
                $wkproduct->setStatus(\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED);
                $this->productRepository->save($wkproduct); //  also save product    
        }
        if (isset($data['product'])) {
            $stockData = $data['product']['quantity_and_stock_status'];
            if (array_key_exists('is_in_stock', $stockData)) {
                $isInStock = $stockData['is_in_stock'];
            }
            if ($isInStock == 1) {
                $collection = $this->_preorderItemCollection->create()
                    ->addFieldToFilter('status', ['eq' => 0])
                    ->addFieldToFilter('notify', ['eq' => 0])
                    ->addFieldToFilter('product_id', ['eq' => $product->getId()]);
                    
                foreach ($collection as $item) {

                    $invoice = $this->checkInvoice($item->getOrderId(), $item->getItemId());
                    
                    if ($item->getProductId() == $product->getId()) {
                       
                        if ($item->getType() == 0) {
                            $updateData = [
                                'status' => 1,
                            ];
                            $this->updatePreorderItem($item->getId(), $updateData);
                        }

                        $emailArray[] = $item->getCustomerEmail();
                        $notifyArray[] = $item->getId();
                        
                        if ($checkPreorderSpecific == 1 && $helper->getSellerPreorderSpecification($item->getSellerId())) {
                            $helper->setProductDisabled($product->getId(), $storeId);
                        } else {
                            $helper->setProductEnabled($product->getId(), $storeId);
                        }
                    }
                }
                $emailArray = array_unique($emailArray);
                $notifyArray = array_unique($notifyArray);
                if ($this->_emailHelper->isAutoEmail($sellerId)) {
                    $this->_emailHelper->sendNotifyEmail($emailArray, $product->getName());
                    foreach ($notifyArray as $temId) {
                        $updateData = [
                                'notify' => 1
                            ];
                        $this->updatePreorderItem($temId, $updateData);
                    }
                }
            } else {
                $collection = $this->_preorderItemCollection->create()
                    ->addFieldToFilter('status', ['eq' => 0])
                    ->addFieldToFilter('notify', ['eq' => 1]);

                foreach ($collection as $item) {
                    if ($item->getProductId() == $product->getId()) {
                        $updateData = [
                                'notify' => 0
                            ];
                        $this->updatePreorderItem($item->getId(), $updateData);
                    }
                }
            }
        }
    }

    public function updatePreorderItem($itemId, $updatedData)
    {
        $itemData = $this->_itemsRepository->getById($itemId);
        $savedData = (array) $itemData->getData();

        $itemsDataObject = $this->_preorderItemsFactory->create();

        $mergeData = array_merge(
            $savedData,
            $updatedData
        );
        
        $mergeData['id'] = $itemId;
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

    public function checkInvoice($orderId, $orderItemId)
    {
        $order = $this->_orderFactory->create()->load($orderId);
        $invoice = 0;
        if ($order->hasInvoices()) {
            $invQty = 0;
            foreach ($order->getInvoiceCollection() as $inv) {
                $invoicedItems = $inv->getItemsCollection()->addAttributeToFilter(
                    'order_item_id',
                    $orderItemId
                )->getData();
                if (count($invoicedItems) > 0) {
                    foreach ($invoicedItems as $invItem) {
                        if ($orderItemId == $invItem['order_item_id']) {
                            $invQty += intval($invItem['qty']);
                            $invoice = $invQty;
                        }
                    }
                }
            }
            if (!isset($invoice) || $invoice<=0) {
                $invoice = 0;
            }
        } else {
            $invoice = 0;
        }
        return $invoice;
    }
}

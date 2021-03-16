<?php

namespace Mangoit\VendorPayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Mangoit VendorPayments SalesOrderSaveAfterObserver Observer.
 */
class SalesOrderSaveAfterObserver implements ObserverInterface
{

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    protected $quoteFactory;


    /**
     * @param Filesystem                                       $filesystem,
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager,
     * @param \Magento\Framework\Stdlib\DateTime\DateTime      $date,
     * @param \Magento\Framework\Message\ManagerInterface      $messageManager,
     * @param \Magento\Store\Model\StoreManagerInterface       $storeManager,
     * @param \Magento\Catalog\Api\ProductRepositoryInterface  $productRepository,
     * @param CollectionFactory                                $collectionFactory,
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param ProductCollection                                $sellerProduct
     * @param \Magento\Framework\Json\DecoderInterface         $jsonDecoder
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\Quote\Model\ResourceModel\Quote\Item $itemResourceModel,
        \Magento\Quote\Model\QuoteFactory $quoteFactory,
        CollectionFactory $collectionFactory
    ) {
        $this->_objectManager = $objectManager;
        $this->_collectionFactory = $collectionFactory;
        $this->quoteItemFactory = $quoteItemFactory;
        $this->itemResourceModel = $itemResourceModel;
        $this->quoteFactory = $quoteFactory;
    }

    /**
     * admin customer save after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder(); 
        $this->processPreOrderSave($order);
        return $this;
    }

    public function processPreOrderSave($order){
        $preorderHelper = $this->_objectManager->get('\Webkul\MarketplacePreorder\Helper\Data');
        $orderedItems = $order->getAllItems();

        foreach ($orderedItems as $item) {

            if ($preorderHelper->isPreorderOrderedItem($order->getId())) {
                $orderItemId = $item->getOrderItemId();

                $itemPreorderComplete = $preorderHelper->getPreorderCompleteData('order_id', $order->getId(), 'eq');

                $itemId = $itemPreorderComplete['quote_item_id'];
                $quoteItem = $this->quoteItemFactory->create();
                $quoteItem1 = $this->itemResourceModel->load($quoteItem, $itemId);
                $cmlptOrdrQutId = $quoteItem->getQuoteId();

                $cmplorder = $this->_objectManager->create('Magento\Sales\Model\Order')->load($cmlptOrdrQutId,'quote_id');
                $cmplorder->setStatus($order->getStatus());
                $cmplorder->setState($order->getState());
                $cmplorder->save();
            }
        }
    }
}

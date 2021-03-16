<?php

namespace Mangoit\VendorPayments\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

/**
 * Mangoit VendorPayments CheckoutOnepageControllerSuccessObserver Observer.
 */
class CheckoutOnepageControllerSuccessObserver implements ObserverInterface
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
        $orderIds = $observer->getEvent()->getData('order_ids');
        $orderId = $orderIds[0];
        $preorderHelper = $this->_objectManager->get('\Webkul\MarketplacePreorder\Helper\Data');
        $order = $preorderHelper->getOrder($orderId);
        $this->processPreOrderCompelte($order);
        return $this;
    }

    public function processPreOrderCompelte($order){
        $orderedItems = $order->getAllItems();
        // $preorderHelper = $this->_objectManager->get('\Webkul\MarketplacePreorder\Helper\Data');
        foreach ($orderedItems as $item) {
            if ($item->getSku() == 'preorder_complete') {
                $options = $item->getProductOptions();
                if ($options) {
                    if (isset($options['options'])) {
                        foreach ($options['options'] as $opt) {
                            if($opt["label"] == "Order Refernce") {
                                $orderRepository = $this->_objectManager->get('\Magento\Sales\Api\Data\OrderInterface');

                                $orderIncrementId = (int) str_replace('#', '', $opt["value"]);
                                $preOrder = $orderRepository->loadByIncrementId($orderIncrementId);

                                $order->setStatus($preOrder->getStatus());
                                $order->setState($preOrder->getState());
                                $order->save();
                            }
                        }
                    }
                }
            }
        }
    }
    
}

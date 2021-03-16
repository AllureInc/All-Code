<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Orderdispatch\Helper;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_request;    
    protected $_storeManager;    
    protected $_cart;    
    protected $_productloader;  
    protected $_customerSession;  
    protected $orderRepository;
    protected $_seller;
    protected $statusCollectionFactory;
    protected $sellerOrder;
    protected $_productModel;
    protected $_customerModel;
    
    public function __construct (
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart ,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Magento\Catalog\Model\Product $_productModel,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Customer\Model\Customer $customerModel,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Webkul\Marketplace\Model\Seller $_seller,
        \Magento\Sales\Model\ResourceModel\Order\Status\CollectionFactory $statusCollectionFactory,
        \Webkul\Marketplace\Model\Orders $sellerOrder,
        array $data = []
    ) {   
        $this->_request = $request;
        $this->_storeManager = $storeManager;   
        $this->_cart = $cart;  
        $this->_productloader = $_productloader;
        $this->_customerSession = $_customerSession;
        $this->orderRepository = $orderRepository;
        $this->_seller = $_seller;
        $this->statusCollectionFactory = $statusCollectionFactory;
        $this->sellerOrder = $sellerOrder;
        $this->_productModel = $_productModel;
        $this->_customerModel = $customerModel;
        parent::__construct($context);
    }

    public function getControllerModule()
    {
         return $this->_request->getControllerModule();
    }
        
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }
        
    public function getRouteName()
    {
         return $this->_request->getRouteName();
    }
        
    public function getActionName()
    {
         return $this->_request->getActionName();
    }
        
    public function getControllerName()
    {
         return $this->_request->getControllerName();
    }
        
    public function getModuleName()
    {
         return $this->_request->getModuleName();
    }   

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    }   

    public function getOrderStatus($orderId)
    {
        $status = array();
        $orderStatus = $this->orderRepository->get($orderId)->getStatus();
        // $status['order_processed'] = 'Order Processed';
        // return $status;
        if ($orderStatus == 'compliance_check') {
            $status;
        } elseif ($orderStatus == 'processing') {
        /*} elseif ($orderStatus == 'pending') {*/
            $status['order_processed'] = __('Ship Order');
        }
        return $status;
    }

    public function getVendorDetails()
    {
        if (!isset($_SESSION['customer']['customer_id'])) {
            $seller_id = null;
        } else {
            $seller_id = $_SESSION['customer']['customer_id'];            
        }

        $sellerObj = $this->_seller->load($seller_id,'seller_id');
        return $sellerObj;
    }
    
    public function getAllOrderStatuses()
    {
        $options = $this->statusCollectionFactory->create()->toOptionArray(); 
        return $options;
        // echo "<pre>"; print_r($options);
        // die('died');
        $orderId = $this->_request->getParam('order_id');
        $orderState = $this->orderRepository->get($orderId)->getState();
        // if ($orderState == 'new') {
        //     $status['new'] = 'Pending'; 
        // }
        unset($status[$orderState]);
        $status['processing'] = __('Processing'); 
        $status['received'] = __('Received'); 
        $status['order_verified'] = __('Order Verified'); 
        $status['closed'] = __('Closed'); 
        $status['complete'] = __('Complete'); 
        $status['fraud'] = __('Suspected Fraud'); 
        $status['holded'] = __('On Hold'); 
        $status['canceled'] = __('Canceled'); 

        return $status;
    }

    public function getOrder()
    {
        $orderId = $this->_request->getParam('order_id');
        $order = $this->orderRepository->get($orderId);
        return $order;
    }

    public function getSellerOrderData($orderId)
    {
        $sellerOrderData = $this->sellerOrder->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderId]
            )
            ->addFieldToFilter(
                'seller_id',
                ['eq' => $_SESSION['customer']['customer_id']]
            )
            ->getFirstItem();
        return $sellerOrderData;
    }

    public function getVendorName()
    {
        return $this->_customerModel->load($_SESSION['customer']['customer_id']);
    }


    public function getProductDimension($id)
    {
        $product = $this->_productModel->load($id);
        $dimention = $product->getMygmbhShippingProductLength() * $product->getMygmbhShippingProductWidth() * $product->getMygmbhShippingProductHeight();
        return $dimention;
    }
}
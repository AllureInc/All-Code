<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

namespace Mangoit\RakutenConnector\Block\Order;

/*
 * Webkul MpEtsyMagentoConnect Order History Block
 */
use Magento\Sales\Model\Order;
use Magento\Customer\Model\Customer;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory;

class History extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Customer\Model\Customer
     */
    public $customer;

    /**
     * @var \Magento\Sales\Model\Order
     */
    public $order;

    /**
     * @var ObjectManagerInterface
     */
    public $_objectManager;

    /**
     * @var Session
     */
    public $_customerSession;

    /**
     * @var CollectionFactory
     */
    public $_orderCollectionFactory;

    /** @var \Magento\Catalog\Model\Product */
    public $salesOrderLists;

    /** @var \Magento\Sales\Model\OrderRepository */
    public $orderRepository;

    /** @var \Magento\Catalog\Model\ProductRepository */
    public $productRepository;
    public $logger;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\ObjectManagerInterface        $objectManager
     * @param Order                                            $order
     * @param Customer                                         $customer
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param CollectionFactory                                $orderCollectionFactory
     * @param \Magento\Sales\Model\OrderRepository             $orderRepository
     * @param \Magento\Catalog\Model\ProductRepository         $productRepository
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        Order $order,
        Customer $customer,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \Magento\Catalog\Model\ProductRepository $productRepository,
        array $data = []
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->Customer = $customer;
        $this->Order = $order;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->logger = $this->getLogger();
        parent::__construct($context, $data);
    }

    /**
     */
    public function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Orders'));
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }

    /**
     * @return bool|\Webkul\Marketplace\Model\ResourceModel\Saleslist\Collection
     */
    public function getAllSalesOrder()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        if (!$this->salesOrderLists) {
            $paramData = $this->getRequest()->getParams();
            $filterOrderid = '';
            $filterOrderstatus = '';
            $filterDataTo = '';
            $filterDataFrom = '';
            $from = null;
            $to = null;

            if (isset($paramData['s'])) {
                $filterOrderid = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            if (isset($paramData['orderstatus'])) {
                $filterOrderstatus = $paramData['orderstatus'] != '' ? $paramData['orderstatus'] : '';
            }
            if (isset($paramData['from_date'])) {
                $filterDataFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
            }
            if (isset($paramData['to_date'])) {
                $filterDataTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
            }

            $orderids = $this->getOrderIdsArray($customerId, $filterOrderstatus);

            $ids = $this->getEntityIdsArray($orderids);

            $collection = $this->_orderCollectionFactory->create()->addFieldToSelect(
                '*'
            )
            ->addFieldToFilter(
                'entity_id',
                ['in' => $ids]
            )
            ->addFieldToFilter(
                'is_rakuten',
                ['eq' => 1]
            );

            if ($filterDataTo) {
                $todate = date_create($filterDataTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }
            if ($filterDataFrom) {
                $fromdate = date_create($filterDataFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            if ($filterOrderid) {
                $collection->addFieldToFilter(
                    'magerealorder_id',
                    ['eq' => $filterOrderid]
                );
            }

            $collection->addFieldToFilter(
                'created_at',
                ['datetime' => true, 'from' => $from, 'to' => $to]
            );

            $collection->setOrder(
                'created_at',
                'desc'
            );
            $collection->getSellerOrderCollection();
            $this->salesOrderLists = $collection;
        }

        return $this->salesOrderLists;
    }

    public function getOrderIdsArray($customerId = '', $filterOrderstatus = '')
    {
        $orderids = [];

        $collectionOrders = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter(
            'seller_id',
            ['eq' => $customerId]
        )
        ->addFieldToFilter(
            'is_rakuten',
            ['eq' => 1]
        )
        ->addFieldToSelect('order_id')
        ->distinct(true);

        foreach ($collectionOrders as $collectionOrder) {
            $tracking = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Orders'
            )->getOrderinfo($collectionOrder->getOrderId());

            if ($tracking) {
                if ($filterOrderstatus) {
                    if ($tracking->getIsCanceled()) {
                        if ($filterOrderstatus == 'canceled') {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    } else {
                        $tracking = $this->orderRepository->get($collectionOrder->getOrderId());
                        if ($tracking->getStatus() == $filterOrderstatus) {
                            array_push($orderids, $collectionOrder->getOrderId());
                        }
                    }
                } else {
                    array_push($orderids, $collectionOrder->getOrderId());
                }
            }
        }

        return $orderids;
    }

    public function getEntityIdsArray($orderids = [])
    {
        $ids = [];
        foreach ($orderids as $orderid) {
            $collectionIds = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter(
                'order_id',
                ['eq' => $orderid]
            )
            ->addFieldToFilter(
                'is_rakuten',
                ['eq' => 1]
            )
            ->addFieldToFilter('parent_item_id', ['null' => 'true'])
            ->setOrder('entity_id', 'DESC')
            ->setPageSize(1);
            foreach ($collectionIds as $collectionId) {
                $autoid = $collectionId->getId();
                array_push($ids, $autoid);
            }
        }

        return $ids;
    }

    /**
     * @return $this
     */
    public function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllSalesOrder()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'marketplace.dashboard.pager'
            )
            ->setCollection(
                $this->getAllSalesOrder()
            );
            $this->setChild('pager', $pager);
            $this->getAllSalesOrder()->load();
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    public function getCurrentUrl()
    {
        return $this->_urlBuilder->getCurrentUrl(); // Give the current url of recently viewed page
    }

    public function getpronamebyorder($orderId)
    {
        $orderHelper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Orders'
        );
        $sellerId = $this->getCustomerId();
        $collection = $this->_orderCollectionFactory->create()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        )
        ->addFieldToFilter(
            'order_id',
            $orderId
        )
        ->addFieldToFilter(
            'is_rakuten',
            ['eq' => 1]
        )
        ->addFieldToFilter('parent_item_id', ['null' => 'true']);
        $productName = '';
        foreach ($collection as $res) {
            if ($res->getParentItemId()) {
                continue;
            }
            $productName = $orderHelper->getOrderedProductName($res, $productName);
        }

        return $productName;
    }

    public function getPricebyorder($orderId)
    {
        $this->logger->info(' -- getPricebyorder --');
        $this->logger->info(' -- Order ID: '.$orderId.' --');
        $sellerId = $this->getCustomerId();
        $collection = $this->saleslistModel->create()
                      ->getCollection()
                      ->addFieldToFilter(
                          'main_table.seller_id',
                          $sellerId
                      )->addFieldToFilter(
                          'main_table.order_id',
                          $orderId
                      )->getPricebyorderData();
        $name = '';
        $actualSellerAmount = 0;
        foreach ($collection as $coll) {
            // calculate order actual_seller_amount in base currency
            $appliedCouponAmount = $coll['applied_coupon_amount']*1;
            $this->logger->info(' -- appliedCouponAmount: '.$appliedCouponAmount.' --');

            $shippingAmount = $coll['shipping_charges']*1;
            $this->logger->info(' -- shippingAmount: '.$shippingAmount.' --');

            $refundedShippingAmount = $coll['refunded_shipping_charges']*1;
            $this->logger->info(' -- refundedShippingAmount: '.$refundedShippingAmount.' --');

            $totalshipping = $shippingAmount - $refundedShippingAmount;
            $this->logger->info(' -- totalshipping: '.$totalshipping.' --');

            if ($coll['tax_to_seller']) {
                $vendorTaxAmount = $coll['total_tax']*1;
                $this->logger->info(' -- vendorTaxAmount: '.$vendorTaxAmount.' --');
            } else {
                $vendorTaxAmount = 0;
                $this->logger->info(' -- vendorTaxAmount: '.$vendorTaxAmount.' --');
            }

            if ($coll['actual_seller_amount'] * 1) {
                $taxShippingTotal = $vendorTaxAmount + $totalshipping - $appliedCouponAmount;
                $this->logger->info(' -- taxShippingTotal: '.$taxShippingTotal.' --');
                $actualSellerAmount += $coll['actual_seller_amount'] + $taxShippingTotal;
                $this->logger->info(' -- actualSellerAmount: '.$actualSellerAmount.' --');
            } else {
                if ($totalshipping * 1) {
                    $actualSellerAmount += $totalshipping - $appliedCouponAmount;
                     $this->logger->info(' -- actualSellerAmount: '.$actualSellerAmount.' --');
                }
            }
        }
        $this->logger->info(' ');

        return $actualSellerAmount;
    }

    public function getOrderedPricebyorder($currencyRate, $basePrice)
    {
        return $basePrice * $currencyRate;
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/History.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;      
    }

    public function getMainOrder($orderId)
    {
        $sellerId = $this->getCustomerId();
        $collection = $this->_objectManager->create(
            'Magento\Sales\Model\Order'
        )->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['eq' => $orderId]
        );
        foreach ($collection as $res) {
            return $res;
        }

        return [];
    }
}

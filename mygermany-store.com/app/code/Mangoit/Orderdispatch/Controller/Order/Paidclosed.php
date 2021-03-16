<?php
namespace Mangoit\Orderdispatch\Controller\Order;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class Paidclosed extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderObj;
    protected $customerSession;
    public $_storeManager;
    public $salesOrderGrid;
    private $_order;
    protected $salesList;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Sales\Model\Order $orderObj,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\Orderdispatch\Model\SalesOrderGrid $salesOrderGrid,
        \Magento\Sales\Model\Order $order,
        \Webkul\Marketplace\Model\Saleslist $salesList,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->orderObj = $orderObj;
        $this->_storeManager=$storeManager;
        $this->salesOrderGrid = $salesOrderGrid;
        $this->_order = $order;
        $this->salesList = $salesList;
        parent::__construct($context);
    }


    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (!empty($params)) {
            if (!empty($params['id'])) {
                $orderData = $this->_order->load($params['id']);
                if ($orderData->getId()) {
                    $sellerId = $this->customerSession->getCustomer()->getId();
                    $salesListColl = $this->salesList->getCollection()
                        ->addFieldToFilter('seller_id',['eq'=> $sellerId])
                        ->addFieldToFilter('order_id',$params['id']);
                    $isPaid = 1;
                    foreach ($salesListColl as $salesListkey => $salesListValue) {
                        if (!$salesListValue->getPaidStatus()) {
                            $isPaid = 0;
                        }
                    }
                    if ($isPaid) {
                        $this->_order->load($params['id'])->setState('closed')->setStatus('closed');
                        $this->_order->addStatusToHistory('closed', NULL)->save();
                    } else {
                        $this->messageManager->addErrorMessage(__('You cannot update unpaid order status!')); 
                    }
                } else {
                    $this->messageManager->addErrorMessage(__('Order not found!')); 
                }
            } else {
                $this->messageManager->addErrorMessage(__('You are not allowed to edit this order!'));                
            }
           
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/view',
                ['id' => $params['id']],
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/history',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
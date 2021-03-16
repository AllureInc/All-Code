<?php
namespace Mangoit\Orderdispatch\Controller\Order;

use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;

class Status extends \Magento\Framework\App\Action\Action
{
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderObj;
    protected $customerSession;
    protected $_emailNotification;
    public $_storeManager;
    public $salesOrderGrid;
    private $_order;
    private $sellerOrder;
    private $mpHelper;
    protected $_packagingSlip;
    protected $_logger;
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
        \Webkul\Marketplace\Model\Orders $sellerOrder,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Mangoit\Orderdispatch\Helper\OrderStatusNotification $emailNotification,
        \Mangoit\Orderdispatch\Helper\PackagingSlip $packagingSlip,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->orderObj = $orderObj;
        $this->_storeManager=$storeManager;
        $this->salesOrderGrid = $salesOrderGrid;
        $this->_order = $order;
        $this->sellerOrder = $sellerOrder;
        $this->mpHelper = $mpHelper;
        $this->_emailNotification = $emailNotification;
        $this->_packagingSlip = $packagingSlip;
        $this->_logger = $this->getLogger();
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
        $postData = $this->getRequest()->getPost()->toArray();
        $params = $this->getRequest()->getParams();
        $marketplaceModel = $this->sellerOrder;

        if (!empty($postData)) {
            if (!empty($postData['mis_statuses'])) {
                $trackingIdExist = $this->_order->load($postData['mis_tracking_id'],'vendor_tracking_id');
                if (!empty($trackingIdExist->getData())) {
                    $this->messageManager->addErrorMessage(__('Tracking Id already exist!')); 
                } else{
                    try {
                        /* For creating and email packaging slip */
                        $this->_packagingSlip->getPdfHtmlContent($postData['mis_order_id'], $postData['mis_tracking_id'], 'OWN');
                    } catch (Exception $e) {
                        $this->messageManager->addSuccessMessage(__('Something went wrong while generating the packaging slip. Please contact to administrator.')); 
                        $this->_logger->info('Exception for Order ID: '.$postData['mis_order_id']);
                        $this->_logger->info($e->getMessage());
                    }

                    $orderData = $this->_order->load($postData['mis_order_id']);

                    $preTrackIds = $orderData->getVendorTrackingId();
                    $newTrackIds = trim($preTrackIds . ', ' . $postData['mis_tracking_id'], ', ');

                    $sellerOrderData = $this->sellerOrder->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $postData['mis_order_id']]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $this->getSellerId()]
                        )
                        ->getFirstItem();

                    $sellerData = $this->mpHelper->getSellerDataBySellerId($this->getSellerId())->getData();
                    $sellerData = (isset($sellerData[0])) ? $sellerData[0] : $sellerData;
                    $sellerName = (isset($sellerData['name'])) ? $sellerData['name'] : '';

                    $salesOrderObj = $this->salesOrderGrid->load($postData['mis_order_id']);
                    if (!empty($salesOrderObj->getData())) {
                        $salesOrderObj->setVendorTrackingId($newTrackIds)->save();

                        $remainingItems = $this->sellerOrder->getCollection()
                            ->addFieldToFilter(
                                'order_id',
                                ['eq' => $postData['mis_order_id']]
                            )
                            ->addFieldToFilter(
                                'tracking_number',
                                ['null' => true]
                            );

                        if($remainingItems->getSize() == 1) {
                            $remainingItem = $remainingItems->getFirstItem();
                            if($remainingItem->getSellerId() == $this->getSellerId()) {
                                $orderData->setState($postData['mis_statuses'])->setStatus($postData['mis_statuses']);
                                $orderData->addStatusToHistory($postData['mis_statuses'], NULL);
                            }
                        }

                        $marketplaceOrder = $marketplaceModel->load($postData['mis_order_id']);

                        $msg = 'Seller "' . $sellerName . '" sent items to myGermany. Tracking Id: '. $postData['mis_tracking_id'];
                        $orderData->addStatusToHistory(false, $msg);
                        $orderData->setVendorShippedBy('OWN');     
                        $orderData->setVendorTrackingId($newTrackIds)->save();
                        $sellerOrderData->setTrackingNumber($postData['mis_tracking_id'])->save();

                        $increment_id = $orderData->getIncrementId();
                        $seller_id = $this->getSellerId();
                        // $salesOrderObj->setVendorTrackingId($postData['mis_tracking_id'])->save();
                        $this->_emailNotification->sendNotifications($increment_id, $seller_id, 'OWN');

                        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
                        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
                        $connection = $resource->getConnection();
                        $tableName = $resource->getTableName('marketplace_orders');
                        $sql = "Update `" . $tableName . "` Set `vendor_shipped_by` = 'OWN' where `order_id` = ".$postData['mis_order_id'];
                        $connection->query($sql);

                        $this->messageManager->addSuccessMessage(__('Order status successfully updated and we have mailed the packaging slip to your registered email ID. Plese check your email. You can download the packaging slip by clicking on "Print Packaging Slip" Button. Thank you !!!')); 
                    } else{
                        $this->messageManager->addSuccessMessage(__('Please try to update order after some time.')); 
                    }
                }
            } else {
                $this->messageManager->addErrorMessage(__('Please select order status to update!')); 
            }
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/view',
                ['id' => $postData['mis_order_id']],
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } elseif (isset($params['id']) && (isset($params['status']))) {

            $this->_order->load($params['id'])->setState('closed')->setStatus('closed');
            $this->_order->addStatusToHistory('closed', NULL)->save();

            $this->messageManager->addErrorMessage(__('Please select order status to update!')); 
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/view',
                ['id' => $params['id']],
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else{
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/order/history',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function getSellerId()
    {
        return $this->customerSession->getCustomerId();
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/Own_Packaging_Slip.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}
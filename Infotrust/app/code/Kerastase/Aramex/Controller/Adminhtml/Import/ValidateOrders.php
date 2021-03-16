<?php


namespace Kerastase\Aramex\Controller\Adminhtml\Import;


class ValidateOrders  extends \Magento\Backend\App\Action
{
    const ORDER_STATUS_COMPLETE = 'complete';
    const ORDER_STATUS_DELIVERED = 'processing_delivered';

    private $resultJsonFactory;
    /**
     * @var \Kerastase\Aramex\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Sales\Model\Order
     */
    private $order;

    public function __construct(
	    // \Magento\Framework\App\Action\Context 
	\Magento\Backend\App\Action\Context $context,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Kerastase\Aramex\Logger\Logger $logger,
        \Magento\Sales\Model\Order $order
    )
    {
        parent::__construct($context);
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->order = $order;
    }

    public function execute()
    {
        $this->logger->info('########### Validate Orders ##############"');
        $message = "";
        $error = false;
        try{
            $result = $this->getRequest()->getPostValue();
            $resultString = json_encode($result);
            $json = json_decode($resultString, true);
            $completedOrders = $json['completed'];
            $deliveredOrders = $json['delivered'];
            $completedOrdersArray = explode(",",$completedOrders);
            $deliveredOrdersArray = explode(",",$deliveredOrders);
            foreach ($completedOrdersArray as $completed){
                $orderId = $completed;
                $completeOrder = $this->order->load($orderId);
                $completeOrder->setStatus(SELF::ORDER_STATUS_COMPLETE);
                $comment = '#Order completed in Aramex with same paid amount (status changed from admin import Order section) ';
                $completeOrder->addStatusHistoryComment($comment, false);
                $completeOrder->save();
            }
            foreach ($deliveredOrdersArray as $delivered){
                $orderId = $delivered;
                $deliverOrder = $this->order->load($orderId);
                $deliverOrder->setStatus(SELF::ORDER_STATUS_DELIVERED);
                $comment = '# Order Delivered from Aramex (status changed from admin import Order section)';
                $deliverOrder->addStatusHistoryComment($comment, false);
                $deliverOrder->save();
            }
        }catch(\Exception $ex){
            $this->logger->info($ex);
        }
        return  $this->resultJsonFactory->create()->setData(['error'=>$error,'message'=>$message]);
    }
}

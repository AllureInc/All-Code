<?php

namespace Cor\Pos\Model\Sales\Order;

use Cor\Pos\Api\Sales\Order\OrderInterface;
use \Magento\Sales\Model\Order as OrderFactory;
use \Magento\Sales\Model\Order\Item as OrderItemFactory;

/**
 * Class OrderManagement
 */
class OrderManagement implements OrderInterface
{
    /**
     * Request
     *
     * @var \Magento\Framework\App\RequestInterface
     */
    protected $_request;

    /**
     * Request
     *
     * @var \Magento\Sales\Model\Order
     */
    protected $_order;

    /**
     * Request
     *
     * @var \Magento\Sales\Model\Order\Item
     */
    protected $_orderItem;

    /**
     * @param \Magento\Framework\App\RequestInterface $request
     */
    public function __construct(
        \Magento\Framework\App\RequestInterface $request,
        OrderFactory $orderFactory,
        OrderItemFactory $orderItemFactory
    ) {
        $this->_request = $request;
        $this->_orderFactory = $orderFactory;
        $this->_orderItemFactory = $orderItemFactory;
    }

    /**
     * List all artists
     * @api
     * @return string
     */
    public function pickup() {
        $orderNumber = $this->_request->getParam('order_id');
        $orderItemIds = $this->_request->getParam('order_item');

        if (!empty($orderNumber) && !empty($orderItemIds)) {

            $order = $this->_orderFactory->load($orderNumber, 'increment_id');
            $orderId = $order->getEntityId();

            $count = 0;
            $pickedOrderItems = array();

            foreach ($orderItemIds as $orderItemId) {
                $orderItem = $this->_orderItemFactory->load($orderItemId);
                if ($orderItem->getOrderId() == $orderId) {
                    $orderItem->setCorItemPickStatus(1);
                    $orderItem->setQtyShipped($orderItem->getQtyOrdered());
                    $orderItem->save();
                    $pickedOrderItems[] = $orderItemId;
                    $count++;
                }
            }

            if ($count) {
                $orderItems = $this->_orderItemFactory->getCollection()->addFieldToFilter('order_id', $orderId)->addFieldToFilter('cor_item_pick_status', 0);
                if (empty($orderItems->getData())) {
                    $orderState = OrderFactory::STATE_COMPLETE;
                    $order->setState($orderState)->setStatus(OrderFactory::STATE_COMPLETE);
                    $order->save();
                }
                $result['order_item'] = $pickedOrderItems;
                $result['message'] = __("Order items picked successfully.");
                echo \Zend_Json::encode($result);
                exit;
            } else {
                $result['message'] = __("Order items trying to pick not found.");
                echo \Zend_Json::encode($result);
                exit;
            }
        } else {
            $result['error'] = '1';
            $result['message'] = __("Order id or order item is missing.");
            echo \Zend_Json::encode($result);
            exit;
        }
    }
}

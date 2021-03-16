<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Orderdispatch
 * @author    Mangoit
 */

namespace Mangoit\Orderdispatch\Helper;

/**
 * Marketplace helper Orders.
 */
class Orders extends \Webkul\Marketplace\Helper\Orders
{
    /**
     * @param string $order, $sellerid, $comment
     *
     * @return bool
     *
     * @throws Mage_Core_Exception
     */
    public function mpregisterCancellation($order, $sellerId, $comment = '')
    {
        $flag = 0;
        if ($order->canCancel()) {
            $cancelState = 'canceled';
            $items = [];
            $shippingAmount = 0;
            $orderId = $order->getId();
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $trackingsdata = $objectManager->create(
                'Webkul\Marketplace\Model\Orders'
            )
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    $orderId
                )
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
            foreach ($trackingsdata as $tracking) {
                $shippingAmount = $tracking->getShippingCharges();
                $items = explode(',', $tracking->getProductIds());

                $itemsarray = $this->_getItemQtys($order, $items);
                foreach ($order->getAllItems() as $item) {
                    if (in_array($item->getProductId(), $items)) {
                        $flag = 1;
                        $item->cancel();
                    }
                }
                foreach ($order->getAllItems() as $item) {
                    if ($cancelState != 'processing' && $item->getQtyToRefund()) {
                        if ($item->getQtyToShip() > $item->getQtyToCancel()) {
                            $cancelState = 'processing';
                        } else {
                            $cancelState = 'complete';
                        }
                    } elseif ($item->getQtyToInvoice()) {
                        $cancelState = 'processing';
                    }
                }
                $order->setState($cancelState, true, $comment)->setStatus($cancelState)->save();
            }
        }

        return $flag;
    }
}

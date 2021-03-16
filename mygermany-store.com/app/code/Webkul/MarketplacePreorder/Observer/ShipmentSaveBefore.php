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

/**
 * Shipment Save Before  Observer.
 */
class ShipmentSaveBefore implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_helper;

    /**
     * @param \Webkul\MarketplacePreorder\Helper\Data     $helper
     */
    public function __construct(
        \Webkul\MarketplacePreorder\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * Product delete after event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $shipment = $observer->getEvent()->getShipment();
        $order = $shipment->getOrder();
        foreach ($shipment->getAllItems() as $item) {
            $itemId = $item->getOrderItemId();
            $preorderItem = $this->_helper->getPreorderItemCollectionData(
                ['order_id', 'item_id'],
                [$order->getId(), $itemId]
            );
            if ($preorderItem) {
                if (!$preorderItem->getStatus()) {
                    $error = "Preorder is not completed.";
                    throw new \Magento\Framework\Validator\Exception(__($error));
                }
            }
        }
    }
}

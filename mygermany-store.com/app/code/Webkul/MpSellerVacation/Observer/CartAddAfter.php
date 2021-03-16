<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart as CustomerCart;
use Magento\Framework\Exception\LocalizedException;

class CartAddAfter implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Magento\Checkout\Model\Cart
     */
    protected $_cart;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param CustomerCart                              $cart
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CustomerCart $cart
    ) {

        $this->_objectManager = $objectManager;
        $this->_cart = $cart;
    }

    /**
     * Observer to stop product from adding to cart.
     */
    public function execute(Observer $observer)
    {
        $item = $observer->getEvent()->getQuoteItem();
        $productId = $item->getProductId();

        if ($productId) {
            $status = $this->_objectManager->create('Webkul\MpSellerVacation\Helper\Data')
                ->getProductvacationStatus($productId);

            if ($status) {
                throw new LocalizedException(__('This item cannot be added to the cart'));
            }
        }
    }
}

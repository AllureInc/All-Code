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

class CartProductCheck implements ObserverInterface
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
        \Magento\Checkout\Model\Session $checkoutSession,
        \Webkul\MpSellerVacation\Helper\Data $helper,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {

        $this->_objectManager = $objectManager;
        $this->_checkoutSession = $checkoutSession;
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
    }

    /**
     * Observer to stop product from adding to cart.
     */
    public function execute(Observer $observer)
    {
        $vacationStatus = false;
        $items = $this->_checkoutSession->getQuote()->getAllVisibleItems();
        $count = 0;
        foreach ($items as $item) {
            $status =  $this->checkSellerStatus($item->getProductId());
            if ($status) {
                $vacationStatus = true;
            }
        }
        if ($vacationStatus) {
            $this->_messageManager->addNotice('Seller is on vacation, so please delete respective products from cart.');
        }
    }

    public function checkSellerStatus($productId)
    {
        $status = $this->_helper->getProductvacationStatus($productId);
        return $status;
    }
}

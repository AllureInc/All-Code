<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellerVacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerVacation\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;

/**
 * Webkul MpSellerVacation BeforeViewCart Observer
 */
class BeforeViewCart implements ObserverInterface
{
    /**
     * @var Webkul\MpSellerVacation\Helper\Data
     */
    private $helper;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $_urlInterface;

    /**
     * @param \Webkul\MpSellerVacation\Helper\Data $helper
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Framework\UrlInterface $urlInterface
     */
    public function __construct(
        \Webkul\MpSellerVacation\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\UrlInterface $urlInterface
    ) {
        $this->helper     = $helper;
        $this->_checkoutSession = $checkoutSession;
        $this->_urlInterface = $urlInterface;
    }

    /**
     * [executes on controller_action_predispatch_checkout_index_index event]
     *
     * @param \Magento\Framework\Event\Observer $observer
     *
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        try {
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
                $url = $this->_urlInterface->getUrl('checkout/cart');
                $observer->getControllerAction()
                    ->getResponse()
                    ->setRedirect($url);
            }
        } catch (\Exception $e) {
        }
    }

    public function checkSellerStatus($productId)
    {
        $status = $this->helper->getProductvacationStatus($productId);
        return $status;
    }
}

<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Orderdispatch
 * @author    Mangoit
 */

namespace Mangoit\Orderdispatch\Controller\Order;

/**
 * Mangoit Orderdispatch Order View Controller.
 */
class View extends \Webkul\Marketplace\Controller\Order
{
    /**
     * Seller Order View page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            $order = $this->_initOrder();
            if ($order && ($order->getStatus() != 'compliance_check')) {
                /*update notification flag*/
                $this->_updateNotification();
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->_resultPageFactory->create();
                if ($helper->getIsSeparatePanel()) {
                    $resultPage->addHandle('marketplace_layout2_order_view');
                }
                $resultPage->getConfig()->getTitle()->set(
                    __('Order #%1', $order->getRealOrderId())
                );

                return $resultPage;
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}

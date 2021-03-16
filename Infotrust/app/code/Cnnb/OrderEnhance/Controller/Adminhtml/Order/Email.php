<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderEnhance
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 */
namespace Cnnb\OrderEnhance\Controller\Adminhtml\Order;

use Psr\Log\LoggerInterface;
use Cnnb\OrderEnhance\Helper\Data as CnnbHelper;

class Email extends \Magento\Sales\Controller\Adminhtml\Order\Email
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::email';
    
    /**
     * @var SalesData
     */
    protected $logger;

    /**
     * @var SalesData
     */
    protected $helper;

    /**
     * Notify user
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $order = $this->_initOrder();
        $this->logger = $this->_objectManager->create(LoggerInterface::class);
        $this->helper = $this->_objectManager->create(CnnbHelper::class);
        if ($order) {
            try {
                $this->orderManagement->notify($order->getEntityId());
                $this->messageManager->addSuccessMessage(__('You sent the order email.'));
                /* Add comment */
                if ($order->getEntityId() && $this->helper->isModuleEnable()) {
                    $this->logger->info(' ---- Adding Email Order Comment ----');
                    $orderId = $order->getEntityId();
                    $this->helper->addCommentInOrder($orderId, $this->helper->getOrderSendEmailComment());
                    $this->logger->info(' ---- You sent the order email. Order ID: '.$orderId.' ----');
                }
                /* Add comment Ends*/
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('We can\'t send the email order right now.'));
                $this->logger->critical($e);
            }
            return $this->resultRedirectFactory->create()->setPath(
                'sales/order/view',
                [
                    'order_id' => $order->getEntityId()
                ]
            );
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
    }
}

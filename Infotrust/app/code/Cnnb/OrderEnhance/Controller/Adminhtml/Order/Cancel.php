<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderEnhance
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 */
namespace Cnnb\OrderEnhance\Controller\Adminhtml\Order;

use Magento\Framework\App\Action\HttpPostActionInterface as HttpPostActionInterface;
use Psr\Log\LoggerInterface;
use Cnnb\OrderEnhance\Helper\Data as CnnbHelper;

class Cancel extends \Magento\Sales\Controller\Adminhtml\Order\Cancel
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::cancel';
    
    /**
     * @var SalesData
     */
    protected $logger;

    /**
     * @var SalesData
     */
    protected $helper;

    /**
     * Cancel order
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $this->logger = $this->_objectManager->create(LoggerInterface::class);
        $this->helper = $this->_objectManager->create(CnnbHelper::class);
        $this->logger->info(' ---- Cnnb\OrderEnhance\Controller\Adminhtml\Orde\Cancel  ----');

        if (!$this->isValidPostRequest()) {
            $this->messageManager->addErrorMessage(__('You have not canceled the item. 1'));
            $this->logger->info(' ---- Line 48 ----');
            return $resultRedirect->setPath('sales/*/');
        }
        $order = $this->_initOrder();
        if ($order) {
            try {
                /* Add comment */
                if ($order->getEntityId() && $this->helper->isModuleEnable()) {
                    $this->logger->info(' ---- Adding Cancel Order Comment ----');
                    $orderId = $order->getEntityId();
                    $this->helper->addCommentInOrder($orderId, $this->helper->getOrderCancelComment());
                    $this->logger->info(' ---- You canceled the order. Order ID: '.$orderId.' ----');
                }
                /* Add comment Ends*/
                $this->orderManagement->cancel($order->getEntityId());
                $this->logger->info(' ---- Line 55 ----');
                $this->messageManager->addSuccessMessage(__('You canceled the order.'));
                $this->logger->info(' ---- Line 57 ----');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                $this->logger->info(' ---- Line 68 ----');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('You have not canceled the item. 2'));
                $this->logger->info(' ---- Exception Message: '.$e->getMessage().' ----');
                $this->_objectManager->get(\Psr\Log\LoggerInterface::class)->critical($e);
            }
            return $resultRedirect->setPath('sales/order/view', ['order_id' => $order->getId()]);
        }
        return $resultRedirect->setPath('sales/*/');
    }
}

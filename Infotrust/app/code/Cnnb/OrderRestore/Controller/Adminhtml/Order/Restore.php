<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderRestore
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 */
namespace Cnnb\OrderRestore\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Cnnb\OrderRestore\Model\OrderFactory;
use Cnnb\OrderRestore\Helper\Data;

class Restore extends Action
{
    /**
     * @var \Cnnb\OrderRestore\Helper\Data
     */
    protected $helper;

    /**
     * Initialize Group Controller
     *
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Cnnb\OrderRestore\Model\OrderFactory $orderFactory
     * @param \Cnnb\OrderRestore\Helper\Data $helper
     */
    public function __construct(
        Context $context,
        OrderFactory $orderFactory,
        Data $helper
    ) {
        $this->orderFactory = $orderFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->_authorization->isAllowed('Cnnb_OrderRestore::restore');
    }

    /**
     * Delete action
     *
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    public function execute()
    {
        $id = $this->getRequest()->getParam('order_id');

        if ($id && $this->helper->isModuleEnable()) {
            $order = $this->orderFactory->create()->load($id);
            try {
                $order->restore($this->helper->getOrderCommentText(), $this->helper->getOrderStatus());
                $order->save();
                $this->messageManager->addSuccess(__('Order has been restored.'));
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
            }
        } else {
            $this->messageManager->addError(__('Unable to restore the order!'));
        }

        /** @var \Magento\Backend\Model\View\Result\Redirect $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        return $resultRedirect->setPath('sales/order/view', ['order_id' => $id]);
    }
}
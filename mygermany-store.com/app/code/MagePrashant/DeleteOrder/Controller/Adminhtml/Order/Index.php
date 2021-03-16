<?php
namespace MagePrashant\DeleteOrder\Controller\Adminhtml\Order;

class Index extends \Magento\Backend\App\Action
{
    protected $order;
    protected $remove;
    protected $request;
    protected $helper;
  
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Sales\Model\OrderRepository $orderRepository,
        \MagePrashant\DeleteOrder\Helper\Data $dataHelper
    ) {
        $this->request = $context->getRequest();
        $this->orderRepository      = $orderRepository;
        $this->dataHelper = $dataHelper;
        parent::__construct($context);
    }

    public function execute()
    {
		if ($this->dataHelper->isEnabled()) {
			if ($id = $this->request->getParam("order_id")) {
				try {
					$this->orderRepository->deleteById($id);
					$this->messageManager->addSuccess(__('Order has been deleted successfully.'));
				} catch (\Exception $e) {
					$this->messageManager->addError($e->getMessage());
				}
			} else {
				$this->messageManager->addError(__('Something went wrong while Delete Order.'));
			}
		} else {
            $this->messageManager->addError(__('Delete Order module is Disabled'));
        }
		$this->_redirect('sales/order/index');
    }
}

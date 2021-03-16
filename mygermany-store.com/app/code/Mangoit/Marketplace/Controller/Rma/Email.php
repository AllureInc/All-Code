<?php
namespace Mangoit\Marketplace\Controller\Rma;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Marketplace Product Add Controller.
 */
class Email extends Action
{
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Framework\View\Result\PageFactory
     */
    protected $_resultPageFactory;

    protected $_helper;

    protected $_objectManager;

    /**
     * @param Context                                       $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory    $resultPageFactory
     * @param \Magento\Customer\Model\Session               $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Mangoit\Marketplace\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['reason'])) {
            $data['orderid'] = $params['orderId'];
            $data['subject'] = $params['reason'];
            $data['email'] = $this->_customerSession->getCustomer()->getEmail();
            $data['name'] = $this->_customerSession->getCustomer()->getName();
            $resultData = $this->_helper->sendCustomEmailToAdmin($data);
            if ($resultData) {
                $this->messageManager->addSuccess(__("Your return request has been sent to admin."));
            } else {
                $this->messageManager->addError(__('Something went wrong.'));
            }
        } else {
            $this->messageManager->addError(__('Please add your reason for cancel this order.'));            
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        return $resultRedirect->setPath('sales/order/view/order_id/'.$params['entity_id'].'/');
    }

}
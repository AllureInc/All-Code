<?php
namespace Mangoit\Marketplace\Controller\Rma;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Marketplace Product Add Controller.
 */
class Payment extends Action
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

    protected $_pageFactory;

    /**
     * @param Context                                       $context
     * @param Webkul\Marketplace\Controller\Product\Builder $productBuilder
     * @param \Magento\Framework\View\Result\PageFactory    $resultPageFactory
     * @param \Magento\Customer\Model\Session               $customerSession
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        ResultFactory $resultFactory,
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Mangoit\Marketplace\Helper\Data $helper,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_pageFactory = $resultFactory;
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_objectManager = $objectmanager;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['receive'])) 
        {
            parse_str($params['receive'], $output);
            $seller_id = $output['seller_id'];
            $alreadyPaid = $this->checkIfAlreadyPaid($seller_id, $output['order_ids']);
            if ($alreadyPaid == true) {
                // $resultPage = $this->_resultPageFactory->create();
                $storemanager = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
                $this->messageManager->addError(__('The payment has been refunded to the admin'));
                header('location: '.$storemanager->getStore()->getBaseUrl());
                exit;
            }
        }
        return $this->_resultPageFactory->create();
    }

    public function checkIfAlreadyPaid($seller_id, $order_ids)
    {
        $functionResult = false;
        $salesList = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');
        foreach ($order_ids as $order_id) {
            $collection = $salesList->getCollection()
                        ->addFieldToFilter(
                            'seller_id', array('eq'=> $seller_id)
                        )->addFieldToFilter(
                            'magerealorder_id', array('eq'=> $order_id)
                        )->addFieldToFilter(
                            'is_payment_returned', array('eq'=> 1)
                        );

            if (count($collection->getData()) > 0) {
                $functionResult = true;
                // exit();
            }
        }
        return $functionResult;
    }

}
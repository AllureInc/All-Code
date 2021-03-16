<?php
namespace Mangoit\Marketplace\Controller\Rma;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;

/**
 * Webkul Marketplace Product Add Controller.
 */
class Emailafterpay extends Action
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

    /** @var \Magento\Sales\Api\Data\OrderInterface $order **/
    protected $_marketplaceEmail;

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
        \Mangoit\Marketplace\Helper\MarketplaceEmail $marketplaceEmail,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_helper = $helper;
        $this->_objectManager = $objectmanager;
        $this->_marketplaceEmail = $marketplaceEmail;
        parent::__construct($context);
    }

    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (isset($params['paid']) && ($params['paid'] == true)) 
        {
            parse_str($params['seller_data'], $output);
            $seller_id = $output['seller_id'];
            // $amount = (float) filter_var($output['amount'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        // $this->getSellerInformation($seller_id);
        $seller = $this->getCustomerInformation($seller_id);
        $data['email_template'] = 'afterpay_email_to_admin';
        $data['orderids'] = implode(',', array_unique($output['order_ids']));
        $data['name'] = $seller->getFirstname().' '.$seller->getLastname();
        $data['email'] = $seller->getEmail();
        $data['amount'] = $output['amount'];
        // print_r($data);
        $emailResult = $this->_helper->sendEmailToAdmin($data, $seller->getData('store_id'));
        if ($emailResult) {
            $this->setPaymentReturned($output['order_ids'], $seller_id);
        }
        return $emailResult;
        // exit();
        // return $this->_resultPageFactory->create();
    }

    public function getSellerInformation($seller_id)
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
        $seller = $model->load($seller_id);
        if ($seller->getData()) {
            // print_r($seller->getData());
            return $seller;

        }

    }

    public function getCustomerInformation($customer_id)
    {
        $model = $this->_objectManager->create('Magento\Customer\Model\Customer');
        $customer = $model->load($customer_id);
        if ($customer->getData()) {
           return $customer;
        }
    }

    public function setPaymentReturned($order_ids, $seller_id)
    {
        $salesList = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');
        foreach ($order_ids as $order_id) {
            $collection = $salesList->getCollection()
            ->addFieldToFilter('seller_id', array('eq'=> $seller_id))
            ->addFieldToFilter('magerealorder_id', array('eq'=> $order_id));
            foreach ($collection as $item) {
                $item->setIsPaymentReturned(true);
                $item->setPaidStatus(3);
                $item->save();
            }
        }

    }

}
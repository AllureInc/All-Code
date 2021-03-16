<?php
namespace Mangoit\Marketplace\Controller\Newsletter;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Unsubscribe extends \Magento\Framework\App\Action\Action
{   
    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    protected $helper;
    protected $subscriberFactory;
    protected $mishelper;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Mangoit\Marketplace\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        \Mangoit\Marketplace\Helper\Data $mishelper
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        $this->subscriberFactory= $subscriberFactory;
        $this->mishelper = $mishelper;
        parent::__construct($context);
    }
    
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $email = $this->getRequest()->getParam('logged');

        $subscribeObj = $this->subscriberFactory->create();
        if ($email) {
            if ($this->_customerSession->isLoggedIn()) {
                $email = $this->_customerSession->getCustomer()->getEmail();
            }
            $checkSubscriber = $subscribeObj->loadByEmail($email);
            if ($checkSubscriber->isSubscribed()) {
                // Customer is subscribed
                $subscribeObj->loadByEmail($email)->unsubscribe();
                $this->messageManager->addSuccess(__('You have successfully unsubscribed newsletter'));
            } else {
                $this->messageManager->addError(__('You are currenctly not subscribed to any newsletter'));
                echo '404';
                // Customer is not subscribed
            }
            exit;
           
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                $this->helper->getBaseUrl(),
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}
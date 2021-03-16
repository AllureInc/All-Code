<?php
namespace Mangoit\Productfaq\Controller\Product;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Action;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class Newfaq extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @var \Magento\Customer\Model\Url
     */
    protected $_url;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_session;
    protected $customerSession;
    public $_storeManager;
    protected $sellerProduct;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param \Magento\Customer\Model\Url $url
     * @param \Magento\Customer\Model\Session $session
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Url $url,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Marketplace\Model\Product $sellerProduct
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_url = $url;
        $this->_session = $session;
        $this->customerSession = $customerSession;
        $this->_storeManager=$storeManager;
        $this->sellerProduct = $sellerProduct;
        parent::__construct($context);
    }

    /**
     * Show customer tickets
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        if ($this->customerSession->isLoggedIn()) {
            $sellerProductColl = $this->sellerProduct->getCollection()
                ->addFieldToFilter('seller_id', $this->customerSession->getCustomer()->getId());
                if ($sellerProductColl->count() > 0) {
                    /** @var \Magento\Framework\View\Result\Page resultPage */
                    $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
                    return $resultPage;
                } else {
                    /** @var \Magento\Framework\View\Result\Page resultPage */
                    $this->messageManager->addError(__('Please create any product to create new FAQ.'));
                    $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
                    return $resultPage->setPath($this->_storeManager->getStore()->getBaseUrl());
                }

        } else {
            /** @var \Magento\Framework\View\Result\Page resultPage */
            $resultPage = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultPage->setPath($this->_storeManager->getStore()->getBaseUrl());
        }
    }
}
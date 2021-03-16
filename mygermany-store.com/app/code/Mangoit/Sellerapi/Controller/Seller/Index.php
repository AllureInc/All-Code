<?php
namespace Mangoit\Sellerapi\Controller\Seller;

class Index extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_resultPageFactory;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
        )
    {
        $this->_resultPageFactory = $resultPageFactory;
        return parent::__construct($context);
    }

    public function execute()
    {
        $resultPage = $this->_resultPageFactory->create();
        return $resultPage;
        /*return $this->_pageFactory->create();*/
    }
}

<?php
namespace Mangoit\ShippingCostCalculator\Controller\Index;

use Magento\Framework\App\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\Controller\ResultFactory;

class ShowCalculation extends \Magento\Framework\App\Action\Action
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

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Mangoit\Marketplace\Helper\Data $helper,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_customerSession = $customerSession;
        $this->_resultPageFactory = $resultPageFactory;
        $this->helper = $helper;
        parent::__construct($context);
    }
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     * @throws NotFoundException
     */
    public function execute()
    {
        $postData = $this->getRequest()->getPost();
        if ($this->getRequest()->isPost()) {
            $layout = $this->_view->getLayout();
            $block = $layout->createBlock('Mangoit\ShippingCostCalculator\Block\Calculations')->setTemplate('calculations.phtml');
            $this->getResponse()->appendBody($block->toHtml());
        }
        // $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        // return $resultPage;
    }
}
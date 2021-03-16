<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\VendorPayments\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class Index extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        // die('sda');
        // $marketplacelabel = $this->_objectManager->get(
        //     'Webkul\Marketplace\Helper\Data'
        // )->getMarketplaceHeadLabel();
        // if (!$marketplacelabel) {
        //     $marketplacelabel = 'Marketplace Landing Page';
        // }
        // $resultPage = $this->_resultPageFactory->create();
        // $resultPage->getConfig()->getTitle()->set(__($marketplacelabel));

        // return $resultPage;

        $this->_view->loadLayout();
        $this->_view->getLayout()->initMessages();
        $this->_view->renderLayout();
    }
}

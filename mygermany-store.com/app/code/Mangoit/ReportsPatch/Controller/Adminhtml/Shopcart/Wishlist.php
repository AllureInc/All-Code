<?php

namespace Mangoit\ReportsPatch\Controller\Adminhtml\Shopcart;

class Wishlist extends \Magento\Backend\App\Action
{

    protected $resultPageFactory;

    /**
     * Constructor
     *
     * @param \Magento\Backend\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Add reports and shopping cart breadcrumbs
     *
     * @return $this
     */
    public function _initAction()
    {
        $this->_view->loadLayout();
        $this->_addBreadcrumb(__('Reports'), __('Reports'));
        $this->_addBreadcrumb(__('Shopping Cart'), __('Shopping Cart'));
        return $this;
    }

    /**
     * Products in carts action
     *
     * @return void
     */
    public function execute()
    {
        $this->_initAction()->_setActiveMenu(
            'Mangoit_ReportsPatch::reportpatch_shopcart_wishlist'
        )->_addBreadcrumb(
            __('Products Report'),
            __('Products Report')
        )->_addContent(
            $this->_view->getLayout()->createBlock(\Mangoit\ReportsPatch\Block\Adminhtml\Wishlist::class)
        );
        $this->_view->getPage()->getConfig()->getTitle()->prepend(__('Products in Wishlist'));
        $this->_view->renderLayout();
    }
}

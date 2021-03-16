<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Manage;

/**
 * Class Index
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Manage
 */
class Index extends \Magento\Backend\App\Action
{
    const ADMIN_RESOURCE = 'Scommerce_CookiePopup::manage';

    /** @var \Magento\Framework\View\Result\PageFactory */
    protected $resultPageFactory;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory resultPageFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory
    ) {
        parent::__construct($context);
        $this->resultPageFactory = $resultPageFactory;
    }

    /**
     * @inheritdoc
     */
    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->addBreadcrumb(__('Manage Cookie Popup Choice List'), __('Manage Choice List'));
        $resultPage->getConfig()->getTitle()->prepend(__('Cookie Popup'));
        $resultPage->getConfig()->getTitle()->prepend(__('Manage Choice List'));

        return $resultPage;
    }
}

<?php
/**
 *
 * Package © Mangoit_Marketplace
 */
namespace Mangoit\Marketplace\Controller\Account;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class LogoutSuccess extends \Magento\Customer\Controller\Account\LogoutSuccess
{
    

    /**
     * Logout success page
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $this->_view->getPage()->getConfig()->getTitle()->set(__('You are signed out'));
        return $this->resultPageFactory->create();
    }
}

<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Mangoit\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

class AccountDetails extends \Webkul\Walletsystem\Controller\Transfer\AccountDetails
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
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails,
        PageFactory $resultPageFactory
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_accountDetails = $accountDetails;
        parent::__construct($context, $accountDetails, $resultPageFactory);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        /** @var \Magento\Framework\View\Result\Page $resultPage */
        if ($this->getRequest()->isPost() && $this->getRequest()->getParams()) {

            if ( ($this->getRequest()->getParam("holdername")) == null ) {
                $this->messageManager->addError(__('A/c Holder Name is required.'));
            } else if (($this->getRequest()->getParam("bankname"))  == null ) {
                $this->messageManager->addError(__('Bank Name is required.'));
            } else if (($this->getRequest()->getParam("bankcode"))  == null) {
                $this->messageManager->addError(__('Bank Code is required.'));
            } else if (($this->getRequest()->getParam("additional"))  == null) {
                $this->messageManager->addError(__('Additional Information is required.'));
            } else {
                $accountDetails = $this->getRequest()->getParams();
                try {
                    $this->_accountDetails->setData($accountDetails)
                                        ->save();
                    $this->messageManager->addSuccess(__('Account Information Saved Successfully'));
                } catch (\Exception $e) {
                    $this->messageManager->addError(__('Something Went Wrong'));
                }
            }
        }

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->set(
            __('Account Details')
        );
        return $resultPage;
    }
}

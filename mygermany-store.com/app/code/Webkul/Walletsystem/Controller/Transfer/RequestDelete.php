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

namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultFactory; 

class RequestDelete extends \Magento\Framework\App\Action\Action
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
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails
    ) {
        $this->_accountDetails = $accountDetails;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->getRequest()->getParams('id')) {
            $id = $this->getRequest()->getParam('id');
            if ($id) {
                $this->_accountDetails->load($id)
                                    ->setRequestToDelete('1')
                                    ->save();
                $this->messageManager->addSuccess(__('Request Has Been Submitted To Admin'));
            } else {
                $this->messageManager->addWarning(__('Please check the data entered'));
            }
        } else {
            $this->messageManager->addWarning(__('Please check the data entered'));
        }

        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());
        return $resultRedirect;
    }
}

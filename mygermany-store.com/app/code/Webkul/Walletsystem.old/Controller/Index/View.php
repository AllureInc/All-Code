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

namespace Webkul\Walletsystem\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Customer\Model\Session as CustomerSession;

class View extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var WallettransactionFactory
     */
    protected $_walletTransaction;
    /**
     *@var CustomerSession
     */
    protected $customerSession;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WallettransactionFactory $walletTransaction,
        CustomerSession $customerSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_walletTransaction = $walletTransaction;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    /**
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        if (is_array($params) && array_key_exists('entity_id', $params)) {
            $walletTransaction = $this->_walletTransaction->create()->load($params['entity_id']);
            $customerId = $this->customerSession->getCustomerId();
            if ($customerId==$walletTransaction->getCustomerId()) {
                /** @var \Magento\Framework\View\Result\Page $resultPage */
                $resultPage = $this->_resultPageFactory->create();
                $resultPage->getConfig()->getTitle()->set(
                    __('View Transaction')
                );
                return $resultPage;
            } else {
                $this->messageManager->addError(__("You are not authorized to access this transaction!"));
                return $this->resultRedirectFactory->create()->setPath(
                    'walletsystem/index/index',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
    }
}

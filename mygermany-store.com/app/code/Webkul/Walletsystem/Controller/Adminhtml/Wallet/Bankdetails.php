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

namespace Webkul\Walletsystem\Controller\Adminhtml\Wallet;

use Webkul\Walletsystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action\Context;

class Bankdetails extends WalletController
{
    /**
     * @var \Magento\Framework\View\Result\LayoutFactory
     */
    protected $_resultLayoutFactory;
    /**
     * @var Magento\Customer\Model\CustomerFactory
     */
    protected $_customerModel;

    /**
     * @param \Magento\Backend\App\Action\Context          $context
     * @param \Magento\Framework\View\Result\LayoutFactory $resultLayoutFactory
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Customer\Model\CustomerFactory $customerModel
    ) {
        parent::__construct($context);
        $this->_customerModel = $customerModel;
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Walletsystem::banktransfer');
        $resultPage->getConfig()->getTitle()
            ->prepend(__('Bank Transfer Details'));
        $resultPage->addBreadcrumb(
            __("Bank Transfer Details"),
            __("Bank Transfer Details")
        );
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $notification = $objectManager->create('\Webkul\Walletsystem\Model\WalletNotification');
        $notifications = $notification->getCollection();
        foreach ($notifications->getItems() as $notification) {
            $notification->setBanktransferCounter(0);
            $notification->save();
        }
        $notifications->save();
        return $resultPage;
    }
}

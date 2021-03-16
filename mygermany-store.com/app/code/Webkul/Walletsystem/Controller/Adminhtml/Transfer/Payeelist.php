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

namespace Webkul\Walletsystem\Controller\Adminhtml\Transfer;

use Webkul\Walletsystem\Controller\Adminhtml\Transfer as TransferController;
use Magento\Framework\Controller\ResultFactory;

class Payeelist extends TransferController
{
    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);
        $resultPage->setActiveMenu('Webkul_Walletsystem::walletpayee');
        $resultPage->getConfig()->getTitle()->prepend(__('Wallet System Payee Details'));
        $resultPage->addBreadcrumb(__('Wallet System Payee Details'), __('Wallet System Payee Details'));
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $notification = $objectManager->create('\Webkul\Walletsystem\Model\WalletNotification');
        $notifications = $notification->getCollection();
        foreach ($notifications->getItems() as $notification) {
            $notification->setPayeeCounter(0);
            $notification->save();
        }
        $notifications->save();
        return $resultPage;
    }
}

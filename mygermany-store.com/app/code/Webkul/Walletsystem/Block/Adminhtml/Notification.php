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
namespace Webkul\Walletsystem\Block\Adminhtml;

class Notification extends \Magento\Backend\Block\Template
{
    
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\View\Asset\Repository $assetRepo,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        array $data = []
    ) {
        $this->_walletNotification = $walletNotification;
        $this->_assetRepo = $assetRepo;
        parent::__construct($context, $data);
    }

    public function getPayeeNotificationCount()
    {
        $notifications = $this->_walletNotification->getCollection();
        if (!$notifications->getSize()) {
            return false;
        } else {
            foreach ($notifications->getItems() as $notification) {
                $data = $notification->getPayeeCounter();
            }
            return $data;
        }
    }


    public function getBankTransferCounter()
    {
        $notifications = $this->_walletNotification->getCollection();
        if (!$notifications->getSize()) {
            return false;
        } else {
            foreach ($notifications->getItems() as $notification) {
                $data = $notification->getBanktransferCounter();
            }
            return $data;
        }
    }

    public function getImageUrl()
    {
        return $this->_assetRepo->getUrl('Webkul_Walletsystem::images/icons_notifications.png');
    }
}

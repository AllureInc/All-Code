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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class SalesOrderInvoiceSaveAfterObserver implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    
    /**
     * @param \Webkul\Walletsystem\Helper\Mail            $mailHelper
     */
    
    public function __construct(
        \Webkul\Walletsystem\Helper\Mail $mailHelper
    ) {
        $this->_mailHelper = $mailHelper;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $order = $observer->getOrder();
        $this->_mailHelper->checkAndUpdateWalletAmount($order);
    }
}

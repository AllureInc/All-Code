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
use Webkul\Walletsystem\Model\Walletrecord;

class SalesOrderPayInvoice implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var Webkul\Walletsystem\Model\Walletrecord
     */
    protected $_walletrecordModel;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param Walletrecord                                $walletRecord
     * @param Magento\Framework\App\Request\Http          $request
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        Walletrecord $walletRecord,
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_helper = $helper;
        $this->_date = $date;
        $this->_walletrecordModel = $walletRecord;
        $this->_request = $request;
    }

    /**
     * Invoice save after
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $event = $observer->getEvent();
        $invoice = $event->getInvoice();
        $order = $invoice->getOrder();
        $orderTotalPaid = $order->getTotalPaid();
        $orderBaseTotalPaid = $order->getBaseTotalPaid();
        $walletAmount = (-1) * ($invoice->getWalletAmount());
        $baseWalletAmount = (-1) * ($this->_helper->baseCurrencyAmount($invoice->getWalletAmount()));
        $order->setBaseTotalPaid($orderBaseTotalPaid + $baseWalletAmount);
        $order->setTotalPaid($orderTotalPaid + $walletAmount);
        return $this;
    }
}

<?php
/**
 * Wallet system block for invoice
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block\Sales\Order;

use Magento\Sales\Model\Order;
use Webkul\Walletsystem\Helper\Data;

class WalletsystemInvoice extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $_order;
    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;
    /**
     * Get data (totals) source model.
     *
     * @return \Magento\Framework\DataObject
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $walletHelper,
        array $data = []
    ) {
        $this->_walletHelper = $walletHelper;
         parent::__construct($context, $data);
    }

    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }
    /**
     * Initialize all order totals.
     */
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $invoice = $parent->getInvoice();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $title = __('Wallet Amount');
        $store = $this->getStore();
        $invoiceCollection = $this->_order->getInvoiceCollection();
        $baseCurrency = $this->_walletHelper->getBaseCurrencyCode();
        $orderCurrency = $this->_order->getOrderCurrencyCode();
        foreach ($invoiceCollection as $previousInvoice) {
            $walletamount = $previousInvoice->getWalletAmount();
            if ((double) $walletamount && !$previousInvoice->isCanceled()) {
                if ($invoice->getId() == $previousInvoice->getId()) {
                    $invoiceAmount = $invoice->getWalletAmount();
                    $baseAmount = $this->_walletHelper->getwkconvertCurrency(
                        $orderCurrency,
                        $baseCurrency,
                        $invoiceAmount
                    );
                    $walletPayment = new \Magento\Framework\DataObject(
                        [
                            'code' => 'wallet_amount',
                            'strong' => false,
                            'value' => $invoiceAmount,
                            'base_value' => $baseAmount,
                            'label' => __($title),
                        ]
                    );
                    $parent->addTotal($walletPayment, 'wallet_amount');
                    break;
                }
            }
        }

        return $this;
    }
    /**
     * Get order store object.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}

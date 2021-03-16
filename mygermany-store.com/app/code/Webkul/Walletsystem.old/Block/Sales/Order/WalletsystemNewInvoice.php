<?php
/**
 * Sales order block for new invoice
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

class WalletsystemNewInvoice extends \Magento\Framework\View\Element\Template
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
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param Data                                             $walletHelper
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Data $walletHelper,
        array $data = []
    ) {
        $this->_walletHelper = $walletHelper;
        parent::__construct($context, $data);
    }
    /**
     * Get data (totals) source model.
     *
     * @return \Magento\Framework\DataObject
     */
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
        $this->_order = $invoice->getOrder();
        $title = 'Wallet Amount';
        $walletamount = 0;
        $invoiceCollection = $this->_order->getInvoiceCollection();
        foreach ($invoiceCollection as $previousInvoice) {
            if ($previousInvoice->getEntityId()) {
                if (!$previousInvoice->isCanceled()) {
                    $walletamount = $walletamount + $previousInvoice->getWalletAmount();
                }
            }
        }
        if ($walletamount != $this->_order->getWalletAmount()) {
            $amount = $this->_order->getWalletAmount() - $walletamount;
            // calculate base currency amount
            $baseCurrency = $this->_walletHelper->getBaseCurrencyCode();
            $orderCurrency = $this->_order->getOrderCurrencyCode();
            $baseAmount = $this->_walletHelper->getwkconvertCurrency($orderCurrency, $baseCurrency, $amount);
            
            $walletPayment = new \Magento\Framework\DataObject(
                [
                    'code'          => 'wallet_amount',
                    'strong'        => false,
                    'value'         => $amount,
                    'base_value'    => $baseAmount,
                    'label'         => __($title),
                ]
            );
            $parent->addTotal($walletPayment, 'wallet_amount');
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

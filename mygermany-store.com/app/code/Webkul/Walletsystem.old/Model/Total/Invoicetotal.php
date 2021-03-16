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

namespace Webkul\Walletsystem\Model\Total;

class Invoicetotal extends \Magento\Sales\Model\Order\Invoice\Total\AbstractTotal
{

    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_httpRequest;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;

    /**
     * @param \Magento\Framework\App\Request\Http $httpRequest
     * @param \Webkul\Walletsystem\Helper\Data    $walletHelper
     * @param array                               $data
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $httpRequest,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        array $data = []
    ) {
        parent::__construct($data);
        $this->_httpRequest = $httpRequest;
        $this->_walletHelper = $walletHelper;
    }

    /**
     * Collect invoice Wallet amount.
     *
     * @param \Magento\Sales\Model\Order\Invoice $invoice
     *
     * @return $this
     */
    public function collect(\Magento\Sales\Model\Order\Invoice $invoice)
    {
        $params = $this->_httpRequest->getParams();
        $invoiceParams = [];
        if (array_key_exists('invoice', $params)) {
            $invoiceParams = $params['invoice']['items'];
        }
        $order = $invoice->getOrder();
        $walletAmount = 0;
        $invoiceAmount = 0;
        $shippingAmount = 0;
        $taxAmount = 0;
        $balance = $order->getWalletAmount();
        $baseWallet = $order->getBaseWalletAmount();
        foreach ($invoice->getOrder()->getInvoiceCollection() as $previousInvoice) {
            if ((double) $previousInvoice->getWalletAmount() && !$previousInvoice->isCanceled()) {
                $walletAmount = $walletAmount + $previousInvoice->getWalletAmount();
                $shippingAmount = $shippingAmount + $previousInvoice->getShippingAmount();
                $taxAmount = $taxAmount + $previousInvoice->getTaxAmount();
            }
            if ($walletAmount == $order->getWalletAmount()) {
                return $this;
            }
        }
        $balance = -1 * $balance;
        $invoiceAmount = $this->getInvoiceAmount($order->getAllItems(), $invoiceParams);
        $finalAmount = $invoiceAmount + ($order->getShippingAmount() - $shippingAmount) + ($order->getTaxAmount() - $taxAmount);
        $finalWalletAmount = $balance - (-$walletAmount);
        if ($finalAmount <= $finalWalletAmount) {
            $balance = $finalAmount;
        } else {
            $balance = $finalWalletAmount;
        }
        $walletHelper = $this->_walletHelper;

        $baseCurrency = $walletHelper->getBaseCurrencyCode();
        $orderCurrency = $order->getOrderCurrencyCode();
        $baseBalance = $walletHelper->getwkconvertCurrency($orderCurrency, $baseCurrency, $balance);
        $balance = -1 * $balance;
        $baseBalance = -1 * $baseBalance;
        $invoice->setWalletAmount($balance);
        $invoice->setGrandTotal($invoice->getGrandTotal() + $balance);
        $invoice->setBaseGrandTotal($invoice->getBaseGrandTotal() + $baseBalance);
        return $this;
    }

    public function getInvoiceAmount($items, $invoiceParams)
    {
        $invoiceAmount = 0;
        foreach ($items as $item) {
            if (array_key_exists($item->getItemId(), $invoiceParams)) {
                $price = $item->getprice() * $invoiceParams[$item->getItemId()];
                $tax = $item->getTaxAmount()/$item->getQtyOrdered();
                $price = $price + $tax * $invoiceParams[$item->getItemId()];
                $invoiceAmount = $invoiceAmount + $price;
            } else {
                $params = [];
                $qty = 1;
                if ($item->getProductOptions()) {
                    $productOptions = $item->getProductOptions();
                    if (is_array($productOptions)) {
                        $params = $productOptions['info_buyRequest'];
                    }
                }
                if (array_key_exists('qty', $params)) {
                    $qty = $params['qty'];
                }
                $price = $item->getprice() * $qty;
                $tax = $item->getTaxAmount()/$item->getQtyOrdered();
                $price = $price + $tax * $qty;
                $invoiceAmount = $invoiceAmount + $price;
            }
        }
        return $invoiceAmount;
    }
}

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
use Magento\Sales\Model\Order\Email\Sender\InvoiceSender;
use Magento\Sales\Model\OrderFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Magento\Sales\Model\Service\InvoiceService;
use Magento\Framework\DB\Transaction;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Magento\Quote\Model\QuoteRepository;
use Magento\Framework\Session\SessionManager;
use Magento\Sales\Api\OrderRepositoryInterface;

class SalesOrderPlaceAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    /**
     * @var \Magento\Checkout\Model\Session
     */
    protected $_checkoutSession;
    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    /**
     * @var Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $_stockRegistry;
    /**
     * @var InvoiceSender
     */
    protected $_invoiceSender;
    /**
     * @var  Webkul\Walletsystem\Model\WalletcreditamountFactory
     */
    protected $_walletcreditAmountFactory;
    /**
     * @var Magento\Sales\Model\OrderFactory;
     */
    protected $_orderModel;
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $_walletTransaction;
    /**
     * @var WalletrecordFactory
     */
    protected $_walletrecordFactory;
    /**
     * @var Magento\Sales\Model\Service\InvoiceService
     */
    protected $_invoiceService;
    /**
     * @var Magento\Framework\DB\Transaction
     */
    protected $_dbTransaction;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $walletUpdateData;
    /**
     * @var QuoteRepository
     */
    protected $_quoteRepository;
    /**
     * @var SessionManager
     */
    protected $_coreSession;
    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;
    /**
     * @param \Magento\Framework\Stdlib\DateTime\DateTime          $date
     * @param \Webkul\Walletsystem\Helper\Data                     $helper
     * @param \Webkul\Walletsystem\Helper\Mail                     $mailHelper
     * @param \Magento\Checkout\Model\Session                      $checkoutSession
     * @param \Magento\Catalog\Model\Product                       $productFactory
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param InvoiceSender                                        $invoiceSender
     * @param \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory
     * @param OrderFactory                                         $orderModel
     * @param WallettransactionFactory                             $walletTransaction
     * @param WalletrecordFactory                                  $walletRecordModel
     * @param InvoiceService                                       $invoiceService
     * @param Transaction                                          $dbTransaction
     */
    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Catalog\Model\Product $productFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        InvoiceSender $invoiceSender,
        \Webkul\Walletsystem\Model\WalletcreditamountFactory $walletcreditAmountFactory,
        OrderFactory $orderModel,
        WallettransactionFactory $walletTransaction,
        WalletrecordFactory $walletRecordModel,
        InvoiceService $invoiceService,
        Transaction $dbTransaction,
        WalletUpdateData $walletUpdateData,
        QuoteRepository $quoteRepository,
        SessionManager $coreSession,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->_date = $date;
        $this->_helper = $helper;
        $this->_mailHelper = $mailHelper;
        $this->_productFactory = $productFactory;
        $this->_checkoutSession = $checkoutSession;
        $this->_stockRegistry = $stockRegistry;
        $this->_invoiceSender = $invoiceSender;
        $this->_walletcreditAmountFactory = $walletcreditAmountFactory;
        $this->_orderModel = $orderModel;
        $this->_walletTransaction = $walletTransaction;
        $this->_walletrecordFactory = $walletRecordModel;
        $this->_invoiceService = $invoiceService;
        $this->_dbTransaction = $dbTransaction;
        $this->walletUpdateData = $walletUpdateData;
        $this->_quoteRepository = $quoteRepository;
        $this->_coreSession = $coreSession;
        $this->_orderRepository = $orderRepository;
    }

    /**
     * sales order place after.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $isMultiShipping = $this->_checkoutSession->getQuote()->getIsMultiShipping();
        if (!$isMultiShipping) {
            $walletProductId = $this->_helper->getWalletProductId();
            $orderId = $observer->getOrder()->getId();
            $order = $this->_orderRepository->get($orderId);
            if ($this->alreadyAddedInData($order)) {
                return;
            }
            $this->setDataInWalletTable($orderId, $order);
        } else {
            $quoteId = $this->_checkoutSession->getLastQuoteId();
            $quote = $this->_quoteRepository->get($quoteId);
            if ($quote->getIsMultiShipping() == 1 || $isMultiShipping == 1) {
                $orderIds = $this->_coreSession->getOrderIds();
                foreach ($orderIds as $orderId => $orderIncId) {
                    $lastOrderId = $orderId;
                    $order = $this->_orderRepository->get($lastOrderId);
                    if ($this->alreadyAddedInData($order)) {
                        continue;
                    }
                    $this->setDataInWalletTable($lastOrderId, $order);
                }
            }
        }
        $this->_checkoutSession->unsWalletDiscount();
    }
    public function setDataInWalletTable($orderId, $order)
    {
        $walletTransaction = $this->_walletTransaction->create();
        $walletProductId = $this->_helper->getWalletProductId();
        $customerId = $order->getCustomerId();
        $currencyCode = $order->getOrderCurrencyCode();
        $incrementId = $order->getIncrementId();
        $flag = 0;
        if ($orderId) {
            foreach ($order->getAllVisibleItems() as $item) {
                $productId = $item->getProductId();
                if ($productId == $walletProductId) {
                    $price = number_format($item->getBasePrice(), 2, '.', '');
                    $currPrice = number_format($item->getPrice(), 2, '.', '');
                    $flag = 1;
                }
            }
        }
        $totalAmount = 0;
        $usedAmount = 0;
        $remainingAmount = 0;
        if ($flag == 1) {
            $transferAmountData = [
                'customerid' => $customerId,
                'walletamount' => $price,
                'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                'curr_code' => $currencyCode,
                'curr_amount' => $currPrice,
                'walletnote' => __('Order id : %1 credited amount', $incrementId),
                'sender_id' => $customerId,
                'sender_type' => $walletTransaction::ORDER_PLACE_TYPE,
                'order_id' => $orderId,
                'status' => $walletTransaction::WALLET_TRANS_STATE_PENDING,
                'increment_id' => $incrementId
            ];
            $this->walletUpdateData->creditAmount($customerId, $transferAmountData);
            $this->_mailHelper->checkAndUpdateWalletAmount($order);
        } else {
            $discountAmount = $order->getBaseWalletAmount();
            $discountcurrAmount = $order->getWalletAmount();
            $walletDiscountParams = [];
            if ($this->_checkoutSession->getWalletDiscount()) {
                $walletDiscountParams = $this->_checkoutSession->getWalletDiscount();
            }
            if (array_key_exists('flag', $walletDiscountParams) && $walletDiscountParams['flag'] == 1) {
                $transferAmountData = [
                    'customerid' => $customerId,
                    'walletamount' => -1 * $discountAmount,
                    'walletactiontype' => $walletTransaction::WALLET_ACTION_TYPE_DEBIT,
                    'curr_code' => $currencyCode,
                    'curr_amount' => -1 * $discountcurrAmount,
                    'walletnote' => __('Order id : %1 debited amount', $incrementId),
                    'sender_id' => $customerId,
                    'sender_type' => $walletTransaction::ORDER_PLACE_TYPE,
                    'order_id' => $orderId,
                    'status' => $walletTransaction::WALLET_TRANS_STATE_APPROVE,
                    'increment_id' => $incrementId
                ];
                $this->walletUpdateData->debitAmount($customerId, $transferAmountData);
            }
            $this->addCreditAmountData($orderId);
            //generate invoice automatically if whole amount is paid by wallet
            if ($order->getPayment()->getMethod() == 'walletsystem') {
                $this->generateInvoiceForWalletPayment($order);
            }
        }
        $this->updateWaletProductQuantity($walletProductId);
    }
    public function addCreditAmountData($orderId)
    {
        $creditamount = $this->_helper->calculateCreditAmountforCart($orderId);
        if ($creditamount > 0) {
            $creditAmountModel = $this->_walletcreditAmountFactory->create();
            $creditAmountModel->setAmount($creditamount)
                ->setOrderId($orderId)
                ->setStatus($creditAmountModel::WALLET_CREDIT_AMOUNT_STATUS_DISABLE)
                ->save();
        }
    }

    public function generateInvoiceForWalletPayment($order)
    {
        if ($order->canInvoice()) {
            $invoice = $this->_invoiceService
                ->prepareInvoice($order);
            $invoice->register();
            $invoice->getOrder()->setIsInProcess(true);
            $invoice->save();
            $transactionSave = $this->_dbTransaction
                ->addObject($invoice)
                ->addObject($invoice->getOrder());
            $transactionSave->save();
            $this->_invoiceSender->send($invoice);
            //send notification code
            $order->addStatusHistoryComment(
                __('Notified customer about invoice #%1.', $invoice->getId())
            )
            ->setIsCustomerNotified(true)
            ->save();
        }
    }
    public function alreadyAddedInData($order)
    {
        $transactionCollection = $this->_walletTransaction
            ->create()
            ->getCollection()
            ->addFieldToFilter('order_id', $order->getId());

        if ($transactionCollection->getSize()) {
            return ture;
        }
        return false;
    }
    public function updateWaletProductQuantity($walletProductId)
    {
        $product = $this->_productFactory->load($walletProductId); //load product which you want to update stock
        $stockItem = $this->_stockRegistry->getStockItem($walletProductId); // load stock of that product
        $stockItem->setData('manage_stock', 0);
        $stockItem->setData('use_config_notify_stock_qty', 0);
        $stockItem->save(); //save stock of item
        $this->_stockRegistry->updateStockItemBySku($this->_helper::WALLET_PRODUCT_SKU, $stockItem);
        $product->setStockData(
            [
                'use_config_manage_stock' => 0,
                'manage_stock' => 0
            ]
        )->save(); //  also save product
    }
}

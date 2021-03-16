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

class CustomerDeleteCommitAfter implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;

    protected $walletPayee;

    protected $walletTransaction;

    protected $walletRecord;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $helper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        \Webkul\Walletsystem\Model\WallettransactionFactory $walletTransaction,
        \Webkul\Walletsystem\Model\WalletrecordFactory $walletRecord
    ) {
        $this->_helper = $helper;
        $this->walletPayee = $walletPayee;
        $this->walletTransaction = $walletTransaction;
        $this->walletRecord = $walletRecord;
    }

    /**
     * customer delete event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getCustomer();
        $customerId = $customer->getId();
        $this->deletePayeeFromList($customerId);
        $this->deleteTransactionForCustomer($customerId);
        $this->deleteRecordForCustomer($customerId);
        return $this;
    }
    protected function deletePayeeFromList($customerId)
    {
        $walletPayeeCollection = $this->walletPayee->create()->getCollection()
            ->addFieldToFilter('payee_customer_id', $customerId);
        if ($walletPayeeCollection->getSize()) {
            foreach ($walletPayeeCollection as $payee) {
                $payee->delete();
            }
        }
    }
    protected function deleteTransactionForCustomer($customerId)
    {
        $walletTransactionCollection = $this->walletTransaction->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($walletTransactionCollection->getSize()) {
            foreach ($walletTransactionCollection as $walletTransactionData) {
                $walletTransactionData->delete();
            }
        }
    }
    protected function deleteRecordForCustomer($customerId)
    {
        $walletRecordCollection = $this->walletRecord->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $walletRecordData) {
                $walletRecordData->delete();
            }
        }
    }
}

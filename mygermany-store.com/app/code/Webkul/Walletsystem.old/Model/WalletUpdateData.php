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

namespace Webkul\Walletsystem\Model;

use Magento\Framework\Model\AbstractModel;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;

class WalletUpdateData extends AbstractModel
{
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    protected $_walletrecord;
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    protected $_walletTransaction;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $_localeDate;

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction $resource,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->_walletrecord = $walletrecord;
        $this->_walletTransaction = $transactionFactory;
        $this->_walletHelper = $walletHelper;
        $this->_date = $date;
        $this->_mailHelper = $mailHelper;
        $this->_messageManager = $messageManager;
        $this->_localeDate = $localeDate;
        parent::__construct(
            $context,
            $registry,
            $resource,
            $resourceCollection,
            $data
        );
    }
    public function getRecordByCustomerId($customerId)
    {
        $recordId = 0;
        $walletRecordModel = '';
        $walletRecordCollection = $this->_walletrecord
            ->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        foreach ($walletRecordCollection as $walletRecord) {
            $recordId = $walletRecord->getEntityId();
        }
        if ($recordId) {
            $walletRecordModel = $this->_walletrecord
                ->create()
                ->load($recordId);
        }
        return $walletRecordModel;
    }
    public function creditAmount($customerId, $params)
    {
        $this->setTransactionModelData($customerId, $params);
        return [
            'success' => 1
        ];
    }

    public function debitAmount($customerId, $params)
    {
        $customerRecord = $this->getRecordByCustomerId($customerId);
        if ($customerRecord!='' &&
            $customerRecord->getEntityId() &&
            $customerRecord->getRemainingAmount() >= $params['walletamount']) {
            $this->setTransactionModelData($customerId, $params);
            return [
                'success' => 1
            ];
        } else {
            $this->_messageManager->addError(
                __(
                    "Respective amount is not available in customer's wallet with customer id %1 to subtract",
                    $customerId
                )
            );
            return [
                'error' => 1
            ];
        }
    }
    public function setTransactionModelData($customerId, $params)
    {
        $currencycode = $this->_walletHelper->getBaseCurrencyCode();
        $walletTransactionModel = $this->_walletTransaction->create();
        $walletTransactionModel->setCustomerId($customerId)
            ->setAmount($params['walletamount'])
            ->setCurrAmount($params['curr_amount'])
            ->setStatus($params['status'])
            ->setCurrencyCode($params['curr_code'])
            ->setAction($params['walletactiontype'])
            ->setTransactionNote($params['walletnote'])
            ->setSenderId($params['sender_id'])
            ->setSenderType($params['sender_type'])
            ->setOrderId($params['order_id'])
            ->setTransactionAt($this->_date->gmtDate());
        $walletTransactionModel->save();

        $walletRecordModel = $this->getRecordByCustomerId($customerId);
        if ($walletRecordModel!='' && $walletRecordModel->getEntityId()) {
            $remainingAmount = $walletRecordModel->getRemainingAmount();
            $totalAmount = $walletRecordModel->getTotalAmount();
            $usedAmount = $walletRecordModel->getUsedAmount();
            $recordId = $walletRecordModel->getEntityId();
            if ($params['walletactiontype']=='debit') {
                $data = [
                    'used_amount' => $usedAmount + $params['walletamount'],
                    'remaining_amount' => $remainingAmount - $params['walletamount'],
                    'updated_at' => $this->_date->gmtDate(),
                ];
                $finalAmount = $remainingAmount - $params['walletamount'];
            } else {
                $data = [
                    'total_amount' => $params['walletamount'] + $totalAmount,
                    'remaining_amount' => $params['walletamount'] + $remainingAmount,
                    'updated_at' => $this->_date->gmtDate(),
                ];
                $finalAmount = $params['walletamount'] + $remainingAmount;
            }
            if ($params['status']==1) {
                $walletRecordModel = $this->_walletrecord->create()
                ->load($recordId)
                ->addData($data);
            }
            $saved = $walletRecordModel->setId($recordId)->save();
        } else {
            if ($params['status']==1) {
                $walletRecordModel = $this->_walletrecord->create();
                $walletRecordModel->setTotalAmount($params['walletamount'])
                    ->setCustomerId($customerId)
                    ->setRemainingAmount($params['walletamount'])
                    ->setUpdatedAt($this->_date->gmtDate());
                $saved = $walletRecordModel->save();
            }
            $finalAmount = $params['walletamount'];
        }
        if ($params['status']==1) {
            $date = $this->_localeDate->date(new \DateTime($this->_date->gmtDate()));
            $formattedDate = $date->format('g:ia \o\n l jS F Y');
            $emailParams = [
                'walletamount' => $this->_walletHelper->getformattedPrice($params['walletamount']),
                'remainingamount' => $this->_walletHelper->getformattedPrice($finalAmount),
                'type' => $params['sender_type'],
                'action' => $params['walletactiontype'],
                'increment_id' => $params['increment_id'],
                'transaction_at' => $formattedDate,
                'walletnote' => $params['walletnote'],
                'sender_id' => $params['sender_id']
            ];
            $store = $this->_walletHelper->getStore();
            $this->_mailHelper->sendMailForTransaction(
                $customerId,
                $emailParams,
                $store
            );
        }
    }
}

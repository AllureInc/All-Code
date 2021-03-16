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

namespace Webkul\Walletsystem\Controller\Adminhtml\Wallet;

use Webkul\Walletsystem\Controller\Adminhtml\Wallet as WalletController;
use Magento\Backend\App\Action;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Webkul\Walletsystem\Model\Wallettransaction;

class MasscancelBanktransfer extends WalletController
{
    /**
     * @var Filter
     */
    protected $_filter;
    /**
     * @var Walletsystem\Model\ResourceModel\Walletcreditrules\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param Action\Context                                           $context
     * @param Filter                                                   $filter
     * @param Walletsyste\Api\WalletTransactionRepositoryInterface                 $creditRuleRepository
     * @param Walletsyste\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory
     */
    public function __construct(
        Action\Context $context,
        Filter $filter,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
        WallettransactionFactory $transactionFactory,
        \Webkul\Walletsystem\Controller\Adminhtml\Wallet\Disapprove $disapprove,
        \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory $collectionFactory,
        WalletUpdateData $walletUpdate,
        \Webkul\Walletsystem\Helper\Data $helper
    ) {
        parent::__construct($context);
        $this->websiteRepositiry = $websiteRepositiry;
        $this->_disapprove = $disapprove;
        $this->_walletHelper = $helper;
        $this->_filter = $filter;
        $this->_walletUpdate = $walletUpdate;
        $this->_walletTransaction = $transactionFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * Mass Update action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        
               
        try {
            $resultRedirect = $this->resultRedirectFactory->create();
            $data = $this->getRequest()->getParams();
            $this->refundAmountToWallet($this->getRequest()->getParams());
            if (isset($data['selected'])) {
                $selected = count($data['selected']);
            } else {
                $selected = __("All Selected");
            }
            
            $status = Wallettransaction::WALLET_TRANS_STATE_CANCEL;
            $collection = $this->_filter->getCollection($this->_collectionFactory->create());
            $entityIds = $collection->getAllIds();
            if (count($entityIds)) {
                $coditionArr = [];
                foreach ($entityIds as $key => $id) {
                    $condition = "`entity_id`=".$id;
                    array_push($coditionArr, $condition);
                }
                $coditionData = implode(' OR ', $coditionArr);

                $creditRuleCollection = $this->_collectionFactory->create();
                $creditRuleCollection->setTableRecords(
                    $coditionData,
                    ['status' => $status]
                );

                $this->messageManager->addSuccess(
                    __(
                        '%1 record(s) successfully updated.',
                        $selected
                    )
                );
            }
            return $resultRedirect->setPath(
                '*/*/bankdetails',
                ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
            );
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\RuntimeException $e) {
            $this->messageManager->addError($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addException(
                $e,
                __($e->getMessage())
            );
        }
        return $resultRedirect->setPath(
            '*/*/bankdetails',
            ['sender_type'=>Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE]
        );
    }

    public function refundAmountToWallet($params)
    {
        if (isset($params['selected'])) {
            $ids = $params['selected'];
            $this->refundSelectedTransactions($ids);
        } else {
            $this->refundAllPendingTransactions();
        }
        return;
    }

    public function refundAllPendingTransactions()
    {
        $txns = $this->_walletTransaction->create()->getCollection()
                                ->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
        foreach ($txns as $txn) {
            $this->creditAmountToCustomerWallet($txn->getEntityId());
        }
        return;
    }
    public function refundSelectedTransactions($ids)
    {
        $txns = $this->_walletTransaction->create()->getCollection()
                                ->addFieldToFilter('entity_id', ['in'=>$ids])
                                ->addFieldToFilter('status', Wallettransaction::WALLET_TRANS_STATE_PENDING);
        foreach ($txns as $txn) {
            $this->creditAmountToCustomerWallet($txn->getEntityId());
        }
        return;
    }

    public function creditAmountToCustomerWallet($txnId)
    {
        $walletTransaction  = $this->_walletTransaction->create();
        $txnDetails = $walletTransaction->getCollection()
        ->addFieldToFilter('entity_id', $txnId);
        foreach ($txnDetails as $txnDetails) {
            $txn = $txnDetails;
        }
        $baseUrl = $this->websiteRepositiry->getDefault()->getDefaultStore()->getBaseUrl();
        $url = $baseUrl."walletsystem/index/view/entity_id/".$txnId;
        $link = "<a href='".$url."'> #".$txnId."</a>";
        $amount = $txn->getAmount();
        $customerId = $txn->getCustomerId();
        $currencycode = $this->_walletHelper->getBaseCurrencyCode();
        $params['curr_code'] = $currencycode;
        $params['walletactiontype'] = "credit";
        $params['curr_amount'] = $amount;
        $params['walletamount'] = $amount;
        $params['sender_id'] = 0;
        $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
        $params['order_id'] = 0;
        $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
        $params['increment_id'] = '';
        $params['customerid'] = $customerId;
        $params['walletnote'] = __("Request To transfer amount to Bank is cancelled").$link;
        $result = $this->_walletUpdate->creditAmount($customerId, $params);
    }
}

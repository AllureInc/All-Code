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
use Magento\Framework\Controller\ResultFactory;
use Magento\Backend\App\Action;
use Webkul\Walletsystem\Model\WalletrecordFactory;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;

class Disapprove extends WalletController
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
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $_walletUpdate;

    /**
     * @param ActionContext                          $context
     * @param WalletrecordFactory                    $walletrecord
     * @param WallettransactionFactory               $transactionFactory
     * @param WebkulWalletsystemHelperData           $walletHelper
     * @param MagentoFrameworkStdlibDateTimeDateTime $date
     * @param WebkulWalletsystemHelperMail           $mailHelper
     * @param WalletUpdateData                       $walletUpdate
     */
    public function __construct(
        Action\Context $context,
        WalletrecordFactory $walletrecord,
        WallettransactionFactory $transactionFactory,
        \Magento\Store\Api\WebsiteRepositoryInterface $websiteRepositiry,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        WalletUpdateData $walletUpdate
    ) {
        $this->_walletrecord = $walletrecord;
        $this->_walletTransaction = $transactionFactory;
        $this->_walletHelper = $walletHelper;
        $this->_date = $date;
        $this->_mailHelper = $mailHelper;
        $this->websiteRepositiry = $websiteRepositiry;
        $this->_walletUpdate = $walletUpdate;
        parent::__construct($context);
    }

    /**
     * @return \Magento\Backend\Model\View\Result\Page
     */
    public function execute()
    {
        $successCounter = 0;
        $params = $this->getRequest()->getParams();

        $walletTransactionModel = $this->_walletTransaction->create();
        $resultRedirect = $this->resultRedirectFactory->create();
        if (is_array($params) && array_key_exists('entity_id', $params) &&
            $params['entity_id'] != '') {
            $condition = "`entity_id`=".$params['entity_id'];
            $statusCollection = $this->_walletTransaction->create()->getCollection()
                                                    ->addFieldToFilter('entity_id', $params['entity_id']);
            foreach ($statusCollection as $status) {
                $status = $status->getStatus();
            }
            if ($status == $walletTransactionModel::WALLET_TRANS_STATE_CANCEL) {
                $this->messageManager->addSuccess(
                    __('Transaction status is updated.')
                );
                return $resultRedirect->setPath(
                    'walletsystem/wallet/view',
                    ['entity_id'=>$params['entity_id']]
                );
            }
            $this->_walletTransaction->create()->getCollection()->setTableRecords(
                $condition,
                ['status' => $walletTransactionModel::WALLET_TRANS_STATE_CANCEL]
            );
            $this->creditAmountToCustomerWallet($params['entity_id']);
            $this->messageManager->addSuccess(
                __('Transaction status is updated.')
            );
        } else {
            $this->messageManager->addError(
                __('Something went wrong, please try again.')
            );
            return $resultRedirect->setPath(
                'walletsystem/wallet/index'
            );
        }
        return $resultRedirect->setPath(
            'walletsystem/wallet/view',
            ['entity_id'=>$params['entity_id']]
        );
    }

    public function creditAmountToCustomerWallet($txnId)
    {
        $walletTransaction  = $this->_walletTransaction->create();
        $txnDetails = $walletTransaction->getCollection()
        ->addFieldToFilter('entity_id', $txnId);
        foreach ($txnDetails as $txnDetails) {
            $txn = $txnDetails;
        }
        $amount = $txn->getAmount();
        $customerId = $txn->getCustomerId();
        
        $baseUrl = $this->websiteRepositiry->getDefault()->getDefaultStore()->getBaseUrl();
        $url = $baseUrl."walletsystem/index/view/entity_id/".$txnId;
        $link = "<a href='".$url."'> #".$txnId."</a>";
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

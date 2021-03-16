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

namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Walletsystem\Helper\Data as WalletHelper;
use Webkul\Walletsystem\Helper\Mail as WalletMail;
use Webkul\Walletsystem\Model\WalletTransferData;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Webkul\Walletsystem\Model\Wallettransaction;

class Sendamount extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    /**
     * @var Webkul\Walletsystem\Helper\Mail
     */
    protected $_walletMail;
    /**
     * @var Webkul\Walletsystem\Model\WalletTransferData
     */
    protected $_waletTransfer;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $_walletUpdate;
    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * @param Context               $context
     * @param PageFactory           $resultPageFactory
     * @param WalletHelper          $walletHelper
     * @param WalletMail            $walletMail
     * @param WalletTransferData    $walletSession
     * @param Encryptor             $encryptor
     * @param WalletUpdateData      $walletUpdate
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WalletHelper $walletHelper,
        WalletMail $walletMail,
        WalletTransferData $walletSession,
        Encryptor $encryptor,
        WalletUpdateData $walletUpdate
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_walletHelper = $walletHelper;
        $this->_walletMail = $walletMail;
        $this->_waletTransfer = $walletSession;
        $this->encryptor = $encryptor;
        $this->_walletUpdate = $walletUpdate;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $params = $this->getRequest()->getParams();
            $error = $this->validateParams($params);
            $walletHelper = $this->_walletHelper;
            if ($error) {
                $this->messageManager->addError(__("Something went wrong, please try again."));
            }
            if (!$walletHelper->getTransferValidationEnable()) {
                $params['curr_code'] = $walletHelper->getCurrentCurrencyCode();
                $fromCurrency = $walletHelper->getCurrentCurrencyCode();
                $toCurrency = $walletHelper->getBaseCurrencyCode();
                $amount = $params['amount'];
                $transferAmount = $walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
                $totalAmount = $walletHelper->getWalletTotalAmount(0);
                if ($transferAmount <= $totalAmount) {
                    $params['base_amount'] = $transferAmount;
                    $params['curr_amount'] = $params['amount'];
                    $this->SendAmountToWallet($params);
                    $this->DeductAmountFromWallet($params);
                    $this->messageManager->addSuccess(__("Amount transferred successfully"));
                } else {
                    $this->messageManager->addError(__("You don't have enough amount in your wallet."));
                    return $this->resultRedirectFactory->create()->setPath(
                        'walletsystem/transfer/index',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            } else {
                $this->_waletTransfer->checkAndUpdateSession();
                $walletTransferData = $this->_waletTransfer->getWalletTransferDataToSession();
                if ($walletTransferData=='') {
                    throw new \Exception(__("Either code session is expired, or amount is already transferred."));
                }
                $walletCookieArray = $walletHelper->convertStringAccToVersion($walletTransferData, 'decode');
                if ($walletCookieArray['sender_id']==$params['sender_id'] &&
                    $walletCookieArray['amount']==$params['amount'] &&
                    $walletCookieArray['reciever_id']==$params['reciever_id']) {
                    if (!$this->encryptor->validateHash($params['code'], $walletCookieArray['code'])) {
                        throw new \Exception(__("Incorrect code"));
                    }
                    $params['curr_code'] = $this->_walletHelper->getCurrentCurrencyCode();
                    $params['curr_amount'] = $params['amount'];
                    $params['walletnote'] = $walletCookieArray['walletnote'];
                    $this->SendAmountToWallet($params);
                    $this->DeductAmountFromWallet($params);
                    $this->_waletTransfer->setWalletTransferDataToSession('');
                    $this->messageManager->addSuccess(__("Amount transferred successfully"));
                } else {
                    throw new \Exception(__("Something went wrong, please try again."));
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());
        }
        return $this->resultRedirectFactory->create()->setPath(
            'walletsystem/transfer/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
    // validate params data
    public function validateParams($params)
    {
        $error = 0;
        foreach ($params as $paramkey => $paramvalue) {
            switch ($paramkey) {
                case 'sender_id':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'reciever_id':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'code':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
                case 'amount':
                    if ($paramvalue=='' || $paramvalue==0) {
                        $error = 1;
                    }
                    break;
            }
        }
        return $error;
    }
    // send amount to customer's wallet
    public function SendAmountToWallet($params)
    {
        $customerModel = $this->_walletHelper->getCustomerByCustomerId($params['sender_id']);
        $senderName = $customerModel->getFirstname()." ".$customerModel->getLastname();
        if ($params['walletnote']=='') {
            $params['walletnote'] = __("Transfer by %1", $senderName);
        }
        $transferAmountData = [
            'customerid' => $params['reciever_id'],
            'walletamount' => $params['base_amount'],
            'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_CREDIT,
            'curr_code' => $params['curr_code'],
            'curr_amount' => $params['curr_amount'],
            'walletnote' => __($params['walletnote']),
            'sender_id' => $params['sender_id'],
            'sender_type' => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
        ];
        $this->_walletUpdate->creditAmount($params['reciever_id'], $transferAmountData);
    }
    // deduct amount from sender's wallet
    public function DeductAmountFromWallet($params)
    {
        $customerModel = $this->_walletHelper->getCustomerByCustomerId($params['reciever_id']);
        $recieverName = $customerModel->getFirstname()." ".$customerModel->getLastname();
        if ($params['walletnote']=='') {
            $params['walletnote'] = __("Transfer to %1", $recieverName);
        }
        $transferAmountData = [
            'customerid' => $params['sender_id'],
            'walletamount' => $params['base_amount'],
            'walletactiontype' => Wallettransaction::WALLET_ACTION_TYPE_DEBIT,
            'curr_code' => $params['curr_code'],
            'curr_amount' => $params['curr_amount'],
            'walletnote' => __($params['walletnote']),
            'sender_id' => $params['reciever_id'],
            'sender_type' => Wallettransaction::CUSTOMER_TRANSFER_TYPE,
            'order_id' => 0,
            'status' => Wallettransaction::WALLET_TRANS_STATE_APPROVE,
            'increment_id' => ''
        ];
        $this->_walletUpdate->debitAmount($params['sender_id'], $transferAmountData);
    }
}

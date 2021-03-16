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
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\Wallettransaction;

class Bankamount extends \Magento\Customer\Controller\AbstractAccount
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $walletHelper;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $_walletUpdate;

    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        WalletUpdateData $walletUpdate
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_walletNotification = $walletNotification;
        $this->walletHelper = $walletHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_walletUpdate = $walletUpdate;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->validateParams($params);
        return $this->resultRedirectFactory->create()->setPath(
            'walletsystem/index/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
    protected function validateParams($params)
    {
        if (!empty($params) && is_array($params)) {
            if (array_key_exists('customer_id', $params) && $params['customer_id']!='') {
                if (array_key_exists('amount', $params) &&
                $params['amount']!='' &&
                array_key_exists('bank_details', $params) &&
                $params['bank_details']!=''
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['walletnote'])
                && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['bank_details'])
                ) {
                    $params['walletnote'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $params['walletnote']);
                    $params['bank_details'] = preg_replace('#<script(.*?)>(.*?)</script>#is', '', $params['bank_details']);
                    $baseCurrencyCode = $this->walletHelper->getBaseCurrencyCode();
                    $currencycode = $this->walletHelper->getCurrentCurrencyCode();
                    $amount = $params['amount'];
                    $baseAmount = $this->walletHelper->getwkconvertCurrency(
                        $currencycode,
                        $baseCurrencyCode,
                        $amount
                    );
                    $customerId = $params['customer_id'];
                    $params['curr_code'] = $currencycode;
                    $params['curr_amount'] = $params['amount'];
                    $params['order_id'] = 0;
                    $params['status'] = Wallettransaction::WALLET_TRANS_STATE_PENDING;
                    $params['increment_id'] = '';
                    $params['walletamount'] = $baseAmount;
                    $params['walletactiontype'] = 'debit';
                    $params['sender_id'] = 0;
                    $params['sender_type'] = Wallettransaction::CUSTOMER_TRANSFER_BANK_TYPE;
                    $params['transfer_to_bank'] = 1;
                    if ($params['walletnote']=='') {
                        $params['walletnote'] = __('%1, Amount is transferred by customer to bank account', $params['amount']);
                    }
                    $customerId = $params['customer_id'];
                    $result = $this->_walletUpdate->debitAmount($customerId, $params);
                    if (is_array($result) && array_key_exists('success', $result)) {
                        $this->setNotificationMessageForAdmin();
                        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                        if ($this->scopeConfig->getValue('walletsystem/message_after_request/show_message', $storeScope)) {
                            $message = $this->scopeConfig->getValue('walletsystem/message_after_request/message_content', $storeScope);
                            $this->messageManager->addSuccess(__($message));
                        } else {
                            $this->messageManager->addSuccess(__("Amount transfer request has been sent!"));
                        }
                    }
                } else {
                    $this->messageManager->addError(__("Something went wrong, please try again"));
                }
            } else {
                $this->messageManager->addError(__("Something went wrong, please try again"));
            }
        } else {
            $this->messageManager->addError(__("Something went wrong, please try again"));
        }
    }

    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->_walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->_walletNotification->setBanktransferCounter(1);
            $this->_walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setBanktransferCounter($notification->getBanktransferCounter()+1);
            }
        }
        $notificationModel->save();
    }
}

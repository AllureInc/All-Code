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

class Singleaddamount extends WalletController
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
        $resultRedirect = $this->resultRedirectFactory->create();
        if (array_key_exists('customerid', $params) &&
            $params['customerid'] != '') {
            if (array_key_exists('walletamount', $params) &&
                $params['walletamount']!= '' &&
                $params['walletamount'] > 0) {
                $currencycode = $this->_walletHelper->getBaseCurrencyCode();
                $params['curr_code'] = $currencycode;
                $params['curr_amount'] = $params['walletamount'];
                $params['sender_id'] = 0;
                $params['sender_type'] = 3;
                $params['order_id'] = 0;
                $params['status'] = 1;
                $params['increment_id'] = '';
                $customerId = $params['customerid'];
                $totalAmount = 0;
                $remainingAmount = 0;
                $params['walletnote'] = $this->_walletHelper->validateScriptTag($params['walletnote']);
                if ($params['walletnote']=='') {
                    $params['walletnote'] = __('Amount %1ed by Admin', $params['walletactiontype']);
                }
                if ($params['walletactiontype']=='debit') {
                    $result = $this->_walletUpdate->debitAmount($customerId, $params);
                } else {
                    $result = $this->_walletUpdate->creditAmount($customerId, $params);
                }
                if (array_key_exists('success', $result)) {
                    $this->messageManager->addSuccess(
                        __("Amount is successfully updated in customer's wallet.")
                    );
                }
            } else {
                $this->messageManager->addError(
                    __('Please Enter a valid amount to add.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Please select Customers to add amount.')
            );
        }
        return $resultRedirect->setPath('walletsystem/wallet/addamount');
    }
}

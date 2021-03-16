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
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\WallettransactionFactory;

class Massaddamount extends WalletController
{
    /**
     * @var Webkul\Walletsystem\Model\WalletrecordFactory
     */
    private $_walletrecord;

    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    private $_walletTransaction;

    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    private $_date;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    private $_mailHelper;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    private $_walletUpdate;
    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

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
        WalletUpdateData $walletUpdate,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder
    ) {
        $this->_walletrecord = $walletrecord;
        $this->_walletTransaction = $transactionFactory;
        $this->_walletHelper = $walletHelper;
        $this->_date = $date;
        $this->_mailHelper = $mailHelper;
        $this->_walletUpdate = $walletUpdate;
        $this->_jsonDecoder = $jsonDecoder;
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
        $walletTransaction  = $this->_walletTransaction->create();
        if (array_key_exists('wkcustomerids', $params) && $params['wkcustomerids'] != '') {
            if (array_key_exists('walletamount', $params) &&
                $params['walletamount']!= '' &&
                $params['walletamount'] > 0) {
                $customerIds = array_flip($this->_jsonDecoder->decode($params['wkcustomerids']));
                $currencycode = $this->_walletHelper->getBaseCurrencyCode();
                $params['curr_code'] = $currencycode;
                $params['curr_amount'] = $params['walletamount'];
                $params['sender_id'] = 0;
                $params['sender_type'] = $walletTransaction::ADMIN_TRANSFER_TYPE;
                $params['order_id'] = 0;
                $params['status'] = $walletTransaction::WALLET_TRANS_STATE_APPROVE;
                $params['increment_id'] = '';
                $params['walletnote'] = $this->_walletHelper->validateScriptTag($params['walletnote']);
                if ($params['walletnote']=='') {
                    $params['walletnote'] = __('Amount %1ed by Admin', $params['walletactiontype']);
                }
                foreach ($customerIds as $customerId) {
                    if ($params['walletactiontype']==$walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                        $result = $this->_walletUpdate->debitAmount($customerId, $params);
                    } else {
                        $result = $this->_walletUpdate->creditAmount($customerId, $params);
                    }
                    if (array_key_exists('success', $result)) {
                        $successCounter++;
                    }
                }

                if ($successCounter > 0) {
                    $this->messageManager->addSuccess(
                        __("Total of %1 Customer(s) wallet are updated", $successCounter)
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

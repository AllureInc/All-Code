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
use Magento\Framework\Encryption\EncryptorInterface as Encryptor;
use Webkul\Walletsystem\Model\WalletTransferData;

class SendCode extends \Magento\Customer\Controller\AbstractAccount
{
    const SYMBOLS_COLLECTION = '0123456789';
    /**
     * The minimum length of the default
     */
    const DEFAULT_LENGTH = 6;
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
     * @var Encryptor
     */
    private $encryptor;
    /**
     * @var Webkul\Walletsystem\Model\WalletTransferData
     */
    protected $_waletTransfer;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @param Context                                $context
     * @param PageFactory                            $resultPageFactory
     * @param WalletHelper                           $walletHelper
     * @param WalletMail                             $walletMail
     * @param Encryptor                              $encryptor
     * @param WalletTransferData                     $walletTransfer
     * @param MagentoFrameworkStdlibDateTimeDateTime $date
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        WalletHelper $walletHelper,
        WalletMail $walletMail,
        Encryptor $encryptor,
        WalletTransferData $walletTransfer,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_walletHelper = $walletHelper;
        $this->_walletMail = $walletMail;
        $this->encryptor = $encryptor;
        $this->_waletTransfer = $walletTransfer;
        $this->_date = $date;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        try {
            $walletHelper = $this->_walletHelper;
            $params = $this->getRequest()->getParams();
            if (!$walletHelper->getTransferValidationEnable()) {
                $this->_forward('sendamount');
            } else {
                if (array_key_exists('created_at', $params)) {
                    if (!$this->updateSession()) {
                        $this->messageManager->addError("Session expired for this transaction, please try again");
                        return $this->resultRedirectFactory->create()->setPath(
                            'walletsystem/transfer/index',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                }
                $status = 0;
                $duration = $walletHelper->getCodeValidationDuration();
                $fromCurrency = $walletHelper->getCurrentCurrencyCode();
                $toCurrency = $walletHelper->getBaseCurrencyCode();
                $params['walletnote'] = $walletHelper->validateScriptTag($params['walletnote']);
                $amount = $params['amount'];
                if ($amount != 0 && $params['reciever_id'] != 0 && $params['reciever_id'] != '') {
                    $transferAmount = $walletHelper->getwkconvertCurrency($fromCurrency, $toCurrency, $amount);
                    $totalAmount = $walletHelper->getWalletTotalAmount($params['sender_id']);
                    if ($transferAmount <= $totalAmount) {
                        $params['base_amount'] = $transferAmount;
                        $data = $this->sendEmailForCode($params);
                        $sessionData = [
                            'sender_id' => $data['customer_id'],
                            'reciever_id' => $params['reciever_id'],
                            'code' => $this->createCodeHash($data['code']),
                            'amount' => $params['amount'],
                            'base_amount' => $transferAmount,
                            'walletnote' => $params['walletnote'],
                            'created_at' => strtotime($this->_date->gmtDate())
                        ];
                        $serializedData = $walletHelper->convertStringAccToVersion($sessionData, 'encode');
                        $this->_waletTransfer->setWalletTransferDataToSession($serializedData);
                        $status = 1;
                        unset($sessionData['code']);
                        $getParamData = urlencode(base64_encode(json_encode($sessionData)));
                        $sendData = [
                            'parameter' => $getParamData
                        ];
                        $this->messageManager->addSuccess(__("Code has been sent to your email id."));
                        return $this->resultRedirectFactory->create()->setPath(
                            'walletsystem/transfer/verificationCode',
                            ['_secure' => $this->getRequest()->isSecure(), '_query' => $sendData]
                        );
                    } else {
                        $this->messageManager->addError("You don't have enough amount in your wallet.");
                    }
                } else {
                    $this->messageManager->addError("Please try again with valid data.");
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
    // send email to customer for code
    public function sendEmailForCode($params)
    {
        $walletEmail = $this->_walletMail;
        $data = [
            'customer_id' => $this->_walletHelper->getCustomerId(),
            'amount' => $params['amount'],
            'base_amount' => $params['base_amount'],
            'code' => $this->generateCode(),
            'duration'=> $this->_walletHelper->getCodeValidationDuration()
        ];
        $walletEmail->sendTransferCode($data);
        return $data;
    }
    // generate a code to send code
    public function generateCode()
    {
        $alphabet = self::SYMBOLS_COLLECTION;
        $length = self::DEFAULT_LENGTH;
        $code = '';
        for ($i = 0, $indexMax = strlen($alphabet) - 1; $i < $length; ++$i) {
            $code .= substr($alphabet, mt_rand(0, $indexMax), 1);
        }
        return $code;
    }
    // create hash code
    protected function createCodeHash($code)
    {
        return $this->encryptor->getHash($code, true);
    }
    // check session code value espires or not
    public function updateSession()
    {
        $this->_waletTransfer->checkAndUpdateSession();
        $walletTransferData = $this->_waletTransfer->getWalletTransferDataToSession();
        if ($walletTransferData=='') {
            return false;
        }
        return true;
    }
}

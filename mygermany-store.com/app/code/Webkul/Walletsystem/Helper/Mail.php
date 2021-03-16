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

namespace Webkul\Walletsystem\Helper;

class Mail extends Data
{
    /**
     * @var templateId
     */
    protected $_tempId;

    protected function generateTemplate($emailTemplateVariables, $senderInfo, $receiverInfo)
    {
        $template =  $this->_transportBuilder->setTemplateIdentifier($this->_tempId)
            ->setTemplateOptions(
                [
                    'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                    'store' => $emailTemplateVariables['store_id'],
                ]
            )
            ->setTemplateVars($emailTemplateVariables)
            ->setFrom($senderInfo, 'store', $emailTemplateVariables['store_id'])
            ->addTo($receiverInfo['email'], $receiverInfo['name']);
        return $this;
    }
    // calls when invoice generated for an order either on create invoice or by capture method
    public function checkAndUpdateWalletAmount($order)
    {
        $walletTransaction = $this->_walletTransaction->create();
        if (count($order->getInvoiceCollection())) {
            $orderId = $order->getId();
            if ($orderId) {
                $totalAmount = 0;
                $remainingAmount = 0;
                $orderModel = $this->_orderModel
                    ->create()
                    ->load($orderId);
                $incrementId = $order->getIncrementId();
                $orderItem = $orderModel->getAllItems();
                $productIdArray = [];
                foreach ($orderItem as $value) {
                    $productIdArray[] = $value->getProductId();
                }
                $walletProductId = $this->getWalletProductId();
                if (in_array($walletProductId, $productIdArray)) {
                    $walletCollection = $this->_walletTransaction
                        ->create()
                        ->getCollection()
                        ->addFieldToFilter('order_id', ['eq' => $orderId])
                        ->addFieldToFilter('status', 0);
                    if (count($walletCollection)) {
                        foreach ($walletCollection as $record) {
                            $rowId = $record->getId();
                            $customerId = $record->getCustomerId();
                            $amount = $record->getAmount();
                            $action = $record->getAction();
                        }
                        $data = ['status' => 1];
                        $walletTansactionModel = $this->_walletTransaction
                            ->create()
                            ->load($rowId)
                            ->addData($data);
                        $walletTansactionModel->setId($rowId)->save();
                        $walletRecordCollection = $this->_walletRecordFactory
                            ->create()
                            ->getCollection()
                            ->addFieldToFilter(
                                'customer_id',
                                ['eq' => $customerId]
                            );
                        if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                            $this->updateWalletDataAmount($walletRecordCollection, $amount, $customerId, $incrementId);
                        }
                    }
                }
            }
        }
    }
    public function updateWalletDataAmount($walletRecordCollection, $amount, $customerId, $incrementId)
    {
        $remainingAmount = 0;
        $totalAmount = 0;
        $walletTransaction = $this->_walletTransaction->create();
        if (count($walletRecordCollection)) {
            foreach ($walletRecordCollection as $record) {
                $totalAmount = $record->getTotalAmount();
                $remainingAmount = $record->getRemainingAmount();
                $recordId = $record->getId();
            }
            $data = [
                'total_amount' => $amount + $totalAmount,
                'remaining_amount' => $amount + $remainingAmount,
                'updated_at' => $this->_date->gmtDate()
            ];
            $walletRecordModel = $this->_walletRecordFactory
                ->create()
                ->load($recordId)
                ->addData($data);
            $saved = $walletRecordModel->setId($recordId)->save();
        } else {
            $walletRecordModel = $this->_walletRecordFactory
                ->create();
            $walletRecordModel->setTotalAmount($amount + $totalAmount)
                ->setCustomerId($customerId)
                ->setRemainingAmount($amount + $remainingAmount)
                ->setUpdatedAt($this->_date->gmtDate());
            $saved = $walletRecordModel->save();
        }
        if ($saved->getId() != 0) {
            $date = $this->_localeDate->date();
            $formattedDate = $this->_date->gmtDate();
            $finalAmount = $amount + $remainingAmount;
            $emailParams = [
                'walletamount' => $this->getformattedPrice($amount),
                'remainingamount' => $this->getformattedPrice($finalAmount),
                'type' => $walletTransaction::ORDER_PLACE_TYPE,
                'action' => $walletTransaction::WALLET_ACTION_TYPE_CREDIT,
                'increment_id' => $incrementId,
                'transaction_at' => $formattedDate
            ];
            $store = $this->_storeManager->getStore();
            $this->sendMailForTransaction(
                $customerId,
                $emailParams,
                $store
            );
        }
    }
    public function sendTransferCode($mailData)
    {
        try {
            $customer = $this->_customerModel
                ->create()
                ->load($mailData['customer_id']);
            $emailTempVariables = [];
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['code'] = $mailData['code'];
            $emailTempVariables['duration'] = $mailData['duration'];
            $emailTempVariables['amount'] = $this->getformattedPrice($mailData['base_amount']);
            $adminEmail= $this->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $adminUsername = $objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

            $senderInfo = [];
            $receiverInfo = [];
            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $storeId = $this->getWebsiteStoreId($customer);
            if (!$storeId) {
                $storeId = $this->_storeManager->getStore()->getId();
            }
            $emailTempVariables['store'] = $this->_storeManager->getStore($storeId);
            $emailTempVariables['store_id'] = $storeId;

            $this->_tempId = $this->getCustomerAmountTransferOTPTemplateId();
            $this->_inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    public function sendMonthlyTransaction($mailData)
    {
        $currency = $this->getBaseCurrencyCode();
        try {
            $customer = $this->_customerModel
                ->create()
                ->load($mailData['customer_id']);
            $emailTempVariables = [];
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['date'] = $mailData['month'].", ".$mailData['year'];
            $emailTempVariables['emailid'] = $customer->getEmail();
            $emailTempVariables['month'] = $mailData['month'];
            $emailTempVariables['year'] = $mailData['year'];
            $emailTempVariables['openingbalance'] = $this->getFormattedPriceAccToCurrency(
                $mailData['openingbalance'],
                2,
                $currency
            );
            $emailTempVariables['closingbalance'] = $this->getFormattedPriceAccToCurrency(
                $mailData['closingbalance'],
                2,
                $currency
            );
            $emailTempVariables['rechargewallet'] = $this->getFormattedPriceAccToCurrency(
                $mailData['rechargewallet'],
                2,
                $currency
            );
            $emailTempVariables['cashbackamount'] = $this->getFormattedPriceAccToCurrency(
                $mailData['cashbackamount'],
                2,
                $currency
            );
            $emailTempVariables['refundamount'] = $this->getFormattedPriceAccToCurrency(
                $mailData['refundamount'],
                2,
                $currency
            );
            $emailTempVariables['admincredit'] = $this->getFormattedPriceAccToCurrency(
                $mailData['admincredit'],
                2,
                $currency
            );
            $emailTempVariables['customercredits'] = $this->getFormattedPriceAccToCurrency(
                $mailData['customercredits'],
                2,
                $currency
            );
            $emailTempVariables['usedwallet'] = $this->getFormattedPriceAccToCurrency(
                $mailData['usedwallet'],
                2,
                $currency
            );
            $emailTempVariables['refundwalletorder'] = $this->getFormattedPriceAccToCurrency(
                $mailData['refundwalletorder'],
                2,
                $currency
            );
            $emailTempVariables['admindebit'] = $this->getFormattedPriceAccToCurrency(
                $mailData['admindebit'],
                2,
                $currency
            );
            $emailTempVariables['transfertocustomer'] = $this->getFormattedPriceAccToCurrency(
                $mailData['transfertocustomer'],
                2,
                $currency
            );
            $emailTempVariables['transfertobank'] = $this->getFormattedPriceAccToCurrency(
                $mailData['transfertobank'],
                2,
                $currency
            );
            $storeId = $this->getWebsiteStoreId($customer);
            if (!$storeId) {
                $storeId = $this->_storeManager->getStore()->getId();
            }
            $emailTempVariables['store'] = $this->_storeManager->getStore($storeId);
            $emailTempVariables['store_id'] = $storeId;

            $adminEmail= $this->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $adminUsername = $objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->_tempId = $this->getMonthlystatementTemplateId();
            $this->_inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            // $this->logger->info($e->getMessage());
        }
    }
    public function sendMailForTransaction($customerId, $params, $store)
    {
        $walletTransaction = $this->_walletTransaction->create();
        $type = $params['type'];
        $action = $params['action'];
        $customer = $this->_customerModel
            ->create()
            ->load($customerId);
        if (array_key_exists('sender_id', $params) && $params['sender_id']>0 && $params['type']==$walletTransaction::CUSTOMER_TRANSFER_TYPE) {
            $sender = $this->_customerModel
                ->create()
                ->load($params['sender_id']);
            $params['sender'] = $sender->getName();
        }
        $this->sendEmailToCustomer($customer, $params, $store, $type, $action);
        $this->sendEmailToAdmin($customer, $params, $store, $type, $action);
    }
    public function sendEmailToAdmin($customer, $params, $store, $type, $action)
    {
        $emailTemplateId = $this->getMailTemplateForTransactionForAdmin($type, $action);
        try {
            $emailTempVariables = $params;
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['store'] = $store;
            $emailTempVariables['store_id'] = $store->getId();

            $adminEmail= $this->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $adminUsername = $objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->_tempId = $emailTemplateId;
            $this->_inlineTranslation->suspend();
            $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
    public function sendEmailToCustomer($customer, $params, $store, $type, $action)
    {
        $emailTemplateId = $this->getMailTemplateForTransactionForCustomer($type, $action);
        try {
            $emailTempVariables = $params;
            $emailTempVariables['customername'] = $customer->getName();
            $emailTempVariables['store'] = $store;
            $emailTempVariables['email'] = $customer->getEmail();
            $emailTempVariables['store_id'] = $store->getId();

            $adminEmail= $this->getDefaultTransEmailId();
            /*$adminUsername = 'Admin';*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $adminUsername = $objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();
            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $customer->getName(),
                'email' => $customer->getEmail(),
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $this->_tempId = $emailTemplateId;
            $this->_inlineTranslation->suspend();
            $this->generateTemplate(
                $emailTempVariables,
                $senderInfo,
                $receiverInfo
            );
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
            $this->_inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
    public function getMailTemplateForTransactionForCustomer($type, $action)
    {
        $walletTransaction = $this->_walletTransaction->create();
        if ($type == $walletTransaction::ORDER_PLACE_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletRechargeTemplateIdForCustomer();
            } else {
                return $this->getWalletUsedTemplateIdForCustomer();
            }
        } elseif ($type == $walletTransaction::CASH_BACK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletCashbackTemplateIdForCustomer();
            } else {
            }
        } elseif ($type == $walletTransaction::REFUND_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletOrderRefundTemplateIdForCustomer();
            } else {
                return $this->getWalletAmountRefundTemplateIdForCustomer();
            }
        } elseif ($type == $walletTransaction::ADMIN_TRANSFER_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getAdminCreditAmountTemplateIdForCustomer();
            } else {
                return $this->getAdminDebitAmountTemplateIdForCustomer();
            }
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getCustomerCreditAmountTemplateIdForCustomer();
            } else {
                return $this->getCustomerDebitAmountTemplateIdForCustomer();
            }
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_BANK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                return $this->getCustomerBankTansferAmountTemplateIdForCustomer();
            } else {
            }
        }
    }
    public function getMailTemplateForTransactionForAdmin($type, $action)
    {
        $walletTransaction = $this->_walletTransaction->create();
        if ($type == $walletTransaction::ORDER_PLACE_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletRechargeTemplateIdForAdmin();
            } else {
                return $this->getWalletUsedTemplateIdForAdmin();
            }
        } elseif ($type == $walletTransaction::CASH_BACK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletCashbackTemplateIdForAdmin();
            } else {
            }
        } elseif ($type == $walletTransaction::REFUND_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getWalletOrderRefundTemplateIdForAdmin();
            } else {
                return $this->getWalletAmountRefundTemplateIdForAdmin();
            }
        } elseif ($type == $walletTransaction::ADMIN_TRANSFER_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getAdminCreditAmountTemplateIdForAdmin();
            } else {
                return $this->getAdminDebitAmountTemplateIdForAdmin();
            }
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_CREDIT) {
                return $this->getCustomerCreditAmountTemplateIdForAdmin();
            } else {
                return $this->getCustomerDebitAmountTemplateIdForAdmin();
            }
        } elseif ($type == $walletTransaction::CUSTOMER_TRANSFER_BANK_TYPE) {
            if ($action == $walletTransaction::WALLET_ACTION_TYPE_DEBIT) {
                return $this->getCustomerBankTansferAmountTemplateIdForAdmin();
            } else {
            }
        }
    }
    private function getWebsiteStoreId($customer, $defaultStoreId = null)
    {
        if ($customer->getWebsiteId() != 0 && empty($defaultStoreId)) {
            $storeIds = $this->_storeManager->getWebsite($customer->getWebsiteId())->getStoreIds();
            $defaultStoreId = reset($storeIds);
        }
        return $defaultStoreId;
    }

    /**
     * send mail to all approved transactions
     * @param wallet transfer collection $collection
     **/
    public function sendCustomerBulkTransferApproveMail($collection)
    {
        foreach ($collection->getData() as $customerToMailDetails) {
            $customerId = $customerToMailDetails['customer_id'];
            $customerData = $this->_customerModel->create()->load($customerId);

            $walletAmount = $customerToMailDetails['amount'];
            $bankDetails = $customerToMailDetails['bank_details'];
            $customerEmail = $customerData['email'];
            $customerName = $customerData['firstname'].' '.$customerData['lastname'];
            $finalData = [
                'name' => $customerName,
                'walletAmount' => $walletAmount,
                'bankDetails' => $bankDetails
            ];
            $this->sendAmountToBankMail($finalData, $customerEmail);
        }
    }

    public function sendAmountToBankMail($finalData, $customerEmail)
    {
        $emailTempVariables = $finalData;
        $emailTempVariables['store_id'] = $this->_storeManager->getStore()->getId();
        $emailTempVariables['message'] = "Your request to transfer amount ".$finalData['walletAmount'];
        $emailTempVariables['message'] = $emailTempVariables['message']." in your bank account ";
        $emailTempVariables['message'].= $emailTempVariables['bankDetails']." has been approved";
        $adminEmail= $this->getDefaultTransEmailId();
        /*$adminUsername = 'Admin';*/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $adminUsername = $objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();
        $senderInfo = [];
        $receiverInfo = [];

        $receiverInfo = [
            'name' => $finalData['name'],
            'email' => $customerEmail,
        ];
        $senderInfo = [
            'name' => $adminUsername,
            'email' => $adminEmail,
        ];
        $emailTemplateId = $this->scopeConfig->getValue(
            'walletsystem/email_template/customer_bank_transfer_approve',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
        $this->_tempId = $emailTemplateId;
        $this->_inlineTranslation->suspend();
        try {
            $this->generateTemplate($emailTempVariables, $senderInfo, $receiverInfo);
            $transport = $this->_transportBuilder->getTransport();
            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }
}

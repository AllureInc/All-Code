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

use \Webkul\Walletsystem\Model\ResourceModel\Wallettransaction\CollectionFactory as WalletTransactionCollection;

class Cronjob
{
    /**
     * @var WebkulWalletsystemHelperData
     */
    protected $_walletHelper;
    /**
     * @var MagentoCustomerModelCustomerFactory
     */
    protected $_customerModel;
    /**
     * @var WebkulWalletsystemModelWallettransactionFactory
     */
    protected $_walletTransaction;
    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;
    /**
     * @var WalletTransactionCollection
     */
    private $_walletTransactionCollection;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $_mailHelper;
    /**
     * @var  \Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timeZone;

    /**
     * @param WebkulWalletsystemHelperData                    $walletHelper
     * @param MagentoCustomerModelCustomerFactory             $customerFactory
     * @param WebkulWalletsystemModelWallettransactionFactory $walletTransactionFactory
     * @param MagentoFrameworkStdlibDateTimeDateTime          $date
     * @param WalletTransactionCollection                     $walletTransactionCollection
     * @param WebkulWalletsystemHelperMail                    $mailHelper
     * @param MagentoFrameworkStdlibDateTimeTimezoneInterface $timeZone
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Webkul\Walletsystem\Model\WallettransactionFactory $walletTransactionFactory,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        WalletTransactionCollection $walletTransactionCollection,
        \Webkul\Walletsystem\Helper\Mail $mailHelper,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezone
    ) {
        $this->_walletHelper = $walletHelper;
        $this->_customerModel = $customerFactory;
        $this->_walletTransaction = $walletTransactionFactory;
        $this->_walletTransactionCollection = $walletTransactionCollection;
        $this->_date = $date;
        $this->_mailHelper = $mailHelper;
        $this->timeZone = $timezone;
    }

    public function execute()
    {
        $helper = $this->_walletHelper;
        if ($helper->getcronEnable()) {
            $customerCollection = $this->_customerModel
                ->create()->getCollection();
            if ($customerCollection->getSize()) {
                foreach ($customerCollection as $customer) {
                    $customerId = $customer->getEntityId();
                    $walletTotalAmount = $helper->getWalletTotalAmount($customerId);
                    $this->sendCustomerEmailForMonthlyStatement($customerId);
                }
            }
        }
        return $this;
    }

    public function sendCustomerEmailForMonthlyStatement($customerId)
    {
        $helper = $this->_walletHelper;
        $closingBalance = $helper->getWalletTotalAmount($customerId);
        $storeScopeTimeZone = $this->timeZone->getConfigTimezone();

        $firstDay = new \DateTime('first day of last month', new \DateTimeZone($storeScopeTimeZone));
        $firstDay = $firstDay->format('Y-m-d');

        $lastDay = new \DateTime('last day of last month', new \DateTimeZone($storeScopeTimeZone));
        $lastDay = new \DateTime($lastDay->format('Y-m-d').'+1 day', new \DateTimeZone($storeScopeTimeZone));

        $lastDay = $lastDay->format('Y-m-d');
        
        $lastMonth = new \DateTime('last month', new \DateTimeZone($storeScopeTimeZone));
        $month = $lastMonth->format('F');
        $year = $lastMonth->format('Y');

        $walletDataForCustomer = $this->_walletTransactionCollection->create()
            ->getMonthlyTransactionDetails($customerId, $firstDay, $lastDay);
        if (array_key_exists(0, $walletDataForCustomer)) {
            $mailData = $walletDataForCustomer[0];
            $openingBalance = $closingBalance + $mailData['totaldebit'] - $mailData['totalcredit'];
            $mailData['month'] = $month;
            $mailData['year'] = $year;
            $mailData['openingbalance'] = $openingBalance;
            $mailData['closingbalance'] = $closingBalance;
            $mailData['customer_id'] = $customerId;
            $this->_mailHelper->sendMonthlyTransaction($mailData);
        } else {
            $mailData['totaldebit'] = 0;
            $mailData['totalcredit'] = 0;
            $openingBalance = $closingBalance + $mailData['totaldebit'] - $mailData['totalcredit'];
            $mailData['month'] = $month;
            $mailData['year'] = $year;
            $mailData['openingbalance'] = $openingBalance;
            $mailData['closingbalance'] = $closingBalance;
            $mailData['customer_id'] = $customerId;
            $mailData['rechargewallet'] = 0;
            $mailData['cashbackamount'] = 0;
            $mailData['refundamount'] = 0;
            $mailData['admincredit'] = 0;
            $mailData['customercredits'] = 0;
            $mailData['usedwallet'] = 0;
            $mailData['refundwalletorder'] = 0;
            $mailData['admindebit'] = 0;
            $mailData['transfertocustomer'] = 0;
            $mailData['transfertobank'] = 0;
            $this->_mailHelper->sendMonthlyTransaction($mailData);
        }
    }
}

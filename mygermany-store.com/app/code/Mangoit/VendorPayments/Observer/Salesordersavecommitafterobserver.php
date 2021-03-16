<?php
namespace Mangoit\VendorPayments\Observer;

class Salesordersavecommitafterobserver extends \Webkul\Marketplace\Observer\SalesOrderSaveCommitAfterObserver
{
    protected $_objectManager;

    public function getObjectManager()
    {
        return \Magento\Framework\App\ObjectManager::getInstance();
    }

    /**
     * Sales order save commmit after on order complete state event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_objectManager = $this->getObjectManager();
        /** @var $orderInstance Order */

        $order = $observer->getOrder();
        $lastOrderId = $observer->getOrder()->getId();
        $helper = $this->_objectManager->get('Webkul\Marketplace\Helper\Data');

        // if ($order->getStatus() == 'processing' || $order->getStatus() == 'received' || $order->getStatus() == 'complete' || $order->getStatus() == 'order_verified') {
        // if ($order->getState() == 'complete') {
            /*
            * Calculate cod and shipping charges if applied
            */
            $paymentCode = '';
            if ($order->getPayment()) {
                $paymentCode = $order->getPayment()->getMethod();
            }

            $orderCurrency = $order->getOrderCurrencyCode();
            $baseCurrency = $order->getBaseCurrencyCode();

            $ordercollection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )
            ->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId)
            ->addFieldToFilter(
                'cpprostatus',
                \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING
            );
            foreach ($ordercollection as $item) {
                $sellerId = $item->getSellerId();
                $taxAmount = $item['total_tax'];

                $taxToSeller = $helper->getConfigTaxManage();
                $marketplaceOrders = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Orders'
                )
                ->getCollection()
                ->addFieldToFilter('order_id', $lastOrderId)
                ->addFieldToFilter('seller_id', $item['seller_id']);
                foreach ($marketplaceOrders as $tracking) {
                    $taxToSeller = $tracking['tax_to_seller'];
                }
                if (!$taxToSeller) {
                    $taxAmount = 0;
                }

                $shippingCharges = 0;
                $codCharges = $item->getCodCharges();
                /*
                 * Calculate cod and shipping charges if applied
                 */
                if ($item->getIsShipping() == 1) {
                    $marketplaceOrders = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Orders'
                    )
                    ->getCollection()
                    ->addFieldToFilter('order_id', $lastOrderId)
                    ->addFieldToFilter('seller_id', $item['seller_id']);
                    foreach ($marketplaceOrders as $tracking) {
                        $shippingamount = $tracking->getShippingCharges();
                        $refundedShippingAmount = $tracking->getRefundedShippingCharges();
                        $shippingCharges = $shippingamount - $refundedShippingAmount;
                    }
                }
                $totalTaxShipping = $taxAmount + $codCharges + $shippingCharges - $item['applied_coupon_amount'];

                $sellerData = $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Data'
                )->getSellerDataBySellerId($sellerId);

                $exchngFeesCol = $this->_objectManager->create('Mangoit\VendorPayments\Model\Exchangefees')
                    ->getCollection();
                $chargeKey = $baseCurrency.'_'.$orderCurrency;
                $exchngFeesCol->addFieldToFilter('base_to_target_currency', $chargeKey);

                $currencyExchngPrcnt = 1; //set default excgange charge to 1%.
                if($exchngFeesCol->count()) {
                    $currencyExchngPrcnt = $exchngFeesCol->getFirstItem()->getChargePercent();
                }

                $savedAmount = $item->getActualSellerAmount();
                $itemCostToWarehouse = (float)$item->getVendorToMygermanyCost();
                $itemTotalCost = $item->getTotalAmount();
                $itemTotalAmount = ($itemTotalCost + $itemCostToWarehouse);

                $paymentFeesCol = $this->_objectManager->create('Mangoit\VendorPayments\Model\Paymentfees')
                    ->getCollection();

                $sellerCountry = $sellerData->getFirstItem()->getCountryPic();

                if($paymentCode == 'wirecard_elasticengine_creditcard'){
                    $paymentFeesCol->addFieldToFilter('payment_method', 'credit_card');

                    $payementHelper = $this->_objectManager->create('Mangoit\VendorPayments\Helper\Data');
                    $transactions = $this->_objectManager
                        ->create('Magento\Sales\Api\Data\TransactionSearchResultInterface')
                        ->addOrderIdFilter($lastOrderId)
                        ->addFieldToFilter('txn_type', 'capture');
                    $txnInfo = $transactions->getFirstItem()->getAdditionalInformation();
                    $txnRawDetails = isset($txnInfo['raw_details_info'])?$txnInfo['raw_details_info']:'';
                    $txnCardNumber = isset($txnRawDetails['card-token.0.masked-account-number']) ? $txnRawDetails['card-token.0.masked-account-number'] : '';

                    $matches = [];
                    preg_match('/^[1-9][0-9]{0,6}/', $txnCardNumber, $matches);
                    $cardPreFix = $matches[0];
                    $cardTyp = $payementHelper->getCardBrand($cardPreFix);

                    $paymentFeesCol->addFieldToFilter('card_type', $cardTyp);

                    $percentOfCost = $paymentFeesCol->getFirstItem()->getPercentOfTotalPerTans();
                    $fixedAmntOfCost = $paymentFeesCol->getFirstItem()->getCostPerTans();

                    $costToMinus = ($itemTotalAmount/100) * (double)$percentOfCost;
                    // $costToMinus = ($item->getActualSellerAmount()/100) * (double)$percentOfCost;
                    $costToMinus = ($costToMinus + $fixedAmntOfCost);
                    $actualAmntToSet = $savedAmount - $costToMinus;

                    $item->setActualSellerAmount($actualAmntToSet);
                    $item->setMitsPaymentFeeAmount($costToMinus);
                } elseif ($paymentCode == 'paypal_express') {
                    $paymentFeesCol->addFieldToFilter('payment_method', 'paypal');
                    $paymentFeesCol->addFieldToFilter('effective_countries', array('in' => $sellerCountry));
                    if(!$paymentFeesCol->count()) {
                        $paymentFeesCol->clear()->getSelect()->reset('where');
                        $paymentFeesCol->addFieldToFilter('payment_method', 'paypal');
                        $paymentFeesCol->addFieldToFilter('counrty_group', 'other');
                    }

                    $percentOfCost = $paymentFeesCol->getFirstItem()->getPercentOfTotalPerTans();
                    $fixedAmntOfCost = $paymentFeesCol->getFirstItem()->getCostPerTans();

                    $costToMinus = ($itemTotalAmount/100) * (double)$percentOfCost;
                    // $costToMinus = ($item->getActualSellerAmount()/100) * (double)$percentOfCost;
                    $costToMinus = ($costToMinus + $fixedAmntOfCost);
                    $actualAmntToSet = $savedAmount - $costToMinus;

                    $item->setActualSellerAmount($actualAmntToSet);
                    $item->setMitsPaymentFeeAmount($costToMinus);
                } elseif ($paymentCode == 'coingate_merchant') {
                    $paymentFeesCol->addFieldToFilter('payment_method', 'crypto');
                    $percentOfCost = $paymentFeesCol->getFirstItem()->getPercentOfTotalPerTans();

                    $costToMinus = ($itemTotalAmount/100) * (double)$percentOfCost;
                    // $costToMinus = ($item->getActualSellerAmount()/100) * (double)$percentOfCost;
                    $item->setActualSellerAmount($savedAmount-$costToMinus);
                    $item->setMitsPaymentFeeAmount($costToMinus);
                } else {
                    $paymentFeesCol->addFieldToFilter('payment_method', 'other');
                    $percentOfCost = $paymentFeesCol->getFirstItem()->getPercentOfTotalPerTans();

                    $costToMinus = ($itemTotalAmount/100) * (double)$percentOfCost;
                    // $costToMinus = ($item->getActualSellerAmount()/100) * (double)$percentOfCost;
                    $item->setActualSellerAmount($savedAmount-$costToMinus);
                    $item->setMitsPaymentFeeAmount($costToMinus);
                }

                $amntToMinus = ($itemTotalAmount/100) * (double)$currencyExchngPrcnt;
                // $amntToMinus = ($item->getActualSellerAmount()/100) * (double)$currencyExchngPrcnt;
                $amountToProcess = $item->getActualSellerAmount() - $amntToMinus;

                $item->setActualSellerAmount($amountToProcess);
                $item->setMitsExchangeRateAmount($amntToMinus);

                /* vendor commission code : pv */
                $actualSellerAmount = $item->getActualSellerAmount();
                if (($paymentCode == 'coingate_merchant') || ($paymentCode == 'paypal_express') || ($paymentCode == 'wirecard_elasticengine_creditcard')) {
                    $this->setSellerTurnoverAmount($sellerId, $actualSellerAmount);
                }

                /* */

                $actparterprocost = $item->getActualSellerAmount() + $totalTaxShipping;
                $totalamount = $item->getTotalAmount() + $totalTaxShipping;
                $codCharges = 0;

                $collectionverifyread = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                )->getCollection();
                $collectionverifyread->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
                if ($collectionverifyread->getSize() >= 1) {
                    foreach ($collectionverifyread as $verifyrow) {
                        $totalsale = $verifyrow->getTotalSale() + $totalamount;
                        $totalremain = $verifyrow->getAmountRemain() + $actparterprocost;
                        $verifyrow->setTotalSale($totalsale);
                        $verifyrow->setAmountRemain($totalremain);
                        $verifyrow->setCommissionRate($item->getCommissionRate());
                        $totalcommission = $verifyrow->getTotalCommission() +
                        ($totalamount - $actparterprocost);
                        $verifyrow->setTotalCommission($totalcommission);
                        $verifyrow->setUpdatedAt($this->_date->gmtDate());
                        $verifyrow->save();
                    }
                } else {
                    $collectionf = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleperpartner'
                    );
                    $collectionf->setSellerId($sellerId);
                    $collectionf->setTotalSale($totalamount);
                    $collectionf->setAmountRemain($actparterprocost);
                    $collectionf->setCommissionRate($item->getCommissionRate());
                    $totalcommission = $totalamount - $actparterprocost;
                    $collectionf->setTotalCommission($totalcommission);
                    $collectionf->setCreatedAt($this->_date->gmtDate());
                    $collectionf->setUpdatedAt($this->_date->gmtDate());
                    $collectionf->save();
                }
                if ($sellerId) {
                    $ordercount = 0;
                    $feedbackcount = 0;
                    $feedcountid = 0;
                    $collectionfeed = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Feedbackcount'
                    )->getCollection()
                    ->addFieldToFilter(
                        'seller_id',
                        $sellerId
                    )->addFieldToFilter(
                        'buyer_id',
                        $order->getCustomerId()
                    );
                    foreach ($collectionfeed as $value) {
                        $feedcountid = $value->getEntityId();
                        $ordercount = $value->getOrderCount();
                        $feedbackcount = $value->getFeedbackCount();
                    }
                    $collectionfeed = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Feedbackcount'
                    )->load($feedcountid);
                    $collectionfeed->setBuyerId($order->getCustomerId());
                    $collectionfeed->setSellerId($sellerId);
                    $collectionfeed->setOrderCount($ordercount + 1);
                    $collectionfeed->setFeedbackCount($feedbackcount);
                    $collectionfeed->setCreatedAt($this->_date->gmtDate());
                    $collectionfeed->setUpdatedAt($this->_date->gmtDate());
                    $collectionfeed->save();
                }
                $item->setUpdatedAt($this->_date->gmtDate());
                $item->setCpprostatus(
                    \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_COMPLETE
                )->save();
            }
        // }
    }

    /**
    * Method for set seller turnover 
    *
    */
    public function setSellerTurnoverAmount($seller_id, $seller_product_amount)
    {
        $this->_objectManager = $this->getObjectManager();
        $model = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Saleperpartner'
                );
        if ($model->load($seller_id, 'seller_id')) {
            if (empty($model->getSellerId()) || (is_null($model->getSellerId())) ) {
                $previous_turnover = 0;
                $curret_turnover = (float) $previous_turnover + $seller_product_amount;
                $model->setSellerId($seller_id);
                $model->setSellerTurnover($curret_turnover);
                $model->save();
            } else {
                $previous_turnover = $model->getSellerTurnover();
                if ($previous_turnover == '' || $previous_turnover == 0) {
                    $previous_turnover = 0;
                }               
                $curret_turnover = (float) $previous_turnover + $seller_product_amount;               
                $model->setSellerTurnover($curret_turnover);
                $model->save();                
            }
        }

    }
}

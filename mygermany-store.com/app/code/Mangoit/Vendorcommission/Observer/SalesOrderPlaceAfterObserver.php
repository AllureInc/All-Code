<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Vendorcommission\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Session\SessionManager;
use Magento\Quote\Model\QuoteRepository;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\Marketplace\Helper\Data as MarketplaceHelper;

/**
 * Webkul Marketplace SalesOrderPlaceAfterObserver Observer Model.
 */
class SalesOrderPlaceAfterObserver extends \Webkul\Marketplace\Observer\SalesOrderPlaceAfterObserver
{

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_logger;

    /**
     * Sales Order Place After event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $this->_logger = $this->getLogger();
        
        $isMultiShipping = $this->_checkoutSession->getQuote()->getIsMultiShipping();

        if (!$isMultiShipping) {
            /** @var $orderInstance Order */
            $order = $observer->getOrder();
            $lastOrderId = $observer->getOrder()->getId();
            $this->orderPlacedOperations($order, $lastOrderId);
        } else {
            $quoteId = $this->_checkoutSession->getLastQuoteId();
            $quote = $this->_quoteRepository->get($quoteId);
            if ($quote->getIsMultiShipping() == 1 || $isMultiShipping == 1) {
                $orderIds = $this->_coreSession->getOrderIds();
                foreach ($orderIds as $ids => $orderIncId) {
                    $lastOrderId = $ids;
                    /** @var $orderInstance Order */
                    $order = $this->_orderRepository->get($lastOrderId);
                    $this->orderPlacedOperations($order, $lastOrderId);
                }
            }
        }
    }

    /**
     * Order Place Operation method.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $lastOrderId
     */
    public function orderPlacedOperations($order, $lastOrderId)
    {
        $this->productSalesCalculation($order);
        /*send placed order mail notification to seller*/

        $paymentCode = '';
        if ($order->getPayment()) {
            $paymentCode = $order->getPayment()->getMethod();
            if ($paymentCode == 'paypal_express') {
                $paymentAdditionalInfo = $order->getPayment()->getAdditionalInformation();
                $this->_logger->info('## SalesOrderSuccessObserver ##');
                $this->_logger->info(print_r($paymentAdditionalInfo['paypal_payer_email'], true));
                
                if (isset($paymentAdditionalInfo['paypal_payer_email'])) {
                    $this->_logger->info('## Customer Email: '.$this->_currentCustomer->getEmail());
                    $this->_logger->info('## paypal_payer_email: '.$paymentAdditionalInfo['paypal_payer_email']);
                    if ($this->_currentCustomer->getEmail() != $paymentAdditionalInfo['paypal_payer_email']) {
                        $newOrderStatus = 'compliance_check';
                        $order->setStatus($newOrderStatus);
                        $order->setState($newOrderStatus);
                        $order->save();

                        $adminStoremail = $this->_marketplaceHelper->getAdminEmailId();
                        $defaultTransEmailId = $this->_marketplaceHelper->getDefaultTransEmailId();
                        $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
                        /*$adminUsername = 'Admin';*/
                        $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

                        $emailVariables['order_id'] = $order->getIncrementId();
                        $emailVariables['admin_name'] = $adminEmail;
                        $emailVariables['name'] = $this->_currentCustomer->getFirstname();

                        $customer['email'] = $this->_currentCustomer->getEmail();
                        $customer['name'] = $this->_currentCustomer->getFirstname();

                        $admin = [
                            'name' => $adminUsername,
                            'email' => $adminEmail
                        ];

                        $sellerStoreId = $this->_currentCustomer->getCreatedIn();

                        if ($sellerStoreId == 'Germany') {
                            $sellerStoreId = 7;
                        } else {
                            $sellerStoreId = 1;
                        }

                        $postObjectData = $emailVariables;
                        $postObject = new \Magento\Framework\DataObject();
                        $postObject->setData($postObjectData);

                        $this->_objectManager->get(
                            'Webkul\Marketplace\Helper\Email'
                        )->sendComplianceCheckNotificationToCustomer(
                            $postObject,
                            $admin,
                            $customer,
                            $sellerStoreId
                        );

                        $this->_objectManager->get(
                            'Webkul\Marketplace\Helper\Email'
                        )->sendComplianceCheckNotificationToAdmin(
                            $postObject,
                            $customer,
                            $admin,
                            $sellerStoreId
                        );
                    }
                }
            }
        }

        $shippingInfo = '';
        $shippingDes = '';

        $billingId = $order->getBillingAddress()->getId();

        $billaddress = $this->_objectManager->create(
            'Magento\Sales\Model\Order\Address'
        )->load($billingId);
        $billinginfo = $billaddress['firstname'].'<br/>'.
        $billaddress['street'].'<br/>'.
        $billaddress['city'].' '.
        $billaddress['region'].' '.
        $billaddress['postcode'].'<br/>'.
        $this->_objectManager->create(
            'Magento\Directory\Model\Country'
        )->load($billaddress['country_id'])->getName().'<br/>T:'.
        $billaddress['telephone'];

        $order->setOrderApprovalStatus(1)->save();

        $payment = $order->getPayment()->getMethodInstance()->getTitle();

        if ($order->getShippingAddress()) {
            $shippingId = $order->getShippingAddress()->getId();
            $address = $this->_objectManager->create(
                'Magento\Sales\Model\Order\Address'
            )->load($shippingId);
            $shippingInfo = $address['firstname'].'<br/>'.
            $address['street'].'<br/>'.
            $address['city'].' '.
            $address['region'].' '.
            $address['postcode'].'<br/>'.
            $this->_objectManager->create(
                'Magento\Directory\Model\Country'
            )->load($address['country_id'])->getName().'<br/>T:'.
            $address['telephone'];
            $shippingDes = $order->getShippingDescription();
        }

        $adminStoremail = $this->_marketplaceHelper->getAdminEmailId();
        $defaultTransEmailId = $this->_marketplaceHelper->getDefaultTransEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
        /*$adminUsername = 'Admin';*/
        $adminUsername = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();

        $sellerOrder = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Orders'
        )
        ->getCollection()
        ->addFieldToFilter('order_id', $lastOrderId)
        ->addFieldToFilter('seller_id', ['neq' => 0]);

        //vendor product delivery days- Part 1 - start
        $vendorProductDelDays = '';
        if ($order->getShippingMethod() == 'warehouse_warehouse') {
            $vendorProductDelDays = unserialize($order->getVendorDeliveryDays());
        }
        $this->_logger->info('### Order Shipping Method: '.$order->getShippingMethod().'  ###');
        //vendor product delivery days- Part 1 - End

        foreach ($sellerOrder as $info) {
            $userdata = $this->_customerRepository->getById($info['seller_id']);
            /*$userdata = $this->_customerRepository->getById($this->_customerSession->getCustomerId());*/
            $username = $userdata->getFirstname();
            $useremail = $userdata->getEmail();
            $this->_logger->info("##### SalesOrderSuccessObserver #######");
            $this->_logger->info("-");
            $this->_logger->info("## Class methods: ".json_encode(get_class_methods($userdata)));
            $this->_logger->info("## getStoreId methods: ".$userdata->getStoreId());
            $this->_logger->info("## getWebsiteId methods: ".$userdata->getWebsiteId());
            $this->_logger->info("## getCreatedIn methods: ".$userdata->getCreatedIn());
            $this->_logger->info("-");
            $sellerStoreId = $userdata->getCreatedIn();
            if ($sellerStoreId == 'Germany') {
                $sellerStoreId = 7;
            } else {
                $sellerStoreId = 1;
            }
            
            $this->_logger->info("##### sellerStoreId: ".$sellerStoreId." #####");
            /*$sellerStoreId = $userdata->getStoreId();*/

            $senderInfo = [];
            $receiverInfo = [];

            $receiverInfo = [
                'name' => $username,
                'email' => $useremail,
            ];
            $senderInfo = [
                'name' => $adminUsername,
                'email' => $adminEmail,
            ];
            $totalprice = 0;
            $totalTaxAmount = 0;
            $codCharges = 0;
            $shippingCharges = 0;
            $orderinfo = '';

            $saleslistIds = [];
            $collection1 = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )->getCollection()
            ->addFieldToFilter('order_id', $lastOrderId)
            ->addFieldToFilter('seller_id', $info['seller_id'])
            ->addFieldToFilter('parent_item_id', ['null' => 'true'])
            ->addFieldToFilter('magerealorder_id', ['neq' => 0])
            ->addFieldToSelect('entity_id');

            $saleslistIds = $collection1->getData();

            $fetchsale = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            )
            ->getCollection()
            ->addFieldToFilter(
                'entity_id',
                ['in' => $saleslistIds]
            );
            $fetchsale->getSellerOrderCollection();
            foreach ($fetchsale as $res) {
                $product = $this->_productRepository->getById($res['mageproduct_id']);

                /* product name */
                $productName = $res->getMageproName();
                $result = [];
                $result = $this->getProductOptionData($res, $result);
                $productName = $this->getProductNameHtml($result, $productName);
                /* end */

                $sku = $product->getSku();
                $orderinfo = $orderinfo."<tbody><tr>
                                <td class='item-info'>".$productName."</td>
                                <td class='item-info'>".$sku."</td>
                                <td class='item-qty'>".($res['magequantity'] * 1)."</td>
                                <td class='item-price'>".
                                    $order->formatPrice(
                                        $res['magepro_price'] * $res['magequantity']
                                    ).
                                '</td>
                             </tr></tbody>';
                $totalTaxAmount = $totalTaxAmount + $res['total_tax'];
                $totalprice = $totalprice + ($res['magepro_price'] * $res['magequantity']);

                /*
                * Low Stock Notification mail to seller
                */
                if ($this->_marketplaceHelper->getlowStockNotification()) {
                    if (!empty($product['quantity_and_stock_status']['qty'])) {
                        $stockItemQty = $product['quantity_and_stock_status']['qty'];
                    } else {
                        $stockItemQty = $product->getQty();
                    }
                    if ($stockItemQty <= $this->_marketplaceHelper->getlowStockQty()) {
                        $orderProductInfo = "<tbody><tr>
                                <td class='item-info'>".$productName."</td>
                                <td class='item-info'>".$sku."</td>
                                <td class='item-qty'>".($stockItemQty * 1).'</td>
                             </tr></tbody>';

                        $emailTemplateVariables = [];
                        $emailTemplateVariables['myvar1'] = $orderProductInfo;
                        $emailTemplateVariables['myvar2'] = $username;

                        $this->_objectManager->get(
                            'Webkul\Marketplace\Helper\Email'
                        )->sendLowStockNotificationMail(
                            $emailTemplateVariables,
                            $senderInfo,
                            $receiverInfo,
                            $sellerStoreId
                        );
                    }
                }
            }
            $shippingCharges = $info->getShippingCharges();
            $totalCod = 0;

            if ($paymentCode == 'mpcashondelivery') {
                $totalCod = $info->getCodCharges();
                $codRow = "<tr class='subtotal'>
                            <th colspan='3'>".__('Cash On Delivery Charges')."</th>
                            <td colspan='3'><span>".
                                $order->formatPrice($totalCod).
                            '</span></td>
                            </tr>';
            } else {
                $codRow = '';
            }

            $orderinfo = $orderinfo."<tfoot class='order-totals'>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Shipping & Handling Charges')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($shippingCharges)."</span></td>
                                </tr>
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Tax Amount')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice($totalTaxAmount).'</span></td>
                                </tr>'.$codRow."
                                <tr class='subtotal'>
                                    <th colspan='3'>".__('Grandtotal')."</th>
                                    <td colspan='3'><span>".
                                    $order->formatPrice(
                                        $totalprice +
                                        $totalTaxAmount +
                                        $shippingCharges +
                                        $totalCod
                                    ).'</span></td>
                                </tr></tfoot>';

            $emailTemplateVariables = [];
            if ($shippingInfo != '') {
                $isNotVirtual = 1;
            } else {
                $isNotVirtual = 0;
            }

            //vendor product delivery days- Part 2 - Start
            $emailTempVariables['vendor_shop_title'] = '';
            $emailTempVariables['delivery_days'] = 0;
            $emailTempVariables['warehouse'] = 0;
            if (is_array($vendorProductDelDays)) {
                $emailTempVariables['warehouse'] = 1;
                $individualVendoDeliveryDetails = $vendorProductDelDays[$info['seller_id']];
                $emailTempVariables['vendor_shop_title'] = $individualVendoDeliveryDetails['shop_title'];
                $emailTempVariables['delivery_days'] = $individualVendoDeliveryDetails['final_days'];
                // foreach ($individualVendoDeliveryDetails as $key => $value) {
                // }
            }
            //vendor product delivery days- Part 2 - End
            
            $emailTempVariables['myvar1'] = $order->getRealOrderId();
            $emailTempVariables['myvar2'] = $order['created_at'];
            $emailTempVariables['myvar4'] = $billinginfo;
            $emailTempVariables['myvar5'] = $payment;
            $emailTempVariables['myvar6'] = $shippingInfo;
            $emailTempVariables['isNotVirtual'] = $isNotVirtual;
            $emailTempVariables['myvar9'] = $shippingDes;
            $emailTempVariables['myvar8'] = $orderinfo;
            $emailTempVariables['myvar3'] = $username;

            if ($this->_marketplaceHelper->getOrderApprovalRequired()) {
                $emailTempVariables['seller_id'] = $info['seller_id'];
                $emailTempVariables['order_id'] = $lastOrderId;
                $emailTempVariables['sender_name'] = $senderInfo['name'];
                $emailTempVariables['sender_email'] = $senderInfo['email'];
                $emailTempVariables['receiver_name'] = $receiverInfo['name'];
                $emailTempVariables['receiver_email'] = $receiverInfo['email'];

                $orderPendingMailsCollection = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\OrderPendingMails'
                );
                $orderPendingMailsCollection->setData($emailTempVariables);
                $orderPendingMailsCollection->setCreatedAt($this->_date->gmtDate());
                $orderPendingMailsCollection->setUpdatedAt($this->_date->gmtDate());
                $orderPendingMailsCollection->save();
                $order->setOrderApprovalStatus(0)->save();
            } else {
                // if ($order->getStatus() != 'compliance_check' && ($paymentCode !='coingate_merchant')) {
                if ($order->getStatus() != 'compliance_check') {
                    $this->_objectManager->get(
                        'Webkul\Marketplace\Helper\Email'
                    )->sendPlacedOrderEmail(
                        $emailTempVariables,
                        $senderInfo,
                        $receiverInfo,
                        $sellerStoreId
                    );
                }
            }
        }

        $this->_logger->info(print_r($order->debug(), true));
    }

    /**
     * Get Order Product Option Data Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $result
     *
     * @return array
     */
    public function getProductOptionData($item, $result = [])
    {
        $productOptionsData = $this->_objectManager->get(
            'Webkul\Marketplace\Helper\Orders'
        )->getProductOptions(
            $item->getProductOptions()
        );
        if ($options = $productOptionsData) {
            if (isset($options['options'])) {
                $result = array_merge($result, $options['options']);
            }
            if (isset($options['additional_options'])) {
                $result = array_merge($result, $options['additional_options']);
            }
            if (isset($options['attributes_info'])) {
                $result = array_merge($result, $options['attributes_info']);
            }
        }

        return $result;
    }

    /**
     * Get Order Product Name Html Data Method.
     *
     * @param array  $result
     * @param string $productName
     *
     * @return string
     */
    public function getProductNameHtml($result, $productName)
    {
        if ($_options = $result) {
            $proOptionData = '<dl class="item-options">';
            foreach ($_options as $_option) {
                $proOptionData .= '<dt>'.$_option['label'].'</dt>';

                $proOptionData .= '<dd>'.$_option['value'];
                $proOptionData .= '</dd>';
            }
            $proOptionData .= '</dl>';
            $productName = $productName.'<br/>'.$proOptionData;
        } else {
            $productName = $productName.'<br/>';
        }

        return $productName;
    }

    /**
     * Seller Product Sales Calculation Method.
     *
     * @param \Magento\Sales\Model\Order $order
     */
    public function productSalesCalculation($order)
    {
        /*
        * Marketplace Order details save before Observer
        */
        $this->_eventManager->dispatch(
            'mp_order_save_before',
            ['order' => $order]
        );

        /*
        * Get Global Commission Rate for Admin
        */
        $percent = $this->_marketplaceHelper->getConfigCommissionRate();

        /*
        * Get Current Store Currency Rate
        */
        $currentCurrencyCode = $this->_marketplaceHelper->getCurrentCurrencyCode();
        $baseCurrencyCode = $this->_marketplaceHelper->getBaseCurrencyCode();
        $allowedCurrencies = $this->_marketplaceHelper->getConfigAllowCurrencies();
        $rates = $this->_marketplaceHelper->getCurrencyRates(
            $baseCurrencyCode,
            array_values($allowedCurrencies)
        );
        if (empty($rates[$currentCurrencyCode])) {
            $rates[$currentCurrencyCode] = 1;
        }

        $lastOrderId = $order->getId();

        /*
        * Marketplace Credit Management module Observer
        */
        $this->_eventManager->dispatch(
            'mp_discount_manager',
            ['order' => $order]
        );

        $this->_eventManager->dispatch(
            'mp_advance_commission_rule',
            ['order' => $order]
        );

        $sellerData = $this->getSellerProductData($order, $rates[$currentCurrencyCode]);

        $sellerProArr = $sellerData['seller_pro_arr'];
        $sellerTaxArr = $sellerData['seller_tax_arr'];
        $sellerCouponArr = $sellerData['seller_coupon_arr'];

        $taxToSeller = $this->_marketplaceHelper->getConfigTaxManage();
        $shippingAll = $this->_coreSession->getData('shippinginfo');
        if (is_array($shippingAll)) {
            $shippingAllCount = count($shippingAll);
        }
        foreach ($sellerProArr as $key => $value) {
            $productIds = implode(',', $value);
            $data = [
                'order_id' => $lastOrderId,
                'product_ids' => $productIds,
                'seller_id' => $key,
                'total_tax' => $sellerTaxArr[$key],
                'tax_to_seller' => $taxToSeller,
            ];

            if (is_array($shippingAll)) {
                # code...
                if (!$shippingAllCount && $key == 0) {
                    $shippingCharges = $order->getShippingAmount();
                    $data = [
                        'order_id' => $lastOrderId,
                        'product_ids' => $productIds,
                        'seller_id' => $key,
                        'shipping_charges' => $shippingCharges,
                        'total_tax' => $sellerTaxArr[$key],
                        'tax_to_seller' => $taxToSeller,
                    ];
                }
            }

            if (!empty($sellerCouponArr) && !empty($sellerCouponArr[$key])) {
                $data['coupon_amount'] = $sellerCouponArr[$key];
            }
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Orders'
            );
            $collection->setData($data);
            $collection->setCreatedAt($this->_date->gmtDate());
            $collection->setSellerPendingNotification(1);
            $collection->setUpdatedAt($this->_date->gmtDate());
            $collection->save();
            $sellerOrderId = $collection->getId();
            $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Notification'
            )->saveNotification(
                \Webkul\Marketplace\Model\Notification::TYPE_ORDER,
                $sellerOrderId,
                $lastOrderId
            );
        }
        /*
        * Marketplace Order details save after Observer
        */
        $this->_eventManager->dispatch(
            'mp_order_save_after',
            ['order' => $order]
        );
    }

    /**
     * Get Seller's Product Data.
     *
     * @param \Magento\Sales\Model\Order $order
     * @param int                        $ratesPerCurrency
     *
     * @return array
     */
    public function getSellerProductData($order, $ratesPerCurrency)
    {
        $lastOrderId = $order->getId();
        /*
        * Get Global Commission Rate for Admin
        */
        $percent = $this->_marketplaceHelper->getConfigCommissionRate();

        $sellerProArr = [];
        $sellerTaxArr = [];
        $sellerCouponArr = [];
        $isShippingFlag = [];
        /*
        * Marketplace Credit discount data
        */
        $discountDetails = [];
        $discountDetails = $this->_coreSession->getData('salelistdata');
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $checkoutSession = $objectManager->create('\Magento\Checkout\Model\Session');

        $getVendorHighestShippingPerProduct = $checkoutSession->getVendorHighestShippingPerProduct();
        foreach ($order->getAllItems() as $item) {
            $itemData = $item->getData();
            $sellerId = $this->getSellerIdPerProduct($item);
            if ($itemData['product_type'] != 'bundle') {
                $isShippingFlag = $this->getShippingFlag($item, $sellerId, $isShippingFlag);

                $price = $this->getProductPrice($item, $discountDetails, $ratesPerCurrency);

                $taxamount = $this->_marketplaceHelper->getOrderedPricebyorder($order, $itemData['tax_amount']);
                $qty = $item->getQtyOrdered();

                $totalamount = $qty * $price;
                $vTmgCost = 0;
                if (isset($getVendorHighestShippingPerProduct[$sellerId][$item->getProductId()])) {
                    $vTmgCost = $getVendorHighestShippingPerProduct[$sellerId][$item->getProductId()];    
                }
                //MIS 20July2018 START
                $misCommissionRate = $totalamount + $vTmgCost;
                $totalAmtWithCommission = $totalamount + $vTmgCost;
                //MIS 20July2018 END

                $advanceCommissionRule = $this->_customerSession->getData(
                    'advancecommissionrule'
                );

                if (!($item->getSku() == 'preorder_complete') ) {    
                    /*echo "<br> in if";    
                    die('<br> die of if');   */     
                    $commission = $this->getCommission($sellerId, $misCommissionRate, $item, $advanceCommissionRule);
                } else {
                    /*echo "<br> in if";
                    echo "<br> in else";*/
                    $commission = 0;
                }
                // $commission = $this->getCommission($sellerId, $totalamount, $item, $advanceCommissionRule);

                $actparterprocost = $totalAmtWithCommission - $commission;
                // $actparterprocost = $totalamount - $commission;
                //MIS 23rd July 2018
            } else {
                if (empty($isShippingFlag[$sellerId])) {
                    $isShippingFlag[$sellerId] = 0;
                }
                $price = 0;
                $taxamount = 0;
                $qty = $item->getQtyOrdered();
                $totalamount = 0;
                $commission = 0;
                $actparterprocost = 0;
            }

            $collectionsave = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleslist'
            );
            
 
            $collectionsave->setMageproductId($item->getProductId());
            $collectionsave->setOrderItemId($item->getItemId());
            $collectionsave->setParentItemId($item->getParentItemId());
            $collectionsave->setOrderId($lastOrderId);
            $collectionsave->setMagerealorderId($order->getIncrementId());
            $collectionsave->setMagequantity($qty);
            $collectionsave->setSellerId($sellerId);
            $collectionsave->setCpprostatus(\Webkul\Marketplace\Model\Saleslist::PAID_STATUS_PENDING);
            $collectionsave->setMagebuyerId($this->_customerSession->getCustomerId());
            $collectionsave->setMageproPrice($price);
            //vendor to myGermany cost for the product for which we calculated highest cost
            $collectionsave->setVendorToMygermanyCost($vTmgCost);
            $collectionsave->setMageproName($item->getName());
            if ($totalamount != 0) {
                $collectionsave->setTotalAmount($totalamount);
                $commissionRate = ($commission * 100) / $totalamount;
            } else {
                $collectionsave->setTotalAmount($price);
                $commissionRate = $percent;
            }
            $collectionsave->setTotalTax($taxamount);
            if (!$this->_marketplaceHelper->isSellerCouponModuleInstalled()) {
                if ((int) $itemData['base_discount_amount']) {
                    $baseDiscountAmount = $itemData['base_discount_amount'];
                    $collectionsave->setIsCoupon(1);
                    $collectionsave->setAppliedCouponAmount($baseDiscountAmount);

                    if (!isset($sellerCouponArr[$sellerId])) {
                        $sellerCouponArr[$sellerId] = 0;
                    }
                    $sellerCouponArr[$sellerId] = $sellerCouponArr[$sellerId] + $baseDiscountAmount;
                }
            }
            $collectionsave->setTotalCommission($commission);
            $collectionsave->setActualSellerAmount($actparterprocost);
            $collectionsave->setCommissionRate($commissionRate);
            if (isset($isShippingFlag[$sellerId])) {
                $collectionsave->setIsShipping($isShippingFlag[$sellerId]);
            }
            $collectionsave->setCreatedAt($this->_date->gmtDate());
            $collectionsave->setUpdatedAt($this->_date->gmtDate());
            $collectionsave->save();
            $qty = 0;
            if (!isset($sellerTaxArr[$sellerId])) {
                $sellerTaxArr[$sellerId] = 0;
            }
            $sellerTaxArr[$sellerId] = $sellerTaxArr[$sellerId] + $taxamount;
            if ($price != 0.0000) {
                if (!isset($sellerProArr[$sellerId])) {
                    $sellerProArr[$sellerId] = [];
                }
                array_push($sellerProArr[$sellerId], $item->getProductId());
            } else {
                if (!$item->getParentItemId()) {
                    if (!isset($sellerProArr[$sellerId])) {
                        $sellerProArr[$sellerId] = [];
                    }
                    array_push($sellerProArr[$sellerId], $item->getProductId());
                }
            }
        }

        return [
            'seller_pro_arr' => $sellerProArr,
            'seller_tax_arr' => $sellerTaxArr,
            'seller_coupon_arr' => $sellerCouponArr
        ];
    }

    /**
     * Get Order Product Price Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $discountDetails
     * @param int                             $ratesPerCurrency
     *
     * @return array
     */
    public function getProductPrice($item, $discountDetails, $ratesPerCurrency)
    {
        if ($discountDetails[$item->getProductId()]) {
            $price = $discountDetails[$item->getProductId()]['price']
            / $ratesPerCurrency;
        } else {
            $price = $item->getPrice() / $ratesPerCurrency;
        }

        return $price;
    }

    public function getProductAttributeId($item)
    {
        $attributeId = 0;
        $proId = $item->getProductId();
        $orderCollection = $this->_objectManager->create(
            'Magento\Catalog\Model\Product'
        )->load($proId);
        if (!($orderCollection->getSku() == 'preorder_complete') ) {
            if ($orderCollection->getProductCatType()) {
                $attributeId = $orderCollection->getProductCatType();
            }
        }
        return $attributeId;
    }
    
    public function getAttributeName($helperObject, $attrId)
    {
        $attributeName;
        $customAttributeArray = $helperObject->getCustomAttributeOption();
        $data = array_column($customAttributeArray, 'label', 'value');
        $attributeName = isset($data[$attrId]) ? $data[$attrId] : '';
        // foreach ($data as $key => $value) {
        //     if ($key == $attrId) {
        //         $attributeName = $value;
        //     }
        // }
        return $attributeName;
    }

    /**
     * Get Commission Amount.
     *
     * @param int                             $sellerId
     * @param float                           $totalamount
     * @param \Magento\Sales\Model\Order\Item $item
     * @param array                           $advanceCommissionRule
     *
     * @return float
     */
    public function getCommission($sellerId, $totalamount, $item, $advanceCommissionRule)
    {
        // die('SalesOrderPlaceAfterObserver');
        $commission = 0;
        /*
        * Get Global Commission Rate for Admin
        */
        if (($sellerId > 0) || ($item->getSku() != 'preorder_complete')) {
            if ($item->getProductType() != 'virtual') {
                $helperObject = $this->_objectManager->create(
                    'Mangoit\Vendorcommission\Helper\Data'
                );
                $helperCollection = $helperObject->getCommissionRules($sellerId);
                $helperData;
                $turnover;
                $percent;
                $vendorId;
                foreach ($helperCollection->getData() as $key => $value) {
                   $vendorId = $value['seller_id'];
                   $helperData = $value['commission_rule'];
                   $turnover = $value['seller_turnover']; 
                   // $turnover = $value['amount_remain'];
                }
                $flag = true;
                if(isset($vendorId)){ // if seller_id available in Saleperpartner table
                    // echo "<br>if seller_id available in Saleperpartner table";
                   if (isset($helperData)) { // Seller_id and commission rule is also available
                    // echo "<br>Seller_id and commission rule is also available";
                       $commissionRule = unserialize($helperData);

                   } else { // seller_id available but rule not avaialble so Globale rule will be usee
                    // echo "<br>  seller_id available but rule not avaialble so Globale rule will be usee";
                        $commissionRule = $this->_objectManager->create(
                                                    'Mangoit\Vendorcommission\Block\Adminhtml\Globalcommission'
                                                )->getSerializedData();
                   }
                }else{ // if both not available the global commisssion rule
                    // echo "<br> if both not available the global commisssion rule";
                    $flag = false;
                    $commissionRule = $this->_objectManager->create(
                                                    'Mangoit\Vendorcommission\Block\Adminhtml\Globalcommission'
                                                )->getSerializedData();
                    $turnover = 0;
                }
                $attrId = $this->getProductAttributeId($item);
                if ($attrId > 0) {
                    $attrName = $this->getAttributeName($helperObject, $attrId);
                    $rangeValue = [];
                    $percent = 0;
                    foreach ($commissionRule as $key => $data) {
                        if ($key == $attrName) {
                            // echo "1 if";
                            foreach ($data as $newkey => $newvalue) {
                                    // echo "<br> foreach $newvalue";
                                $rangeValue = explode("-", $newkey);
                                if ($rangeValue[1]!='<') {

                                    if(($rangeValue[0] <= $turnover) && ($turnover <= $rangeValue[1])){
                                        $percent = $newvalue;
                                    } else if ($rangeValue[1] <= $turnover ) {
                                         $percent = $newvalue;
                                    }
                                    // echo "<br> is not < here : $newvalue";
                                } else {
                                    if($rangeValue[0] <= $turnover){
                                        $percent = $newvalue;
                                    }
                                    // echo "<br> is < here: $percent";
                                }
                            }
                            break;
                        } else {
                            $percent = $this->notAvailableAttributePercentage($attrName, $turnover);
                        }
                    }
                    $commission = ($totalamount * $percent) / 100;
                }
            }
        } else { 
          $commission = 0;   
        }
        
        return $commission;
        
    }

    public function notAvailableAttributePercentage($attrName, $turnover)
    {
        $commissionRule = $this->_objectManager->create(
                                                'Mangoit\Vendorcommission\Block\Adminhtml\Globalcommission'
                                            )->getSerializedData();
        $percent = 0;
        foreach ($commissionRule as $key => $data) {
            if ($key == $attrName) {
                foreach ($data as $newkey => $newvalue) {
                    $rangeValue = explode("-", $newkey);
                    if ($rangeValue[1]!='<') {

                        if(($rangeValue[0] <= $turnover) && ($turnover <= $rangeValue[1])){
                            $percent = $newvalue;
                        }
                            // echo "<br> is not < here : $newvalue";
                    } else {
                        if($rangeValue[0] <= $turnover){
                             $newvalue;
                        }
                    // echo "<br> is < here: $percent";
                    }
                }
            }
        }

        return $percent;

    }

    /**
     * Get Seller ID Per Product.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     *
     * @return int
     */
    public function getSellerIdPerProduct($item)
    {
        $infoBuyRequest = $item->getProductOptionByCode('info_buyRequest');

        $mpassignproductId = 0;
        if (isset($infoBuyRequest['mpassignproduct_id'])) {
            $mpassignproductId = $infoBuyRequest['mpassignproduct_id'];
        }
        if ($mpassignproductId) {
            $mpassignModel = $this->_objectManager->create(
                'Webkul\MpAssignProduct\Model\Items'
            )->load($mpassignproductId);
            $sellerId = $mpassignModel->getSellerId();
        } elseif (array_key_exists('seller_id', $infoBuyRequest)) {
            $sellerId = $infoBuyRequest['seller_id'];
        } else {
            $sellerId = '';
        }
        if ($sellerId == '') {
            $collectionProduct = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $item->getProductId()
            );
            foreach ($collectionProduct as $value) {
                $sellerId = $value->getSellerId();
            }
        }
        if ($sellerId == '') {
            $sellerId = 0;
        }

        return $sellerId;
    }

    /**
     * Get Shipping Flag Per Seller Method.
     *
     * @param \Magento\Sales\Model\Order\Item $item
     * @param int                             $sellerId
     * @param array                           $result
     *
     * @return array
     */
    public function getShippingFlag($item, $sellerId, $isShippingFlag = [])
    {
        if (($item->getProductType() != 'virtual') && ($item->getProductType() != 'downloadable')) {
            if (!isset($isShippingFlag[$sellerId])) {
                $isShippingFlag[$sellerId] = 1;
            } else {
                $isShippingFlag[$sellerId] = 0;
            }
        }

        return $isShippingFlag;
    }

    public function getLogger()
    {
        $writer = new \Zend\Log\Writer\Stream(BP . '/var/log/SalesOrderPlaceAfterObserver.log');
        $logger = new \Zend\Log\Logger();
        $logger->addWriter($writer);
        return $logger;
    }
}

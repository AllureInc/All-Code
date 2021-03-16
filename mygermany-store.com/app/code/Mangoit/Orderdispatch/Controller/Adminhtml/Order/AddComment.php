<?php
/**
 *
 * Copyright © Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Orderdispatch\Controller\Adminhtml\Order;

use Magento\Backend\App\Action;
use Magento\Sales\Model\Order\Email\Sender\OrderCommentSender;

class AddComment extends \Magento\Sales\Controller\Adminhtml\Order\AddComment
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Magento_Sales::comment';

    /**
     * Add order comment action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $order = $this->_initOrder();
        if ($order) {
            $oldStatus = $order->getStatus();
            try {
                $data = $this->getRequest()->getPost('history');
                if (empty($data['comment']) && $data['status'] == $order->getDataByKey('status')) {
                    throw new \Magento\Framework\Exception\LocalizedException(__('Please enter a comment.'));
                }

                $notify = isset($data['is_customer_notified']) ? $data['is_customer_notified'] : false;
                $visible = isset($data['is_visible_on_front']) ? $data['is_visible_on_front'] : false;

                $history = $order->addStatusHistoryComment($data['comment'], $data['status']);
                $history->setIsVisibleOnFront($visible);
                $history->setIsCustomerNotified($notify);
                $history->save();

                $comment = trim(strip_tags($data['comment']));
                
                $order->save();
                /** @var OrderCommentSender $orderCommentSender */
                $orderCommentSender = $this->_objectManager
                    ->create(\Magento\Sales\Model\Order\Email\Sender\OrderCommentSender::class);

                
                $orderCommentSender->send($order, $notify, $comment);
                $order->setState($data['status']);
                $order->setStatus($data['status']);
                $order->save();
                if ($data['status'] == 'received') {

                    $webkulOrders = $this->_objectManager->create('\Webkul\Marketplace\Model\Orders')->getCollection();
                    $webkulOrders->addFieldToFilter('order_id',$order->getId())->getSelect()->group('seller_id');

                    $scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');


                    $salesName = $scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $salesEmail = $scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                    $orderEmailDetials = [];
                    foreach ($webkulOrders as $ordersValue) {

                        $sender = [
                            'name' => $salesName,
                            'email' => $salesEmail,
                        ];
                        $customerObj = $this->_objectManager->create('\Magento\Customer\Model\Customer');
                        $vendorObj = $customerObj->load($ordersValue->getSellerId());
                        $vendorEmail = $vendorObj->getEmail();
                        $vendorName = $vendorObj->getFirstname();//echo "<pre>";
                        $transportBuilder = $this->_objectManager->create('\Magento\Framework\Mail\Template\TransportBuilder');
                        $transportBuilder->clearFrom();
                        $transportBuilder->clearSubject();
                        $transportBuilder->clearMessageId();
                        $transportBuilder->clearBody();
                        $transportBuilder->clearRecipients();
                        $inlineTranslation = $this->_objectManager->create('Magento\Framework\Translate\Inline\StateInterface');
                        $postObject = new \Magento\Framework\DataObject();
                        $postObject->setData(['name'=> $vendorName,'orderid' => $order->getIncrementId()]);
                        $inlineTranslation->suspend();
                        $transportBuilder->setTemplateIdentifier('marketplace_email_order_received')
                        ->setTemplateOptions(
                          [
                            'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                          ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($sender)
                        ->addTo($vendorEmail); 
                        $transportBuilder->getTransport()->sendMessage();
                        $inlineTranslation->resume();
                    }
                }
                if ($data['status'] == 'pending' && ($oldStatus == 'compliance_check')) {
                        //Sending Email notification to vendor after compliance check
                        $paymentCode = '';
                        if ($order->getPayment()) {
                            $paymentCode = $order->getPayment()->getMethod();
                        }

                        $order->setOrderApprovalStatus(1)->save();

                        $payment = $order->getPayment()->getMethodInstance()->getTitle();

                        $helper = $this->_objectManager->create('\Webkul\Marketplace\Helper\Data');
                        $_customerRepository = $this->_objectManager->create('\Magento\Customer\Api\CustomerRepositoryInterface');
                        $emailHelper = $this->_objectManager->create('\Mangoit\TranslationSystem\Helper\Email');
                        $emailTemplate = $emailHelper->getTemplateId('marketplace/email/order_notification_to_sender');

                        $adminStoremail = $helper->getAdminEmailId();
                        $defaultTransEmailId = $helper->getDefaultTransEmailId();

                        $adminEmail = $adminStoremail ? $adminStoremail : $defaultTransEmailId;
                        $adminUsername = $this->_objectManager->get(
                            'Mangoit\Marketplace\Helper\Corehelper'
                        )->adminEmailName();
                        
                        /*$adminUsername = 'Admin';*/

                        $sellerOrder = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Orders'
                        )
                        ->getCollection()
                        ->addFieldToFilter('order_id', $order->getId())
                        ->addFieldToFilter('seller_id', ['neq' => 0]);
                        //vendor product delivery days- Part 1 - start
                        $vendorProductDelDays = '';
                        if ($order->getShippingMethod() == 'warehouse_warehouse') {
                            $vendorProductDelDays = unserialize($order->getVendorDeliveryDays());
                        }
                        //vendor product delivery days- Part 1 - End
                        foreach ($sellerOrder as $info) {
                            $userdata = $_customerRepository->getById($info['seller_id']);
                            $username = $userdata->getFirstname();
                            $useremail = $userdata->getEmail();

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
                            ->addFieldToFilter('order_id', $order->getId())
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
                                $product = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($res['mageproduct_id']);
                                // $product = $this->_productRepository->getById($res['mageproduct_id']);

                                /* product name */
                                $productName = $res->getMageproName();
                                $result = [];
                                $result = $this->getProductOptionData($res, $result);
                                $productName = $this->getProductNameHtml($result, $productName);
                                /* end */

                                $sku = $product->getSku();
                                $orderinfo = $orderinfo."<tbody><tr>
                                                <td style='text-align:left;' class='item-info'>".$productName."</td>
                                                <td style='text-align:left;' class='item-info'>".$sku."</td>
                                                <td style='text-align:left;' class='item-qty'>".($res['magequantity'] * 1)."</td>
                                                <td style='text-align:left;' class='item-price'>".
                                                    $order->formatPrice(
                                                        $res['magepro_price'] * $res['magequantity']
                                                    ).
                                                '</td>
                                             </tr></tbody>';
                                $totalTaxAmount = $totalTaxAmount + $res['total_tax'];
                                $totalprice = $totalprice + ($res['magepro_price'] * $res['magequantity']);
                            }
                            
                            $shippingCharges = $order->getShippingAmount();
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
                                                    <th style='text-align:right;' colspan='3'>".__('Shipping & Handling Charges')."</th>
                                                    <td style='text-align:left;' colspan='3'><span>".
                                                    $order->formatPrice($shippingCharges)."</span></td>
                                                </tr>
                                                <tr class='subtotal'>
                                                    <th style='text-align:right;' colspan='3'>".__('Tax Amount')."</th>
                                                    <td style='text-align:left;' colspan='3'><span>".
                                                    $order->formatPrice($totalTaxAmount).'</span></td>
                                                </tr>'.$codRow."
                                                <tr class='subtotal'>
                                                    <th style='text-align:right;' colspan='3'>".__('Grandtotal')."</th>
                                                    <td style='text-align:left;' colspan='3'><span>".
                                                    $order->formatPrice(
                                                        $totalprice +
                                                        $totalTaxAmount +
                                                        $shippingCharges +
                                                        $totalCod
                                                    ).'</span></td>
                                                </tr></tfoot>';

                            $emailTemplateVariables = [];

                            $scopeConfig = $this->_objectManager->create('\Magento\Framework\App\Config\ScopeConfigInterface');
                            $salesName = $scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
                            $salesEmail = $scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

                            $sender = [
                                'name' => $salesName,
                                'email' => $salesEmail,
                            ];

                            //vendor product delivery days- Part 2 - Start
                            $emailTempVariables['vendor_shop_title'] = '';
                            $emailTempVariables['delivery_days'] = 0;
                            $emailTempVariables['warehouse'] = 0;
                            if (is_array($vendorProductDelDays)) {
                                $emailTempVariables['warehouse'] = 1;
                                $individualVendoDeliveryDetails = $vendorProductDelDays[$info['seller_id']];
                                foreach ($individualVendoDeliveryDetails as $key => $value) {
                                    $emailTempVariables['vendor_shop_title'] = $key;
                                    $emailTempVariables['delivery_days'] = $value;
                                }
                            }
                            //vendor product delivery days- Part 2 - End

                            $emailTempVariables['myvar1'] = $order->getRealOrderId();
                            $emailTempVariables['myvar2'] = $order['created_at'];
                            $emailTempVariables['myvar5'] = $payment;
                            // $emailTempVariables['myvar6'] = $shippingInfo;
                            // $emailTempVariables['isNotVirtual'] = $isNotVirtual;
                            $emailTempVariables['myvar8'] = $orderinfo;
                            $emailTempVariables['myvar3'] = $username;
                            $emailTempVariables['myvar9'] = $order->getShippingDescription();

                            $transportBuilder = $this->_objectManager->create('\Magento\Framework\Mail\Template\TransportBuilder');
                            // $transportBuilder->setTemplateIdentifier('marketplace_email_order_notification_to_sender')
                            $transportBuilder->setTemplateIdentifier($emailTemplate)
                                ->setTemplateOptions(
                              [
                                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                              ]
                            )
                            ->setTemplateVars($emailTempVariables)
                            ->setFrom($sender)
                            ->addTo($receiverInfo['email'], $receiverInfo['name']); // ->addTo($toEmail);
                            $transportBuilder->getTransport()->sendMessage();

                        }
                }
                return $this->resultPageFactory->create();
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $response = ['error' => true, 'message' => $e->getMessage()];
            } catch (\Exception $e) {
                $response = ['error' => true, 'message' => __('We cannot add order history.')];
            }
            if (is_array($response)) {
                $resultJson = $this->resultJsonFactory->create();
                $resultJson->setData($response);
                return $resultJson;
            }
        }
        return $this->resultRedirectFactory->create()->setPath('sales/*/');
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
}

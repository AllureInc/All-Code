<?php

namespace Mangoit\VendorPayments\Controller\Webkul\Order;

class Cancel extends \Webkul\Marketplace\Controller\Order\Cancel
{
    /**
     * Default customer account page.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isCustomer = $this->getRequest()->getParam('customer');
        if($isCustomer != 1) {
            parent::execute();
            return;
        }
        $orderId = $this->getRequest()->getParam('id');
        if ($isCustomer == 1) {
            $order = $this->_orderRepository->get($orderId);
            if ($order) {
                try {
                    $sellersColle = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleslist'
                    )
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        ['eq' => $orderId]
                    );

                    foreach ($sellersColle as $seller) {
                        $sellerId = $seller->getSellerId();
                        $flag = $this->_objectManager->create(
                            'Webkul\Marketplace\Helper\Orders'
                        )->cancelorder($order, $sellerId);
                        $order->setStatus('canceled_by_customer')->save();
                        $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
                        $paymentCode = '';
                        $paymentMethod = '';
                        if ($order->getPayment()) {
                            $paymentCode = $order->getPayment()->getMethod();
                        }
                        $collection = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Saleslist'
                        )
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            ['eq' => $orderId]
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            ['eq' => $sellerId]
                        );
                        foreach ($collection as $saleproduct) {
                            $saleproduct->setCpprostatus(
                                $paidCanceledStatus
                            );
                            $saleproduct->setPaidStatus(
                                $paidCanceledStatus
                            );
                            if ($paymentCode == 'mpcashondelivery') {
                                $saleproduct->setCollectCodStatus(
                                    $paidCanceledStatus
                                );
                                $saleproduct->setAdminPayStatus(
                                    $paidCanceledStatus
                                );
                            }
                            $saleproduct->save();
                        }
                        $trackingcoll = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Orders'
                        )
                        ->getCollection()
                        ->addFieldToFilter(
                            'order_id',
                            $orderId
                        )
                        ->addFieldToFilter(
                            'seller_id',
                            $sellerId
                        );
                        foreach ($trackingcoll as $tracking) {
                            $tracking->setTrackingNumber('canceled');
                            $tracking->setCarrierName('canceled');
                            $tracking->setIsCanceled(1);
                            $tracking->save();
                        }
                    }
                    $this->messageManager->addSuccess(
                        __('The order has been cancelled.')
                    );
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addError(
                        __('We can\'t send the email order right now.')
                    );
                }
                if($isCustomer == 1) {
                    return $this->resultRedirectFactory->create()->setPath(
                        'sales/order/view',
                        [
                            'order_id' => $order->getEntityId(),
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                }
            } else {
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/history',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        }
    }
}

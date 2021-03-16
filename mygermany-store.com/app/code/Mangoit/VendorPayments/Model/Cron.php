<?php

namespace Mangoit\VendorPayments\Model;

use Magento\Framework\App\Action\Context;

/**
 * custom cron actions
 */
class Cron
{
    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $date;

    /**
     * @var \Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mangoit\VendorPayments\Helper\Data
     */
    protected $logger;

    protected $objectmanager;

    protected $marketplaceHelper;

    protected $_transportBuilder;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $mageOrderModel;

    /**
     *
     * @param Context $context
     *
     */
    public function __construct(
        Context $context,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Sales\Model\Order $mageOrderModel
    ) {
        $this->invoiceModel = $invoiceModel;
        $this->date = $date;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->objectmanager = $objectmanager;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->_objectManager = $objectManager;
        $this->mageOrderModel = $mageOrderModel;
    }

    /**
     * cron exection code
     *
     * @return void
     */
    public function notifyCanceledOrdersToAdmin()
    {
        $alreadySent = [];
        $orderCancelModel = $this->_objectManager->create('Mangoit\VendorPayments\Model\Ordercancelemail');
        $orderCancelCollection = $orderCancelModel->getCollection()->addFieldToFilter('email_sent_date', array('eq'=> date('d-m-y')))->addFieldToSelect('order_id')->getData();
        
        if (count($orderCancelCollection) > 0) {
            foreach ($orderCancelCollection as $key => $value) {
                $alreadySent[] = $value['order_id'];
            }
            # code...
        }

        $this->logger->info('++++++++++++++ notifyCanceledOrdersToAdmin() started. ++++++++++++++');
        try {
            $notifyDays = $this->helper->getConfigValue(
                    'marketplace/cancel_order_settings/cancel_rq_notify_days',
                    $this->helper->getStore()->getStoreId()
                );
            // $this->logger->info(' notifyDays ==> '.$notifyDays);
            $afterDate = $this->date->gmtDate('Y-m-d H:i:s', strtotime("-$notifyDays days"));
            // $fromDate = date('Y-m-d H:i:s', strtotime('01-01-2010'));
            // $this->logger->info(' afterDate ==> '.$afterDate);
            $collection = $this->invoiceModel->getCollection()
                ->addFieldToFilter('invoice_typ', 1)
                ->addFieldToFilter('cancellation_req_status', 5)
                ->addFieldToFilter('created_at', ['lteq' => $afterDate])
                ->addFieldToFilter('cancellation_pay_method', 'bank_trans');
            // $this->logger->info(' collection ==> '.(string)$collection->getSelect());
            $this->logger->info(' count ==> '.$collection->count());
            foreach ($collection as $account) {
                $this->sendInvoiceTo_myGermany($account, $alreadySent);
            }
        } catch (\Exception $e) {
            $this->logger->info('Model Cron notifyCanceledOrdersToAdmin : '.$e->getMessage());
        }
        $this->logger->info('====================== cron exection finished================= ');
    }


    public function sendInvoiceTo_myGermany($cancelInvoice, $alreadySent)
    {
        try {
            //Email Address of Store Owner.
            $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );

            $emailTemplate = $this->helper->getTemplateId(
                    'marketplace/cancel_order_settings/cancel_order_admin_notify'
                );
            $sellerId = $cancelInvoice->getSellerId();

            $sellerData = $this->marketplaceHelper->getSellerDataBySellerId($sellerId);
            $sellerData = $sellerData->getData()[0];
            $sellerMageObj = $this->marketplaceHelper->getCustomer();

            $salesListMod = $this->objectmanager->create(
                    'Webkul\Marketplace\Model\Saleslist'
                )
                ->getCollection()
                ->addFieldToFilter(
                    'order_id',
                    ['eq' => $cancelInvoice->getCanceledOrderId()]
                )->addFieldToFilter(
                    'seller_id',
                    ['eq' => $sellerId]
                )->getFirstItem();

            $mageOrder = $this->mageOrderModel->load($salesListMod->getOrderId());

           
            if (!in_array($salesListMod->getMagerealorderId(), $alreadySent)) {
                //sending required data to email template.
                $postObjectData = [];
                $postObjectData['order_id'] = $salesListMod->getMagerealorderId();
                $postObjectData['sellername'] = $sellerData['name'];
                $postObjectData['seller_id'] = $sellerData['seller_id'];
                $postObjectData['charges'] = $this->helper->getFormatedPrice(
                        $this->getVendorOrderCancellationCharge($sellerId, $mageOrder)
                    );

                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($postObjectData);

                $sender = [
                   'name' => $sellerData['name'],
                   'email' => $sellerData['email'],
                ];

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($emailTemplate)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                        ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($generalEmail);

                $transport->getTransport()->sendMessage();
                $orderCancelModel = $this->_objectManager->create('Mangoit\VendorPayments\Model\Ordercancelemail');
                $orderCancelModel->setOrderId($postObjectData['order_id']);
                $orderCancelModel->setEmailSentDate(date('d-m-y'));
                $orderCancelModel->setIsMailSent(1);
                $orderCancelModel->save();
            } else {
                $this->logger->info('#### Mail for this order id'.$salesListMod->getMagerealorderId().' has been already sent.');
            }
            
        } catch (\Exception $e) {
            $this->logger->info(' getMessage ==> '.$e->getMessage());
        }
    }

    public function getVendorOrderCancellationCharge($sellerId, $order)
    {
        $fixedAmount = $this->helper->getConfigValue('marketplace/general_settings/cancel_order_chrg_fxd', $this->helper->getStore()->getStoreId());
        $percent = $this->helper->getConfigValue('marketplace/general_settings/cancel_order_chrg_in_p', $this->helper->getStore()->getStoreId());
        
        $collectionselect = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Saleperpartner'
            )->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $sellerId
            );
        if ($collectionselect->getSize() == 1) {
            foreach ($collectionselect as $verifyrow) {
                $autoid = $verifyrow->getEntityId();
            }

            $collectionupdate = $this->_objectManager->get(
                    'Webkul\Marketplace\Model\Saleperpartner'
                )->load($autoid);

            $cancelOrderChrgData = json_decode($collectionupdate->getCancelOrderChrgData(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $fixedAmount = ($cancelOrderChrgData['fixed'] != '') ? $cancelOrderChrgData['fixed'] : $fixedAmount;
                $percent = ($cancelOrderChrgData['percent'] != '') ? $cancelOrderChrgData['percent'] : $percent;
            }
        }
        $percentOfTotal = ($order->getBaseGrandTotal() * $percent) / 100;

        $percentOfTotal = ($order->getBaseSubtotalInclTax() * $percent) / 100;
        $totalCharge = $percentOfTotal + $fixedAmount;
        return $totalCharge;
    }
}

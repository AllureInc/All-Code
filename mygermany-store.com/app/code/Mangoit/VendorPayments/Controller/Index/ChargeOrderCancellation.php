<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_VendorPayments
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */
namespace Mangoit\VendorPayments\Controller\Index;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Sales\Api\OrderRepositoryInterface;

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class ChargeOrderCancellation extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    protected $_resultFactory;

    protected $_url;
    protected $_session;
    protected $marketplaceHelper;
    protected $attachmentContainer;
    protected $_transportBuilder;

    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $salesListModel;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    protected $_objectManager;
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        ResultFactory $resultFactory,
        OrderRepositoryInterface $orderRepository,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Webkul\Marketplace\Model\Saleslist $salesListModel,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Mangoit\VendorPayments\Helper\Data $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_resultFactory = $resultFactory;
        $this->_orderRepository = $orderRepository;
        $this->_session = $session;
        $this->_url = $url;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->attachmentContainer = $attachmentContainer;
        $this->_transportBuilder = $transportBuilder;
        $this->invoiceModel = $invoiceModel;
        $this->date = $date;
        $this->salesListModel = $salesListModel;
        $this->_objectManager = $objectmanager; 
        $this->helper = $helper;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Marketplace Landing page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $isPartner = $this->marketplaceHelper->isSeller();
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $data = $this->getRequest()->getParams();
        $returnData = [];

        if ($isPartner == 1) {

            $cancellationReqStatus = ($data['pay_method'] == 'paypal') ? 10 : 5; // 5 Bank pending, 7 bank verified.
            $order = $this->_orderRepository->get($data['order_id']);
            $payData = isset($data['pay_data']) ? serialize($data['pay_data']) : '';
            $salesListMod = $this->salesListModel->load($data['order_id'], 'order_id');

            $model = $this->invoiceModel;
            $model->setSellerId($sellerId);
            $model->setInvoiceNumber('CANCEL-'.$salesListMod->getMagerealorderId());
            $model->setInvoiceTyp(1);
            $model->setInvoiceStatus(11);// Not used anywhere.
            $model->setCanceledOrderId($data['order_id']);
            $model->setCancellationChargeTotal($data['charge_total']);
            $model->setCancellationPayMethod($data['pay_method']);
            $model->setCancellationTxnData($payData);
            $model->setCancellationReqStatus($cancellationReqStatus);
            $model->setCreatedAt($this->date->gmtDate());

            $model->save();

            if($data['pay_method'] == 'paypal') {

                $flag = $this->_objectManager->create(
                    'Webkul\Marketplace\Helper\Orders'
                )->cancelorder($order, $sellerId);

                if($flag) {
                    $paidCanceledStatus = \Webkul\Marketplace\Model\Saleslist::PAID_STATUS_CANCELED;
                    $paymentCode = '';
                    $paymentMethod = '';
                    if ($order->getPayment()) {
                        $paymentCode = $order->getPayment()->getMethod();
                    }
                    // $orderId = $this->getRequest()->getParam('id');
                    $collection = $this->_objectManager->create(
                        'Webkul\Marketplace\Model\Saleslist'
                    )
                    ->getCollection()
                    ->addFieldToFilter(
                        'order_id',
                        ['eq' => $data['order_id']]
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
                        $data['order_id']
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

                $attachmentContent = $this->helper->getCancelInvoicePdfHtmlContent($model);
                $attachmentName = $model->getInvoiceNumber().'.pdf';
                if(isset($attachmentContent)) {
                    $this->helper->attachPdf(
                        $attachmentContent,
                        $attachmentName,
                        $this->attachmentContainer
                    );
                }
                $order->setStatus('canceled_by_vendor')->save();

                $returnData['redirect_url'] = $this->_url->getUrl(
                        'marketplace/order/view',
                        ['id' => $data['order_id'], '_secure' => $this->getRequest()->isSecure()]
                    );
            } else {
                $order->setStatus('canceled_by_vendor')->save();
                $this->messageManager->addSuccess(__('Order cancellation requested successfully.'));
                $returnData['redirect_url'] = $this->_url->getUrl(
                        'marketplace/order/view',
                        ['id' => $data['order_id'], '_secure' => $this->getRequest()->isSecure()]
                    );

            }

        }
        $this->sendInvoiceTo_myGermany($salesListMod, $data['pay_method']);
        $this->sendInvoiceTo_Vendor($salesListMod, $data['pay_method']);
        $response = $this->_resultFactory->create(ResultFactory::TYPE_RAW);
        $response->setHeader('Content-type', 'text/plain');
        $response->setContents(
            json_encode($returnData)
        );
        return $response;
    }


    public function sendInvoiceTo_myGermany($salesListMod, $paymentTyp)
    {
        try {
            //Email Address of Store Owner.
            $marketPlaceHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\MarketplaceEmail');

            $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );
            $generalName = $this->helper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->helper->getStore()->getStoreId()
                );

            //Value of custom email template from store.
            if($paymentTyp == 'paypal') {

               /* $emailTemplate = $marketPlaceHelper->getTemplateId('
                    marketplace/general_settings/cancel_order_invoice_email_admin_paypal',
                    $this->helper->getStore()->getStoreId()
                );*/

                $emailTemplate = $this->helper->getTemplateId(
                        'marketplace/general_settings/cancel_order_invoice_email_admin_paypal'
                    );
            } else {
                /*$emailTemplate = $marketPlaceHelper->getTemplateId('
                    marketplace/general_settings/cancel_order_invoice_email_admin_bank',
                    $this->helper->getStore()->getStoreId()
                );*/
                $emailTemplate = $this->helper->getTemplateId(
                        'marketplace/general_settings/cancel_order_invoice_email_admin_bank'
                    );
            }


            $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
            $sellerMageObj = $this->marketplaceHelper->getCustomer();
            $sellerName = $sellerMageObj->getName();

            //sending required data to email template.
            $postObjectData = [];
            $postObjectData['order_id'] = $salesListMod->getMagerealorderId();
            $postObjectData['sellername'] = $sellerName;
            $postObjectData['sellerid'] = $sellerData->getSellerId();
            $postObjectData['url'] = $this->marketplaceHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$sellerData->getShopUrl());
            $postObjectData['phone'] = $sellerData->getContactNumber();

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($postObjectData);

            $sender = [
               'name' => $sellerName,
               'email' => $sellerMageObj->getEmail(),
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

            if ($this->attachmentContainer->hasAttachments()) {
                foreach ($this->attachmentContainer->getAttachments() as $attachment) {
                    $transport->addAttachment1($attachment);
                }
            }
            $transport->getTransport()->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            // return $this->resultRedirectFactory->create()->setPath(
            //     '*/*/invoice',
            //     ['_secure' => $this->getRequest()->isSecure()]
            // );
        }
    }

    public function sendInvoiceTo_Vendor($salesListMod, $paymentTyp)
    {
        try {
            $marketPlaceHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\MarketplaceEmail');

            //Email Address of Store Owner.
            $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );
            $generalName = $this->helper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->helper->getStore()->getStoreId()
                );

            //Value of custom email template from store.
            if($paymentTyp == 'paypal') {

                $emailTemplate = $marketPlaceHelper->getTemplateId('marketplace/general_settings/cancel_order_invoice_email_vendor_paypal',
                    $this->helper->getStore()->getStoreId()
                );


                /*$emailTemplate = $this->helper->getTemplateId(
                        'marketplace/general_settings/cancel_order_invoice_email_vendor_paypal'
                    );*/
            } else {
                $emailTemplate = $marketPlaceHelper->getTemplateId('marketplace/general_settings/cancel_order_invoice_email_vendor_bank',
                    $this->helper->getStore()->getStoreId()
                );
                
                /*$emailTemplate = $this->helper->getTemplateId(
                        'marketplace/general_settings/cancel_order_invoice_email_vendor_bank'
                    );*/
            }


            $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
            $sellerMageObj = $this->marketplaceHelper->getCustomer();
            $sellerName = $sellerMageObj->getName();
            //sending required data to email template.
            $postObjectData = [];
            $postObjectData['order_id'] = $salesListMod->getMagerealorderId();
            $postObjectData['sellername'] = $sellerName;
            $postObjectData['sellerid'] = $sellerData->getSellerId();
            $postObjectData['url'] = $this->marketplaceHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$sellerData->getShopUrl());
            $postObjectData['phone'] = $sellerData->getContactNumber();

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($postObjectData);

            $sender = [
               'name' => $generalName,
               'email' => $generalEmail,
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
                ->addTo($sellerMageObj->getEmail());

            if ($this->attachmentContainer->hasAttachments()) {
                foreach ($this->attachmentContainer->getAttachments() as $attachment) {
                    $transport->addAttachment1($attachment);
                }
                $this->attachmentContainer->resetAttachments();
            }
            $transport->getTransport()->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            // return $this->resultRedirectFactory->create()->setPath(
            //     '*/*/invoice',
            //     ['_secure' => $this->getRequest()->isSecure()]
            // );
        }
    }
}

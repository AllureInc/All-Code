<?php

namespace Mangoit\VendorPayments\Controller\Adminhtml\Cancelrequest;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Sales\Api\OrderRepositoryInterface;

class Approve extends Action
{
    protected $_resultPageFactory;
    protected $_resultPage;
    protected $_objectManager;
    protected $_session;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $_messageManager;
    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var OrderRepositoryInterface
     */
    protected $_orderRepository;

    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    protected $attachmentContainer;

    protected $marketplaceHelper;

    protected $_transportBuilder;

    public function __construct(
        Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        PageFactory $resultPageFactory,
        OrderRepositoryInterface $orderRepository,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer
    ) {
        $this->_messageManager = $managerInterface;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;  
        $this->_orderRepository = $orderRepository;  
        $this->helper = $helper;    
        $this->invoiceModel = $invoiceModel;    
        $this->marketplaceHelper = $marketplaceHelper;    
        $this->_transportBuilder = $transportBuilder;    
        $this->attachmentContainer = $attachmentContainer;    
        parent::__construct($context);
    }

    public function execute()
    {

        $id = $this->getRequest()->getParam('order_id');
        try {

            $order = $this->_orderRepository->get($id);

            $allOrders = $this->invoiceModel->getCollection()
                ->addFieldToFilter('canceled_order_id', $id)
                ->addFieldToFilter('cancellation_req_status', '5')->getFirstItem();

            $sellerId = $allOrders->getSellerId();

            $flag = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Orders'
            )->cancelorder($order, $sellerId);

            if ($flag) {
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
                    ['eq' => $id]
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
                    $id
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

                $allOrders->setCancellationReqStatus(7)->save();
                $order->setStatus('canceled_by_vendor')->save();

                $this->_messageManager->addSuccess(
                    __('The order has been cancelled.')
                );

                $attachmentContent = $this->helper->getCancelInvoicePdfHtmlContent($allOrders);
                $attachmentName = $allOrders->getInvoiceNumber().'.pdf';
                if(isset($attachmentContent)) {
                    $this->helper->attachPdf(
                        $attachmentContent,
                        $attachmentName,
                        $this->attachmentContainer
                    );
                }
                $this->sendInvoiceTo_Vendor($collection->getFirstItem(), $sellerId);
            }
            // else {
            //     $this->messageManager->addError(
            //         __('You are not permitted to cancel this order.')
            //     );

            //     return $this->_resultPageFactory->create()->setPath(
            //         '*/*/history',
            //         ['_secure' => $this->getRequest()->isSecure()]
            //     );
            // }

            $this->_messageManager->addSuccess(__("Approved Cancel Request."));    
        } catch (Exception $e) {
            $this->_messageManager->addError($e->getMessage());
            // return false;
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setUrl($this->_redirect->getRefererUrl());

        // $resultRedirect->setUrl('marketplace/order/cancel', ['id' => $this->getRequest()->getParam('order_id'), '_secure' => $this->getRequest()->isSecure()]);
        return $resultRedirect;
    }

    public function sendInvoiceTo_Vendor($salesListMod, $sellerId)
    {
        try {
            //Email Address of Store Owner.
            $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );
            $generalName = $this->helper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->helper->getStore()->getStoreId()
                );
            $marketPlaceHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\MarketplaceEmail');
            $customerRepo = $this->_objectManager->create('Magento\Customer\Model\Customer');
            
            $sellerData = $customerRepo->load($sellerId);

            $sellerStoreId = $sellerData->getCreatedIn();
            if ($sellerStoreId == 'Germany') {
                $sellerStoreId = 7;
            } else {
                $sellerStoreId = 1;
            }
            
            $emailTemplate = $marketPlaceHelper->getTemplateId('marketplace/general_settings/cancel_order_invoice_email_approval_vendor',
                $sellerStoreId
            );

            //Value of custom email template from store.
            /*$emailTemplate = $this->helper->getTemplateId(
                    'marketplace/general_settings/cancel_order_invoice_email_approval_vendor'
                );*/
            
            $sellerData = $this->marketplaceHelper->getSellerDataBySellerId($sellerId);
            $sellerData = $sellerData->getData()[0];

            //sending required data to email template.
            $postObjectData = [];
            $postObjectData['order_id'] = $salesListMod->getMagerealorderId();
            $postObjectData['sellername'] = $sellerData['name'];
            $postObjectData['sellerid'] = $sellerData['seller_id'];

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
                ->addTo($sellerData['email']);

            if ($this->attachmentContainer->hasAttachments()) {
                foreach ($this->attachmentContainer->getAttachments() as $attachment) {
                    $transport->addAttachment($attachment->getContent(), $attachment->getFilename(), $attachment->getMimeType());
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
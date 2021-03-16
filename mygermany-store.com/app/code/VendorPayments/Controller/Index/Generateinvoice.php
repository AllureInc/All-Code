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

/**
 * Webkul Marketplace Landing page Index Controller.
 */
class Generateinvoice extends Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;

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
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Mangoit\VendorPayments\Helper\Data $helper
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->_session = $session;
        $this->_url = $url;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->attachmentContainer = $attachmentContainer;
        $this->_transportBuilder = $transportBuilder;
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

        if(($this->getRequest()->getParam('makeDownload') == 1) && $this->_session->getSaleslistItemIds()){
            $getSaleslistItemIds = $this->_session->getSaleslistItemIds();
            $this->_session->unsSaleslistItemIds();
            return $this->helper->downloadPdfAction($getSaleslistItemIds, $sellerId);
        }

        if ($isPartner == 1) {

            $attachmentContent = $this->helper->getOrderPdfHtmlContent();

            if(isset($attachmentContent['invoiced_order_item_ids']) && empty($attachmentContent['invoiced_order_item_ids'])) {
                $this->messageManager->addError(__('There are no items to generate invoice for.'));
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/invoice',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }

            if(isset($attachmentContent['str'])) {
                $this->helper->attachPdf(
                    $attachmentContent['str'],
                    'credit_invoice.pdf',
                    $this->attachmentContainer
                );
            }

            $this->sendInvoiceTo_myGermany();
            $this->sendInvoiceTo_Vendor();

            $this->messageManager->addSuccess(__('Invoice has been generated and sent to myGermany.'));
            // print_r($attachmentContent['invoiced_order_ids']);
            // die;
            $this->_session->setSaleslistItemIds($attachmentContent['invoiced_order_item_ids']);

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/invoice',
                ['_secure' => $this->getRequest()->isSecure(), 'makeDownload' => 1]
            );
           
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }


    public function sendInvoiceTo_myGermany()
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

            //Value of custom email template from store.
            $emailTemplate = $this->helper->getTemplateId('marketplace/general_settings/vendor_invoice_email_template');


            $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
            $sellerMageObj = $this->marketplaceHelper->getCustomer();
            $sellerName = $sellerMageObj->getName();
            //sending required data to email template.
            $postObjectData = [];
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

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/invoice',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function sendInvoiceTo_Vendor()
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

            //Value of custom email template from store.
            $emailTemplate = $this->helper->getTemplateId('marketplace/general_settings/vendor_invoice_email_template_vendor_copy');


            $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
            $sellerMageObj = $this->marketplaceHelper->getCustomer();
            $sellerName = $sellerMageObj->getName();
            //sending required data to email template.
            $postObjectData = [];
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

            return $this->resultRedirectFactory->create()->setPath(
                '*/*/invoice',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}

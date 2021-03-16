<?php
/**
 * A Magento 2 module named Mangoit/TranslationSystem
 * Copyright (C) 2017 Mango IT Solutions
 * 
 * This file is part of Mangoit/TranslationSystem.
 * 
 * Mangoit/TranslationSystem is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the
 * GNU General Public License for more details.
 * 
 * You should have received a copy of the GNU General Public License
 * along with this program. If not, see <http://www.gnu.org/licenses/>.
 */

namespace Mangoit\TranslationSystem\Controller\Operation;

use Magento\Framework\App\RequestInterface;

class Requestquote extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $logger;
    protected $_stockItem;
    protected $_scopeConfig;
    protected $_transportBuilder;
    protected $_catalogProductHelper;
    protected $translationHelper;
    protected $emailHelper;
    protected $_url;
    protected $_session;

    /**
     * @var Api\AttachmentContainerInterface
     */
    protected $attachmentContainer;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Psr\Log\LoggerInterface $logger, 
        \Magento\CatalogInventory\Api\StockStateInterface $stockItem, 
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, 
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Mangoit\TranslationSystem\Helper\Data $translationHelper,
        \Mangoit\TranslationSystem\Helper\Email $emailHelper,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->logger = $logger;
        $this->_stockItem = $stockItem;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_catalogProductHelper = $catalogProductHelper;
        $this->translationHelper = $translationHelper;
        $this->emailHelper = $emailHelper;
        $this->_session = $session;
        $this->_url = $url;
        $this->attachmentContainer = $attachmentContainer;
        $this->marketplaceHelper = $marketplaceHelper;
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
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $mphelper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $isPartner = $mphelper->isSeller();

        if ($isPartner == 1) {
            $attachmentContent = $this->translationHelper->getAttachmentContent();

            if(isset($attachmentContent['pdf_content'])) {
                $this->emailHelper->attachPdf(
                    $attachmentContent['pdf_content'],
                    'translation_data.pdf',
                    $this->attachmentContainer
                );
            }
            if(isset($attachmentContent['csv_content'])) {
                $this->emailHelper->attachCsv(
                    $attachmentContent['csv_content'],
                    'translation_data.csv',
                    $this->attachmentContainer
                );
            }
            $sellerData = $this->marketplaceHelper->getSellerData()->getFirstItem();
            $sellerMageObj = $this->marketplaceHelper->getCustomer();

            $translateBlock = $this->_view->getLayout()->createBlock(
                    'Mangoit\TranslationSystem\Block\Index\Translate'
                );

            $partner = $mphelper->getSellerCollectionObj($sellerData->getSellerId())->getFirstItem()->getData();

            $contentArray = array();
            $contentArray['shop_title'] = $partner['shop_title'];
            $contentArray['company_locality'] = $partner['company_locality'];
            $contentArray['company_description'] = $partner['company_description'];
            $contentArray['meta_keyword'] = $partner['meta_keyword'];
            $contentArray['meta_description'] = $partner['meta_description'];
            $contentArray['return_policy'] = $partner['return_policy'];
            $contentArray['shipping_policy'] = $partner['shipping_policy'];

            $shopWordCont = str_word_count(strip_tags(implode(' ', $contentArray)));

            $productWordCont = $translateBlock->getProductsContent($sellerData->getSellerId());
            $vendorFaqWords = $translateBlock->getVendorFaqCount($sellerData->getSellerId());
            $preOrderMsgWords = $translateBlock->getVendorPreorderMsgCount();
            $pricePerWord = $translateBlock->getPricePerWord();
            $totalWords = ($shopWordCont + $productWordCont + $vendorFaqWords + $preOrderMsgWords);
            $totalPrice = ($totalWords * $pricePerWord);

            $sellerName = $sellerMageObj->getFirstname().' '.$sellerMageObj->getLastname();

            //sending required data to email template.
            $postObjectData = [];
            $postObjectData['sellername'] = $sellerName;
            $postObjectData['sellerid'] = $sellerData->getSellerId();
            $postObjectData['url'] = $this->marketplaceHelper->getRewriteUrl('marketplace/seller/profile/shop/'.$sellerData->getShopUrl());
            $postObjectData['phone'] = $sellerData->getContactNumber();
            $postObjectData['totalnumber'] = $totalWords;
            $postObjectData['shopwords'] = $shopWordCont;
            $postObjectData['productwords'] = $productWordCont;
            $postObjectData['faqword'] = $vendorFaqWords;
            $postObjectData['custom_msg_word'] = $preOrderMsgWords;
            $postObjectData['priceperword'] = $translateBlock->getFormatedCostPrice($pricePerWord);
            $postObjectData['price'] = $translateBlock->getFormatedCostPrice($totalPrice);

            $this->sendQuoteToAdmin($postObjectData, $sellerMageObj);

            $this->messageManager->addSuccess(__('A free quote request has been sent.'));

            return $this->resultRedirectFactory->create()->setPath(
                '*/index/translate',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function sendQuoteToAdmin($postObjectData, $sellerMageObj)
    {
        try {
            //Email Address of Store Owner.
            $generalEmail = $this->emailHelper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->emailHelper->getStore()->getStoreId()
                );
            $generalName = $this->emailHelper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->emailHelper->getStore()->getStoreId()
                );

            //Value of custom email template from store.
            /*$emailTemplate = $this->emailHelper->getTemplateId('translation/translationsettings/email_template');*/
            $emailTemplate = $this->emailHelper->getConfigValue('translation/translationsettings/email_template', $sellerMageObj->getData('store_id'));
           

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData($postObjectData);

            $sellerName = $sellerMageObj->getFirstname().' '.$sellerMageObj->getLastname();
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
                    /*echo "<pre>";
                    print_r(get_class_methods($attachment));
                    die();*/
                    $transport->addAttachment($attachment->getContent(), $attachment->getFilename(), $attachment->getMimeType());
                }
            }
            $transport->getTransport()->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                '*/index/translate',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }
}

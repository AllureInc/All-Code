<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Account;

use Webkul\MpAmazonConnector\Model\AccountsFactory;

class Save extends \Magento\Customer\Controller\AbstractAccount
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    protected $resultJsonFactory;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Data
     */
    protected $dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AccountsFactory $accountsFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAmazonConnector\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountsFactory = $accountsFactory;
        $this->dataHelper =  $helper;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $sellerPost = $this->getRequest()->getParams();
        if ($sellerPost) {
            
            $sellerId = $this->customerSession->getCustomerId();
            $sellerAccountDetails = $this->accountsFactory
                                            ->create()
                                            ->getCollection()
                                            ->addFieldToFilter('seller_id', $sellerId);

            $sellerPost = $this->getRawData($sellerPost,$sellerAccountDetails);

            $amzClient = $this->dataHelper->validateAmzCredentials($sellerPost);
            if ($amzClient) {
                $extraInfo = $this->dataHelper->getAmazonParticipation($sellerPost['marketplace_id']);
                $sellerPost = array_merge($sellerPost, $extraInfo);
                $sellerPost['default_website'] = $this->dataHelper->getWebsiteId();
                $sellerPost['default_store_view'] = $this->dataHelper->getCurrentStore();
                
                if ($sellerAccountDetails->getSize()) {
                    foreach ($sellerAccountDetails as $sellerAccount) {
                        $sellerAccount->addData($sellerPost);
                        $sellerAccount->save();
                    }
                } else {
                    $sellerAccount = $this->accountsFactory->create();
                    $sellerAccount->addData($sellerPost);
                    $sellerAccount->setSellerId($sellerId);
                    $sellerAccount->save();
                }
                
                $this->messageManager->addSuccess(
                    __('Amazon information saved successfuly.')
                );
            } else {
                $this->messageManager->addError(
                    __('Amazon Credentials are not valid.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Invalid request.')
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setUrl(
                $this->_url->getUrl('mpamazonconnect/account/index')
            );
    }

    /**
     * decrypt encrpted data
     *
     * @param array $data
     * @param array $collection
     * @return array
     */
    private function getRawData($data,$collection)
    {
        if ($collection->getSize()) {
            foreach ($collection as $sellerAccount) {
                if ($data['access_key_id'] === '*****') {
                    $data['access_key_id'] = $sellerAccount->getAccessKeyId();
                }
                if ($data['secret_key'] === '*****') {
                    $data['secret_key'] = $sellerAccount->getSecretKey();
                }
            }
        }
        return $data;
    }
}

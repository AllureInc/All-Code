<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Account;

use Mangoit\RakutenConnector\Model\AccountsFactory;

class Save extends \Magento\Customer\Controller\AbstractAccount
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    protected $resultJsonFactory;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Data
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
        \Mangoit\RakutenConnector\Helper\Data $helper
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
            $rktnClient = $this->dataHelper->validateRakutenAccount($sellerPost);
            if ($rktnClient) {
                // $extraInfo = $this->dataHelper->getAmazonParticipation($sellerPost['marketplace_id']);
                // $sellerPost = array_merge($sellerPost, $extraInfo);
                $sellerPost['default_website'] = $this->dataHelper->getWebsiteId();
                $sellerPost['default_store_view'] = $this->dataHelper->getCurrentStore();
                $sellerId = $this->customerSession->getCustomerId();
                $sellerAccountDetails = $this->accountsFactory
                                                ->create()
                                                ->getCollection()
                                                ->addFieldToFilter('seller_id', $sellerId);
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
                    __('Rakuten information saved successfuly.')
                );
            } else {
                $this->messageManager->addError(
                    __('Rakuten Credentials are not valid.')
                );
            }
        } else {
            $this->messageManager->addError(
                __('Invalid request.')
            );
        }
        $resultRedirect = $this->resultRedirectFactory->create();
            return $resultRedirect->setUrl(
                $this->_url->getUrl('rakutenconnect/account/index')
            );
    }
}

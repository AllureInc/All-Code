<?php

namespace Mangoit\Advertisement\Controller\Adminhtml\Adminads;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\App\Filesystem\DirectoryList;


class Enablevendorads extends Action
{
	protected $_resultPageFactory;
	protected $_resultPage;
    protected $_objectManager;
    protected $_session;
    protected $_mediaDirectory;
    protected $_fileUploaderFactory;
    protected $_messageManager;
    protected $_scopeConfig;
    protected $marketplaceHelper;
    protected $_transportBuilder;
    protected $_marketplaceEmail;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

	public function __construct(
		Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\Session\SessionManagerInterface $coreSession,
        \Magento\Framework\Message\ManagerInterface $managerInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Mangoit\Marketplace\Helper\MarketplaceEmail $marketplaceEmail,
        PageFactory $resultPageFactory
        )
	{
		parent::__construct($context);
        $this->_messageManager = $managerInterface;
        $this->_resultPageFactory = $resultPageFactory;
        $this->_objectManager = $objectmanager;
        $this->_session = $coreSession;
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_fileUploaderFactory = $fileUploaderFactory;
        $this->_scopeConfig = $scopeConfig;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->_date = $date;
        $this->_marketplaceEmail = $marketplaceEmail;
	}

    public function execute() 
    {
        $sellerStoreId = 0;
        $postData = $this->getRequest()->getParams();
        $purchasedAdModel = $this->_objectManager->create('Webkul\MpAdvertisementManager\Model\AdsPurchaseDetail');

        if (isset($postData['image'])) {
            if ($postData['image']['image_enable'] == 2) {
                $this->_messageManager->addError(__("Please select enable/Disable.")); 
               return $this->resultRedirectFactory->create()->setPath('*/adminads/view/id/'.$postData['ad_id'],
                ['_secure' => $this->getRequest()->isSecure()] );
            } else {
                $purchasedAdModel->load($postData['ad_id']);
                $purchasedAdModel->setEnable($postData['image']['image_enable']);
                $purchasedAdModel->setMisApprovalStatus($postData['approvalStatus']);
                $purchasedAdModel->setMisApprovalDate(($postData['approvalStatus'] == 1) ? $this->_date->gmtDate() : '');
                $purchasedAdModel->setMisApprovalDeclineMsg(isset($postData['declineMsg' ] ) ? $postData['declineMsg'] : '');
                $purchasedAdModel->save();
            }
        } else if (isset($postData['product'])) {
            if ($postData['product']['product_enable'] == 2) {
                $this->_messageManager->addError(__("Please select enable/Disable.")); 
                return $this->resultRedirectFactory->create()->setPath('*/adminads/view/id/'.$postData['ad_id'],
                ['_secure' => $this->getRequest()->isSecure()] );
            } else {
                $purchasedAdModel->load($postData['ad_id']);
                $purchasedAdModel->setEnable($postData['product']['product_enable']);
                $purchasedAdModel->setMisApprovalStatus($postData['approvalStatus']);
                $purchasedAdModel->setMisApprovalDate(($postData['approvalStatus'] == 1) ? $this->_date->gmtDate() : '');
                $purchasedAdModel->setMisApprovalDeclineMsg(isset($postData['declineMsg']) ? $postData['declineMsg'] : '');
                $purchasedAdModel->save();
            }
        } else if (isset($postData['html'])) {
            if ($postData['html']['html_enable'] == 2) {
                $this->_messageManager->addError(__("Please select enable/Disable.")); 
                return $this->resultRedirectFactory->create()->setPath('*/adminads/view/id/'.$postData['ad_id'],
                ['_secure' => $this->getRequest()->isSecure()] );
            } else {
                $purchasedAdModel->load($postData['ad_id']);
                $purchasedAdModel->setEnable($postData['html']['html_enable']);
                $purchasedAdModel->setMisApprovalStatus($postData['approvalStatus']);
                $purchasedAdModel->setMisApprovalDate(($postData['approvalStatus'] == 1) ? $this->_date->gmtDate() : '');
                $purchasedAdModel->setMisApprovalDeclineMsg(isset($postData['declineMsg' ]) ? $postData['declineMsg'] : '');
                $purchasedAdModel->save();
            }
        }
        if($purchasedAdModel->getSellerId()) {
            $sellerDataObj = $this->marketplaceHelper->getSellerDataBySellerId($purchasedAdModel->getSellerId());
            $vendorEmail = '';
            $vendorName = '';
            foreach ($sellerDataObj->getData() as $data) {
                $vendorEmail = $data['email'];
                $vendorName = $data['name'];
                $sellerStoreId = $data['store_id'];
            }

            if ($sellerStoreId == 0) {
                $sellerStoreId = 7;
            }

            if(isset($postData['approvalStatus'])) {
                $postObjectData = [];
                $postObjectData['sellername'] = $vendorName;

                $generalName = $this->_scopeConfig->getValue(
                        'trans_email/ident_general/name',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                $generalEmail = $this->_scopeConfig->getValue(
                        'trans_email/ident_general/email',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    );
                if ($postData['approvalStatus'] == 1) {
                    $emailTemplate = $this->_marketplaceEmail->getTemplateId('marketplace/ads_settings/approval_template',$sellerStoreId);
                    /*$emailTemplate = $this->_scopeConfig->getValue(
                            'marketplace/ads_settings/approval_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );*/
                } elseif ($postData['approvalStatus'] == 2) {
                    $postObjectData['msg'] = isset($postData['declineMsg']) ? $postData['declineMsg'] : '';

                    $emailTemplate = $this->_marketplaceEmail->getTemplateId('marketplace/ads_settings/decline_template',
                        $sellerStoreId);

                    $emailTemplate = $this->_scopeConfig->getValue(
                            'marketplace/ads_settings/decline_template',
                            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                        );
                }

                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($postObjectData);

                $sender = [
                   'name' => $generalName,
                   'email' => $generalEmail,
                ];
             

                if ($postData['approvalStatus'] == 1 || $postData['approvalStatus'] == 2) {

                    $transport = $this->_transportBuilder
                        ->setTemplateIdentifier($emailTemplate)
                        ->setTemplateOptions(
                            [
                                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                                'store' => $sellerStoreId,
                            ]
                        )
                        ->setTemplateVars(['data' => $postObject])
                        ->setFrom($sender)
                        ->addTo($vendorEmail);
                    $transport->getTransport()->sendMessage();
                }
            }
        }

        $this->_messageManager->addSuccess(__("Advertise status has been changed.")); 
        return $this->resultRedirectFactory->create()->setPath('*/adminads/view/id/'.$postData['ad_id'],
                ['_secure' => $this->getRequest()->isSecure()]);
    }
}

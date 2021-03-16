<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Observer;

use Magento\Framework\Event\ObserverInterface;
use Webkul\Marketplace\Model\ResourceModel\Seller\CollectionFactory;
use Webkul\Marketplace\Model\SellerFactory as MpSellerFactory;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Magento\UrlRewrite\Model\UrlRewriteFactory;
use Webkul\Marketplace\Helper\Email as MpEmailHelper;

/**
 * Mangoit Marketplace CustomerRegisterSuccessObserver Observer.
 */
class CustomerRegisterSuccessObserver extends \Webkul\Marketplace\Observer\CustomerRegisterSuccessObserver
{

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var MpSellerFactory
     */
    protected $mpSellerFactory;

    /**
     * @var UrlRewriteFactory
     */
    protected $urlRewriteFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlInterface;

    /**
     * @var MpEmailHelper
     */
    protected $mpEmailHelper;

    /**
     * @var \Magento\Backend\Model\Url
     */
    protected $urlBackendModel;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_sellerModel;
    protected $_urlInterface;
    protected $_customerModel;
    protected $_urlRewrite;
    protected $_webkulHelper;
    protected $_webkulEmailHelper;

    public function __construct(
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        MpHelper $mpHelper,
        MpSellerFactory $mpSellerFactory,
        UrlRewriteFactory $urlRewriteFactory,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\UrlInterface $urlInterface,
        MpEmailHelper $mpEmailHelper,
        \Magento\Backend\Model\Url $urlBackendModel,        
        \Magento\Framework\ObjectManagerInterface $objectManager,
        CollectionFactory $collectionFactory,
        \Webkul\Marketplace\Model\Seller $sellerModel,
        \Magento\Customer\Model\Session $customerModel,
        \Magento\UrlRewrite\Model\UrlRewrite $urlRewrite,
        \Webkul\Marketplace\Helper\Data $webkulHelper,
        \Webkul\Marketplace\Helper\Email $webkulEmailHelper
    ) {
        $this->_storeManager = $storeManager;
        $this->_messageManager = $messageManager;
        $this->_date = $date;
        $this->mpHelper = $mpHelper;
        $this->mpSellerFactory = $mpSellerFactory;
        $this->urlRewriteFactory = $urlRewriteFactory;
        $this->customerSession = $customerSession;
        $this->urlInterface = $urlInterface;
        $this->mpEmailHelper = $mpEmailHelper;
        $this->urlBackendModel = $urlBackendModel;
        $this->_objectManager = $objectManager;
        $this->_collectionFactory = $collectionFactory;
        $this->_sellerModel = $sellerModel;
        $this->_customerModel = $customerModel;
        $this->_urlRewrite = $urlRewrite;
        $this->_webkulHelper = $webkulHelper;
        $this->_webkulEmailHelper = $webkulEmailHelper;
        parent::__construct($date, $storeManager, $messageManager, $mpHelper, $mpSellerFactory, $urlRewriteFactory, $customerSession, $urlInterface, $mpEmailHelper, $urlBackendModel, $objectManager, $collectionFactory, $sellerModel, $customerModel, $urlRewrite, $webkulHelper, $webkulEmailHelper);
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $observer['account_controller'];
        try {
            $paramData = $data->getRequest()->getParams();
            if (!empty($paramData['is_seller']) && !empty($paramData['profileurl']) && $paramData['is_seller'] == 1) {
                $customer = $observer->getCustomer();
                $profileurlcount = $this->_sellerModel->getCollection();
                $profileurlcount->addFieldToFilter(
                    'shop_url',
                    $paramData['profileurl']
                );
                if (!$profileurlcount->getSize()) {
                    $status = $this->_objectManager->get(
                        'Webkul\Marketplace\Helper\Data'
                    )->getIsPartnerApproval() ? 0 : 1;
                    $customerid = $customer->getId();
                    $model = $this->_sellerModel;
                    $model->setData('is_seller', $status);
                    $model->setData('shop_url', $paramData['profileurl']);
                    $model->setData('website_url', $paramData['website_url']);
                    $model->setData('contact_number', $paramData['contact_number']);
                    $model->setData('company_locality', $paramData['comp_address']);
                    // $model->setData('generate_invoice', $paramData['generate_invoice']);
                    $model->setData('become_seller_request', 1);
                    $model->setData('seller_id', $customerid);
                    $model->setData('store_id', 0);
                    $model->setCreatedAt($this->_date->gmtDate());
                    $model->setUpdatedAt($this->_date->gmtDate());
                    if ($status == 0) {
                        $model->setAdminNotification(1);
                    }
                    $model->save();             

                    $loginUrl = $this->urlInterface->getUrl("marketplace/account/dashboard");

                    $this->_customerModel->setBeforeAuthUrl($loginUrl);

                    $this->_customerModel->setAfterAuthUrl($loginUrl);
                    $helper = $this->_webkulHelper;

                    if ($helper->getAutomaticUrlRewrite()) {
                        $this->createSellerPublicUrls($paramData['profileurl']);
                    }
                    $adminStoremail = $helper->getAdminEmailId();
                    $adminEmail = $adminStoremail ? $adminStoremail : $helper->getDefaultTransEmailId();
                    
                    $adminUsername = $this->_objectManager->get(
                        'Mangoit\Marketplace\Helper\Corehelper'
                    )->adminEmailName();
                    /*$adminUsername = 'Admin';*/

                    $senderInfo = [
                        'name' => $customer->getFirstName().' '.$customer->getLastName(),
                        'email' => $customer->getEmail(),
                    ];
                    $receiverInfo = [
                        'name' => $adminUsername,
                        'email' => $adminEmail,
                    ];
                    $emailTemplateVariables['myvar1'] = $customer->getFirstName().' '.
                    $customer->getLastName();
                    $emailTemplateVariables['myvar2'] = $this->_objectManager->get(
                        'Magento\Backend\Model\Url'
                    )->getUrl(
                        'customer/index/edit',
                        ['id' => $customer->getId()]
                    );
                    $emailTemplateVariables['myvar3'] = 'Admin';
                    $emailTemplateVariables['myvar4'] = $paramData['profileurl'];
                    $emailTemplateVariables['myvar5'] = $paramData['website_url'];
                    $emailTemplateVariables['myvar6'] = $paramData['contact_number'];
                    $emailTemplateVariables['myvar7'] = $paramData['comp_address'];
                    $emailTemplateVariables['myvar8'] = $paramData['firstname'];
                    $emailTemplateVariables['myvar9'] = $paramData['lastname'];
                    $sellerStoreId = $customer->getStoreId();
                    $this->_webkulEmailHelper->sendNewSellerRequest(
                        $emailTemplateVariables,
                        $senderInfo,
                        $receiverInfo,
                        $sellerStoreId
                    );
                } else {
                    $this->_messageManager->addError(
                        __('This Shop URL already Exists.')
                    );
                }
            }
        } catch (\Exception $e) {
            $this->_messageManager->addError($e->getMessage());
        }
    }

    private function createSellerPublicUrls($profileurl = '')
    {
        if ($profileurl) {
            $getCurrentStoreId = $this->_webkulHelper->getCurrentStoreId();

            /*
            * Set Seller Profile Url
            */
            $sourceProfileUrl = 'marketplace/seller/profile/shop/'.$profileurl;
            $requestProfileUrl = $profileurl;
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $profileRequestUrl = '';

            $urlCollectionData = $this->_urlRewrite->getCollection()
            ->addFieldToFilter('target_path', $sourceProfileUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);

            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $profileRequestUrl = $value->getRequestPath();
            }
            if ($profileRequestUrl != $requestProfileUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceProfileUrl)
                ->setRequestPath($requestProfileUrl)
                ->save();
            }

            /*
            * Set Seller Collection Url
            */
            $sourceCollectionUrl = 'marketplace/seller/collection/shop/'.$profileurl;
            $requestCollectionUrl = $profileurl.'/collection';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $collectionRequestUrl = '';

            $urlCollectionData = $this->_urlRewrite->getCollection()
            ->addFieldToFilter('target_path', $sourceCollectionUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);

            foreach ($urlCollectionData as $value) {
                $urlId = $value->getId();
                $collectionRequestUrl = $value->getRequestPath();
            }
            if ($collectionRequestUrl != $requestCollectionUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceCollectionUrl)
                ->setRequestPath($requestCollectionUrl)
                ->save();
            }

            /*
            * Set Seller Feedback Url
            */
            $sourceFeedbackUrl = 'marketplace/seller/feedback/shop/'.$profileurl;
            $requestFeedbackUrl = $profileurl.'/feedback';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $feedbackRequestUrl = '';

            $urlFeedbackData = $this->_urlRewrite->getCollection()
            ->addFieldToFilter('target_path', $sourceFeedbackUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);

            foreach ($urlFeedbackData as $value) {
                $urlId = $value->getId();
                $feedbackRequestUrl = $value->getRequestPath();
            }
            if ($feedbackRequestUrl != $requestFeedbackUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceFeedbackUrl)
                ->setRequestPath($requestFeedbackUrl)
                ->save();
            }

            /*
            * Set Seller Location Url
            */
            $sourceLocationUrl = 'marketplace/seller/location/shop/'.$profileurl;
            $requestLocationUrl = $profileurl.'/location';
            /*
            * Check if already rexist in url rewrite model
            */
            $urlId = '';
            $locationRequestUrl = '';

            $urlLocationData = $this->_urlRewrite->getCollection()
            ->addFieldToFilter('target_path', $sourceLocationUrl)
            ->addFieldToFilter('store_id', $getCurrentStoreId);

            foreach ($urlLocationData as $value) {
                $urlId = $value->getId();
                $locationRequestUrl = $value->getRequestPath();
            }
            if ($locationRequestUrl != $requestLocationUrl) {
                $idPath = rand(1, 100000);
                $this->_objectManager->create('Magento\UrlRewrite\Model\UrlRewrite')
                ->load($urlId)
                ->setStoreId($getCurrentStoreId)
                ->setIsSystem(0)
                ->setIdPath($idPath)
                ->setTargetPath($sourceLocationUrl)
                ->setRequestPath($requestLocationUrl)
                ->save();
            }
        }
    }
}

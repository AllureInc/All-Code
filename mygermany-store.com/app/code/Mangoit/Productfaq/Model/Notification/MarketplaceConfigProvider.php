<?php

namespace Mangoit\Productfaq\Model\Notification;

use Magento\Customer\Api\CustomerRepositoryInterface as CustomerRepository;
use Magento\Customer\Model\Context as CustomerContext;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Customer\Model\Url as CustomerUrlManager;
use Magento\Framework\App\Http\Context as HttpContext;
use Magento\Framework\Data\Form\FormKey;
use Magento\Framework\Locale\FormatInterface as LocaleFormat;
use Magento\Framework\UrlInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;

class MarketplaceConfigProvider extends \Webkul\Marketplace\Model\Notification\MarketplaceConfigProvider
{
   /**
     * @var CustomerRepository
     */
    private $customerRepository;

    /**
     * @var CustomerSession
     */
    private $authSession;

    /**
     * @var FormKey
     */
    protected $formKey;

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;
    /**
     * @var UrlInterface
     */
    protected $helper;

    /**
     * View file system
     *
     * @var \Magento\Framework\View\Asset\Repository
     */
    protected $viewFileSystem;

     /**
      * @var \Magento\Framework\Stdlib\DateTime\DateTime
      */
    protected $date;

     /**
      * @var \Webkul\Marketplace\Model\ProductFactory
      */
    protected $productModel;

     /**
      * @var \Webkul\Marketplace\Model\SellerFactory
      */
    protected $sellerModel;

     /**
      * @var \Magento\Customer\Model\CustomerFactory
      */
    protected $customerModel;

     /**
      * @var \Magento\Catalog\Model\ProductFactory
      */
    protected $productFactory;

     /**
      * @var \Webkul\Marketplace\Model\FeedbackFactory
      */
    protected $feedbackFactory;

    public function __construct(
        \Magento\Backend\Model\Auth\Session $authSession,
        FormKey $formKey,
        ScopeConfigInterface $scopeConfig,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        \Webkul\Marketplace\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Framework\View\Asset\Repository $viewFileSystem,
        \Magento\Backend\Helper\Data $adminHelper,
        \Webkul\Marketplace\Model\ProductFactory $productModel,
        \Webkul\Marketplace\Model\SellerFactory $sellerModel,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Webkul\Marketplace\Model\FeedbackFactory $feedbackFactory,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->authSession = $authSession;
        $this->formKey = $formKey;
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
        $this->urlBuilder = $urlBuilder;
        $this->helper = $helper;
        $this->adminHelper = $adminHelper;
        $this->orderHelper = $orderHelper;
        $this->viewFileSystem = $viewFileSystem;
        $this->date = $date;
        $this->productModel = $productModel;
        $this->sellerModel = $sellerModel;
        $this->customerModel = $customerModel;
        $this->productFactory = $productFactory;
        $this->feedbackFactory = $feedbackFactory;
        $this->objectManager = $objectManager;
        parent::__construct($authSession, $formKey, $scopeConfig, $storeManager, $urlBuilder, $helper, $orderHelper, $date, $viewFileSystem, $adminHelper, $productModel, $sellerModel, $customerModel, $productFactory, $feedbackFactory, $objectManager);
    }

    public function getConfig()
    {
        if ($this->isAdminLoggedIn()) {
            $defaultImageUrl = $this->viewFileSystem->getUrlWithParams(
                'Webkul_Marketplace::images/icons_notifications.png',
                []
            );
            $output['formKey'] = $this->formKey->getFormKey();
            $output['image'] = $defaultImageUrl;
            $output['productNotification'] = $this->getProductNotificationData();
            $output['sellerNotification'] = $this->getSellerNotificationData();
            $output['faqNotification'] = $this->getFaqNotificationData();
            // echo "<pre>"; print_r($this->getSellerProfileNotification());
            // die();
            $output['sellerProfileNotification'] = $this->getSellerProfileNotification();
            $output['feedbackNotification'] = $this->getFeedbackNotificationData();
        }
        return $output;
    }
    /**
     * create newly created faq notification data.
     * @return array
     */
    protected function getFaqNotificationData()
    {
        $faqData = [];

        $faqCollection = $this->objectManager->create('Ced\Productfaq\Model\Productfaq')->getCollection()
        ->addFieldToFilter('admin_notification', ['neq' => 0])
        ->addFieldToFilter('is_translated', ['eq' => 1]);
        if ($faqCollection->getSize()) {
            $faqData ['title'] = 'FAQ Notification';
            $faqData ['comment'] = 'You have new FAQ request from the vendor';
            $faqData['length'] = $faqCollection->getSize();

        }
        return $faqData;
    }
    /**
     * create newly created faq notification data.
     * @return array
     */
    protected function getSellerProfileNotification()
    {
        $sellerProfileData = [];

        $sellerCollection = $this->objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()->addFieldToFilter('profile_admin_notification', ['neq' => 0]);
        $sellerCollection->getSelect()->group('seller_id');
        // $sellerCollection = $this->objectManager->create('Webkul\Marketplace\Model\Seller')->getCollection()->getSelect()->group('seller_id')->addFieldToFilter('admin_notification', ['neq' => 0]);
        if ($sellerCollection->getSize()) {

             foreach ($sellerCollection as $seller) {
                $title = '';
                $desc = '';
                if ($seller->getProfileAdminNotification() == 1) {
                    $title = __('Seller profile verificaton request. Please see recent vendor profile');
                    $desc = __(
                        sprintf(
                            'Customer "%s" requested to verify there profile, click here to see Marketplce Seller list.',
                            '<span class="wk-focus">'.$this->getSellerName($seller->getSellerId())->getName().'</span>'
                        )
                    );
                }
                $sellerProfileData[] = [
                    'seller_id' => $seller->getSellerId(),
                    'title' => $title,
                    'desc'  => $desc,
                    'seller_name' => $this->getSellerName($seller->getSellerId())->getName(),
                    'updated_time'  => $this->date->gmtDate(
                        'l jS \of F Y h:i:s A',
                        strtotime($seller->getUpdatedAt())
                    ),
                    'url' => $this->adminHelper->getUrl('marketplace/seller')
                ];
            }
            // $sellerProfileData ['title'] = 'Seller Profile Notification';
            // $sellerProfileData ['comment'] = 'You have new seller profile request. Please see recent vendor profile.';
            // $sellerProfileData['length'] = $sellerCollection->getSize();

        }
        return $sellerProfileData;
    }
    private function isAdminLoggedIn()
    {
        return (bool)$this->authSession->isLoggedIn();
    }
}

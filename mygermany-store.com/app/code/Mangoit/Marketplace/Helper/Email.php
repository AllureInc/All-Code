<?php
namespace Mangoit\Marketplace\Helper;

use Magento\Framework\App\Action\Action;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;
use Webkul\MarketplacePreorder\Api\PreorderItemsRepositoryInterface;
use Webkul\MarketplacePreorder\Api\PreorderSellerRepositoryInterface;
use Webkul\MarketplacePreorder\Api\PreorderCompleteRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Eav\Model\ResourceModel\Entity\Attribute;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\App\Area;

/**
 * CMS Page Helper
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class Email extends \Webkul\MarketplacePreorder\Helper\Email
{
        /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var PreorderItemCollection
     */
    protected $_itemsCollectionFactory;
    /**
     * @var Webkul\MarketplacePreorder\Model\Sourcer\PreorderSpecification
    */
    protected $_preorderSpecification;


    protected $_productFactory;


    protected $_marketplaceProductFactory;

    /**
     * @var PreorderItemsRepositoryInterface
     */
    protected $_itemsRepository;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_marketplaceHelper;

    /**
     * @var Data
     */
    protected $_preorderHelper;

    /**
     * @var [type]
     */
    protected $_inlineTranslation;

    /**
     * @var [type]
     */
    protected $_transportBuilder;

    /**
     * @var [type]
     */
    protected $_misLogger;

    /**
     *
     * @param \Magento\Framework\App\Helper\Context      $context
     * @param \Magento\Framework\ObjectManagerInterface  $objectManager
     * @param \Magento\Customer\Model\Session            $customerSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Catalog\Model\ProductFactory      $product
     * @param \Webkul\Marketplace\Model\ProductFactory   $marketplaceProduct
     * @param \Webkul\Marketplace\Helper\Data            $marketplaceHelper
     * @param TransportBuilder                           $transportBuilder
     * @param StateInterface                             $inlineTranslation
     * @param Data                                       $preorderHelper
     */
    /*public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\ProductFactory $product,
        \Webkul\Marketplace\Model\ProductFactory $marketplaceProduct,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        TransportBuilder $transportBuilder,
        StateInterface $inlineTranslation,
        Data $preorderHelper
    ) {
        $this->_scopeConfig = $context->getScopeConfig();
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        $this->_productFactory = $product;
        $this->_marketplaceProductFactory = $marketplaceProduct;
        $this->_marketplaceHelper = $marketplaceHelper;
        $this->_inlineTranslation = $inlineTranslation;
        $this->_transportBuilder = $transportBuilder;
        $this->_preorderHelper = $preorderHelper;
        parent::__construct($context, $objectManager, $customerSession, $storeManager, $product, $marketplaceProduct, $marketplaceHelper, $transportBuilder, $inlineTranslation, $preorderHelper);
    }*/

    public function getLogger()
    {
        $this->_misLogger =  $this->_objectManager->create('Psr\Log\LoggerInterface');
    }

    /**
     * isAutoEmail
     * used to check whether automatic email is set or not].
     * @param int $sellerId
     * @return bool
     */
    public function isAutoEmail($sellerId)
    {
        $emailAction = $this->_preorderHelper->getEmailAction($sellerId);
        if ($emailAction == 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Send Notification Email when Product is in Stock.
     *
     * @param array emailIds
     * @param string $productName
     */
    public function sendNotifyEmail($emailIds, $productName)
    {
        $this->getLogger();
        $this->_misLogger->info("##### MarketPlace Preorder Email ######");
        $this->_misLogger->info("##");
        $this->_misLogger->info("##### sendNotifyEmail Function ######");

        $adminEmail = $this->getAdminEmail();
        $loginUrl = $this->getRedirectUrl();
        if ($adminEmail != '') {
            $area = Area::AREA_FRONTEND;
            $store = $this->_storeManager->getStore()->getId();
            $msg = __('Product "%1" is in stock. Please go your account to complete preorder.', $productName);
            $templateOptions = ['area' => $area, 'store' => $store];
            $templateVars = [
                                'store' => $this->_storeManager->getStore(),
                                'message' => $msg,
                                'login_url' => $loginUrl,
                            ];
            $from = ['email' => $adminEmail, 'name' => 'Admin myGermany Store'];
            foreach ($emailIds as $emailId) {
                $customer = $this->getCustomer($emailId);
                $templateVars['customer_name'] = $customer->getName();
                $this->_inlineTranslation->suspend();
                $to = [$emailId];

                $sellerStoreId = $customer->getCreatedIn();
                if ($sellerStoreId == 'Germany') {
                    $sellerStoreId = 7;
                } else {
                    $sellerStoreId = 1;
                }

                $this->_misLogger->info("###");
                $this->_misLogger->info("#### 1 Customer's Email ID: ".$sellerStoreId);
                $this->_misLogger->info("###");

                $this->_misLogger->info("#### 1 Email Send Function Called ####");
                try {
                    $this->_objectManager->get('Mangoit\Marketplace\Helper\MarketplaceEmail')->sendPreorderInStockEmail(
                        $sellerStoreId, 
                        $templateVars, 
                        $from, 
                        ['email'=> $emailId, 'name'=> $templateVars['customer_name']], 
                        $templateOptions
                    );

                    $this->_misLogger->info("##");
                    $this->_misLogger->info("### 1 Preorder Mail has been sent to ".$emailId.".");
                    $this->_misLogger->info("##");                    
                } catch (Exception $e) {
                    $this->_misLogger->info("### 1 Exception in Preorder 2 : ".$e->getMessage());    
                }

                /*$transport = $this->_transportBuilder
                                    ->setTemplateIdentifier('preorder_in_stock_notify')
                                    ->setTemplateOptions($templateOptions)
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($from)
                                    ->addTo($to)
                                    ->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();*/
            }
        }
    }

    public function getAdminEmail()
    {
        $adminStoremail = $this->_marketplaceHelper->getAdminEmailId();
        $adminEmail = $adminStoremail ? $adminStoremail : $this->_marketplaceHelper->getDefaultTransEmailId();

        return $adminEmail;
    }
    /**
     * Get Account Login Url For Customer.
     *
     * @return string
     */
    public function getRedirectUrl()
    {
        return $this->_urlBuilder->getUrl('sales/order/history/');
    }

    /**
     * Get Account Login Url For Customer.
     *
     * @return string
     */
    public function getLogInUrl()
    {
        return $this->_urlBuilder->getUrl('customer/account/login/');
    }

    public function notifyBuyers($emailIds, $productIds, $prorderIds)
    {
        $this->getLogger();
        $this->_misLogger->info("##### MarketPlace Preorder Email ######");
        $this->_misLogger->info("##");
        $this->_misLogger->info("##### notifyBuyers Function ######");

        $sellerId = $this->_customerSession->getCustomerId();
        $loginUrl = $this->getLogInUrl();
        $senderName = '';
        $senderEmail = '';
        if ($sellerId) {
            $sender =  $this->_objectManager->create(
                'Magento\Customer\Model\Customer'
            )->load($sellerId);

            $senderName = $sender->getName();
            $senderEmail = $sender->getEmail();
        } else {
            $senderName = $this->_objectManager->get('Mangoit\Marketplace\Helper\Corehelper')->adminEmailName();
            /*$senderName = 'Admin';*/
            $senderEmail = $this->getAdminEmail();
        }
        if ($senderEmail != '') {
            $area = Area::AREA_FRONTEND;
            $store = $this->_storeManager->getStore()->getId();
            $templateOptions = ['area' => $area, 'store' => $store];
            $templateVars = [
                'store' => $this->_storeManager->getStore(),
                'login_url' => $loginUrl,
            ];
            $from = ['email' => $senderEmail, 'name' => $senderName];
            foreach ($emailIds as $key => $emailId) {
                $customer = $this->getCustomer($emailId);
                
                $product = $this->_preorderHelper->getProduct($productIds[$key]);
                $msg = __(
                    'Product "%1" is in stock. Please go your account to complete preorder.',
                    $product->getName()
                );
                $templateVars['message'] = $msg;
                $templateVars['customer_name'] = $customer->getName();

                $this->_inlineTranslation->suspend();
                $to = [$emailId];

                $sellerStoreId = $customer->getCreatedIn();
                if ($sellerStoreId == 'Germany') {
                    $sellerStoreId = 7;
                } else {
                    $sellerStoreId = 1;
                }

                $this->_misLogger->info("###");
                $this->_misLogger->info("#### Customer's Email ID: ".$sellerStoreId);
                $this->_misLogger->info("###");

                $this->_misLogger->info("#### Email Send Function Called ####");
                try {
                    $this->_objectManager->get('Mangoit\Marketplace\Helper\MarketplaceEmail')->sendPreorderInStockEmail(
                        $sellerStoreId, 
                        $templateVars, 
                        $from, 
                        ['email'=> $emailId, 'name'=> $templateVars['customer_name']], 
                        $templateOptions
                    );

                    $this->_misLogger->info("##");
                    $this->_misLogger->info("### Preorder Mail has been sent to ".$emailId.".");
                    $this->_misLogger->info("##");                    
                } catch (Exception $e) {
                    $this->_misLogger->info("### Exception in Preorder 2 : ".$e->getMessage());    
                }


                /*$transport = $this->_transportBuilder
                                    ->setTemplateIdentifier('preorder_in_stock_notify')
                                    ->setTemplateOptions($templateOptions)
                                    ->setTemplateVars($templateVars)
                                    ->setFrom($from)
                                    ->addTo($to)
                                    ->getTransport();
                $transport->sendMessage();
                $this->_inlineTranslation->resume();*/
            }
        }
    }

    public function getCustomer($emailId)
    {
        return $this->_objectManager->create(
            'Magento\Customer\Model\Customer'
        )->loadByEmail($emailId);
    }
}

<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Helper;

use Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface;
use Magento\Framework\Session\SessionManager;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    private $customerId;

    private static $_exportStatus = [
            0 => 'Pending',
            1 => 'Complete',
    ];

    private static $_amzProStatus = [
            0 => 'Failed',
            1 => 'Active',
            2 => 'Inactive',
            3 => 'Pending'
    ];

    public $rktnClient;

    public $accountRepository;

    private $logger;

    public $config;

    /**
     * @param \Magento\Framework\App\Helper\Context $context
     * @param Amazonmws                             $amazonmws
     * @param AmazonGlobal                          $amazonGlobal
     * @param AmazonTempDataRepositoryInterface     $rakutenTempDataRepo
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface $rakutenTempDataRepo,
        \Mangoit\RakutenConnector\Api\AccountsRepositoryInterface $accountRepository,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Helper\Data $mpHelper,
        \Magento\Framework\App\Config\Storage\WriterInterface $configWriter,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\RakutenConnector\Model\Accounts $accounts,
        ProductMapRepositoryInterface $productMapRepo,
        SessionManager $coreSession,
        \Webkul\Marketplace\Model\Product $mpProduct,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor
    ) {
        parent::__construct($context);
        $this->productMapRepo = $productMapRepo;
        $this->rakutenTempDataRepo = $rakutenTempDataRepo;
        $this->customerSession = $customerSession;
        $this->rakutenTempDataRepo = $rakutenTempDataRepo;
        $this->accountRepository = $accountRepository;
        $this->logger = $logger;
        $this->mpHelper = $mpHelper;
        $this->configWriter = $configWriter;
        $this->objectManager = $objectManager;
        $this->storeManager = $storeManager;
        $this->accounts = $accounts;
        $this->coreSession = $coreSession;
        $this->mpProduct = $mpProduct;
        $this->encryptor = $encryptor;
    }

    /**
     * decrpyted the obscure value
     *
     * @param string $fieldValue
     * @return string
     */
    public function getDecryptValue($fieldValue)
    {
        return $this->encryptor->decrypt($fieldValue);
    }

    /**
     * get current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        return $this->mpHelper->getWebsiteId();
    }

    /**
     * get current store
     *
     * @return int
     */
    public function getCurrentStore()
    {
        return $this->mpHelper->getCurrentStoreId();
    }

    /**
     * get list of required states
     *
     * @return array
     */
    public function getRequiredStateList()
    {
        return $this->scopeConfig->getValue(
            'general/region/state_required',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * get currency code of amazon account
     * @return string
     */
    // public function getAmazonCurrencyCode()
    // {
    //     return $this->config['currency_code'];
    // }

    /**
     * get currency rate
     *
     * @param string $currency
     * @return void | int
     */
    public function getCurrencyRate($currency)
    {
        return $this->storeManager->getStore()
                    ->getBaseCurrency()->getRate($currency);
    }

    /**
     * get amazon feed status
     * @return string
     */
    public function getExportStatus($status)
    {
        return self::$_exportStatus[$status];
    }

    /**
     * get amazon product status
     * @return int
     */
    public function getAmzProductStatus($status)
    {
        if ($status!== '') {
            $status = self::$_amzProStatus[$status];
        } else {
            $status = '';
        }
        return $status;
    }

    public function sellerProApprovalRequired()
    {
        return $this->mpHelper->getIsProductApproval();
    }
    /**
     * get import product type
     * @return int
     */
    public function getImportTypeOfProduct()
    {
        return $this->config['product_create'];
    }

    /**
     * get status of amazon magento product
     * @return int
     */
    public function getItemReviseStatus()
    {
        return $this->config['revise_item'];
    }

    /**
     * get default website for amazon product
     * @return int|null
     */
    public function getDefaultWebsite()
    {
        return $this->config['default_website'];
    }

    /**
     * get default product qty
     * @return int
     */
    public function getDefaultProductQty()
    {
        return $this->config['default_pro_qty'];
    }

    /**
     * get default product qty
     * @return int
     */
    public function getDefaultTaxClassId()
    {
        return $this->config['default_tax_class_id'];
    }

    /**
     * get default product weight
     * @return int
     */
    public function getDefaultProductWeight()
    {
        return $this->config['default_pro_weight'];
    }

    /**
     * get attribute set id
     * @param  int $accountId
     * @return int
     */
    public function getAttributeSet()
    {
        return $this->config['attribute_set'];
    }

    /**
     * get default Category
     * @return int
     */
    public function getDefaultCategory()
    {
        return $this->config['default_cate'];
    }

    /**
     * get default store for order sync
     * @return int
     */
    public function getDefaultStoreOrderSync()
    {
        return $this->scopeConfig
                ->getValue('rakutenconnect/order_option/defaultstore');
    }

    /**
     * get order status
     * @return int
     */
    public function getOrderStatus()
    {
        return $this->config['order_status'];
    }

    /**
     * check street address line
     * @return int
     */
    public function getStreetLineNumber()
    {
        return $this->scopeConfig
                ->getValue('customer/address/street_lines');
    }

    /**
     * get count of imported item
     * @param  string $itemType
     * @param  int $sellerId
     * @return object
     */
    public function getTotalImported($itemType, $sellerId, $all = false)
    {
        $collection = $this->rakutenTempDataRepo
                    ->getByAccountIdnItemType($itemType, $sellerId);
        if ($all) {
            return $collection;
        } else {
            foreach ($collection as $record) {
                return $record;
            }
        }
    }

    /**
     * get amazon account id from collection
     *
     * @param object $collection
     * @return void | int
     */
    public function getRakutenAccountId($collection)
    {
        $sellerId = false;
        if ($collection->getSize()) {
            foreach ($collection as $account) {
                $sellerId = $account->getSellerId();
            }
        }
        return $sellerId;
    }

    /**
     * get id by collection
     *
     * @return int
     */
    public function getById($collection)
    {
        $id = null;
        if ($collection->getSize()) {
            foreach ($collection as $account) {
                $id = $account->getEntityId();
            }
        }
        return $id;
    }

    /**
     * get product attribute's value
     * @param  object $product
     * @param  string $attributCode
     * @return string
     */
    public function getProductAttrValue($product, $attributCode)
    {
        return (string)$product->getResource()->getAttribute($attributCode)
                ->getFrontend()->getValue($product);
    }

    /**
     * get Rakuten Client
     *
     * @param boolean $sellerId
     * @param boolean $amzCredentails
     * @return void
     */
    public function validateRakutenAccount($amzCredentails = false)
    {
        try {
            $config = [
                // 'Seller_Id'             => $amzCredentails['amz_seller_id'],
                // 'Marketplace_Id'        => $amzCredentails['marketplace_id'],
                // 'Access_Key_ID'         => $amzCredentails['access_key_id'],
                // 'MWSAuthToken'          => $amzCredentails['marketplace_id'],
                // 'Application_Version'   => '0.0.*',
                'Secret_Access_Key'     => $amzCredentails['secret_key']
            ];
            $this->rktnClient = new RktnClient($config);
            if ($this->rktnClient->validateCredentials()) {
                return $this->rktnClient;
            } else {
                return false;
            }
        } catch (\Exception $e) {
            $this->logger->info('Data validateRakutenAccount : '.$e->getMessage());
            return false;
        }
    }

    /**
     * get configuration field of amazon
     *
     * @param string $field
     * @return void
     */
    public function getStoreConfig($field)
    {
        return $this->scopeConfig->getValue("rakutenconnect/configuration/$field");
    }

    /**
     * get Rakuten Client
     *
     * @param boolean $sellerId
     * @return void
     */
    public function getRktnClient($entityId = false)
    {
        try {
            if (!$this->rktnClient) {
                $rktnCredentails = $this->getSellerRktnCredentials(true, $entityId)->toArray();

                $this->config = [
                    // 'Seller_Id'             => $amzCredentails['amz_seller_id'],
                    // 'Marketplace_Id'        => $amzCredentails['marketplace_id'],
                    // 'Access_Key_ID'         => $amzCredentails['access_key_id'],
                    // 'MWSAuthToken'          => $amzCredentails['marketplace_id'],
                    // 'Application_Version'   => '0.0.*',
                    // 'inventory_report_id'   => $amzCredentails['inventory_report_id'],
                    // 'listing_report_id'     => $amzCredentails['listing_report_id'],
                    // 'currency_code'         => $amzCredentails['currency_code'],
                    'attribute_set'         => $rktnCredentails['attribute_set'],
                    'revise_item'           => $rktnCredentails['revise_item'],
                    'default_cate'          => $rktnCredentails['default_cate'],
                    'default_store_view'    => $rktnCredentails['default_store_view'],
                    'product_create'        => $rktnCredentails['product_create'],
                    'default_website'       => $rktnCredentails['default_website'],
                    'order_status'          => $rktnCredentails['order_status'],
                    'default_pro_qty'       => $rktnCredentails['default_pro_qty'],
                    'default_pro_weight'    => $rktnCredentails['default_pro_weight'],
                    'default_tax_class_id'    => $rktnCredentails['default_tax_class_id'],
                    'Secret_Access_Key'     => $rktnCredentails['secret_key']
                ];
                $this->rktnClient = new RktnClient($this->config);
            }
            return $this->initialilzeRktnClient($this->rktnClient);
        } catch (\Exception $e) {
            $this->logger->info('Data getRktnClient : '.$e->getMessage());
            return false;
        }
    }

    /**
     * initialize Rakuten Client
     *
     * @param object $rktnClient
     * @return object
     */
    public function initialilzeRktnClient($rktnClient)
    {
        $manageProductRawData = $this->objectManager
                        ->get('Mangoit\RakutenConnector\Helper\ManageProductRawData');
        $product = $this->objectManager
                        ->get('Mangoit\RakutenConnector\Helper\Product');
        $manageOrderRawData = $this->objectManager
                        ->get('Mangoit\RakutenConnector\Helper\ManageOrderRawData');
        $order = $this->objectManager
                        ->get('Mangoit\RakutenConnector\Helper\Order');
        $productOnRakuten = $this->objectManager
                        ->get('Mangoit\RakutenConnector\Helper\ProductOnRakuten');

        // initialize Rakuten Client variable manageProductRawData
        $manageProductRawData->rktnClient = $rktnClient;
        // initialize Rakuten Client variable at product
        $product->rktnClient = $rktnClient;
        // initialize Rakuten Client variable productOnRakuten
        $productOnRakuten->rktnClient = $rktnClient;
        // initialize Rakuten Client variable ManageOrderRawData
        $manageOrderRawData->rktnClient = $rktnClient;
        // initialize Rakuten Client variable Order
        $order->rktnClient = $rktnClient;

        return $rktnClient;
    }

    /**
     * get amazon credentials by seller id
     *
     * @param int $sellerId
     * @return void
     */
    public function getSellerRktnCredentials($needObject = false, $entityId = 0)
    {
        if ($needObject || !$this->config) {
            $sellerId = $this->getCustomerId($entityId);
            return $this->accountRepository->getById($sellerId);
        } else {
            return $this->config;
        }
    }

    /**
     * get customer id
     *
     * @return int
     */
    public function getCustomerId($entityId = 0)
    {
        if ($this->customerId) {
            return $this->customerId;
        } elseif ($this->mpHelper->getCustomerId()) {
            $this->customerId = $this->mpHelper->getCustomerId();
        } elseif (!$this->customerId && $entityId) {
            $this->customerId = $this->accounts->load($entityId)->getSellerId();
        } else {
            $this->customerId = 0;
        }
        return $this->customerId;
    }

    /**
     * get amazon participation
     *
     * @return array
     */
    public function getAmazonParticipation($marketplaceId)
    {
        $sellerPost = [];
        $sellerParticipation = $this->rktnClient->ListMarketplaceParticipations();
        $participateMp = $sellerParticipation['ListMarketplaces']['Marketplace'];
        $participateMp = isset($participateMp[0]) ? $participateMp : [0 => $participateMp];
        foreach ($participateMp as $marketplace) {
            if ($marketplace['MarketplaceId'] === $marketplaceId) {
                $sellerPost['currency_code'] =  $marketplace['DefaultCurrencyCode'];
                $sellerPost['country'] = $marketplace['DefaultCountryCode'];
            }
        }
        return $sellerPost;
    }

    /**
     * get single record from collection
     *
     * @return object | void
     */
    public function getRecordModel($collection)
    {
        $recordModel = false;
        if ($collection->getSize()) {
            foreach ($collection as $record) {
                $recordModel = $record;
            }
        }
        return $recordModel;
    }

    /**
     * get exported pending status count
     *
     * @param int $accountId
     * @return void | object
     */
    public function getExportedProColl($sellerId)
    {
        $productMapColl = $this->productMapRepo->getByAccountId($sellerId);
        $productMapColl->addFieldToFilter('export_status', 0);
        return $productMapColl;
    }

    /**
     * get all allowed currency
     *
     * @return array
     */
    public function getAllowedCurrencies()
    {
        $currenciesArray = [];
        $availableCurrencies = $this->storeManager->getStore()->getAvailableCurrencyCodes();
        foreach ($availableCurrencies as $currencyCode) {
            $currenciesArray[] = $currencyCode;
        }
        return $currenciesArray;
    }

    /**
     * get core session
     *
     * @return object
     */
    public function getCoreSession()
    {
        return $this->coreSession;
    }

    /**
     * check session values
     *
     * @return bool
     */
    public function validateSessionVals()
    {
        $backendSession = $this->objectManager->get(
            '\Magento\Backend\Model\Session'
        );
        $cronSession = $this->coreSession->getData('rktn_cron');
        $productCreateSession = $this->coreSession->getData('rktn_session');
        if (!$backendSession->getAmzSession() && !$cronSession && !$productCreateSession) {
            return true;
        }
        return false;
    }

    public function getSellerProductIds($sellerId)
    {
        $filteredArray = [];
        $mappedProIds = $this->productMapRepo
                      ->getByAccountId($sellerId)
                      ->getColumnValues('magento_pro_id');

        $sellerProIds = $this->mpProduct->getCollection()                                                             ->addFieldToFilter('seller_id', $sellerId)
                      ->getColumnValues('mageproduct_id');
        if ($sellerId) {
            $filteredArray = array_diff($sellerProIds, $mappedProIds);
        } else {
            $filteredArray = array_diff($mappedProIds, $sellerProIds);
        }
        return $filteredArray;
    }

    /**
     * get Current Website Id
     * @return void
     */
    public function getCurrentWebsiteId()
    {
        return $this->storeManager->getStore()->getWebsiteId();
    }

    /**
     * get Current Store Id
     * @return void
     */
    public function getCurrentStoreId()
    {
        // give the current store id
        return $this->storeManager->getStore()->getStoreId();
    }
}

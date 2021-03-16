<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Helper;

use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Customer\Model\Session;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * MpSellerVacation data helper.
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var Magento\Framework\Stdlib\DateTime\TimezoneInterface
     */
    protected $timezone;

    /**
     * Customer session.
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Framework\Data\Form\FormKey\Validator
     */
    protected $_formKeyValidator;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $_productRepository;

    /**
     * @param Magento\Framework\App\Helper\Context        $context
     * @param Magento\Directory\Model\Currency            $currency
     * @param Magento\Customer\Model\Session              $customerSession
     * @param Magento\Framework\UrlInterface              $url
     * @param Magento\Catalog\Model\ResourceModel\Product $product
     * @param Magento\Store\Model\StoreManagerInterface   $_storeManager
     */
    public function __construct(
        Session $customerSession,
        \Magento\Framework\App\Helper\Context $context,
        FormKeyValidator $formKeyValidator,
        TimezoneInterface $timezone,
        DateTime $date,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
    ) {

        $this->_date = $date;
        $this->timezone = $timezone;
        $this->_customerSession = $customerSession;
        $this->_objectManager = $objectManager;
        $this->_formKeyValidator = $formKeyValidator;
        $this->_storeManager = $storeManager;
        $this->_productRepository = $productRepository;

        parent::__construct($context);
    }

    /**
     * function to check disable time.
     *
     * @param Webkul\MpSellerVacation\Model\Vacation $vacation
     *
     * @return bool
     */
    public function checkDisableTime($vacation)
    {
        if ($vacation->getProductStatus() == 1 && $vacation->getVacationStatus() == 1) {
            $dateTo=$this->converToTz(
                $vacation->getDateTo(),
                $vacation
            );
            $dateTo = $this->_date->timestamp($dateTo);
            $dateFrom=$this->converToTz(
                $vacation->getDateFrom(),
                $vacation
            );
            $dateFrom = $this->_date->timestamp($dateFrom);
            $vacationMode = $this->getVacationMode($vacation);
            if ($vacationMode == 'product_disable') {
                $currentDate = strtotime($this->timezone->date()->format('Y-m-d'));
            } else {
                $currentDate = strtotime($this->timezone->date()->format('Y-m-d H:i:s'));
            }
            if ($dateFrom <= $currentDate && $dateTo >= $currentDate) {
                return true;
            } else {
                return false;
            }
        } else {
            return false;
        }
    }
    /**
     * returns true if seller is on vacation else false.
     *
     * @param Webkul\MpSellerVacation\Model\Vacation $vacation
     *
     * @return bool
     */
    public function isOnVacation($vacation)
    {
        $dateTo=$this->converToTz(
            $vacation->getDateTo(),
            $vacation
        );
        $dateTo = $this->_date->timestamp($dateTo);
        $dateFrom=$this->converToTz(
            $vacation->getDateFrom(),
            $vacation
        );
        $dateFrom = $this->_date->timestamp($dateFrom);
        $vacationMode = $this->getVacationMode($vacation);
        if ($vacationMode == 'product_disable') {
            $currentDate = strtotime($this->timezone->date()->format('Y-m-d'));
        } else {
            $currentDate = strtotime($this->timezone->date()->format('Y-m-d h:i:s'));
        }
        if ($dateFrom <= $currentDate && $dateTo >= $currentDate) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * vacation mode set by admin in configuration.
     *
     * @return string
     */
    public function getVacationMode($vacation = false)
    {
        if (!$vacation) {
            return $this->scopeConfig
                ->getValue(
                    'mpsellervacation/vacation_settings/vacation_mode',
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                );
        } else {
            return $vacation->getProductDisableType();
        }
    }

    /**
     * vacation mode set by admin in configuration.
     *
     * @return string
     */
    public function getStoreCloseLabel()
    {
        return $this->scopeConfig
            ->getValue(
                'mpsellervacation/vacation_settings/label_for_disabled_pro',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
    }

    /**
     * vacation message set by admin for cart products in configuration.
     *
     * @return string
     */
    public function getCartVacationMessage()
    {
        return $this->scopeConfig
             ->getValue(
                 'mpsellervacation/vacation_settings/label_for_cart_pro',
                 \Magento\Store\Model\ScopeInterface::SCOPE_STORE
             );
    }

    /**
     * disable seller products if seller is on vacation.
     *
     * @param id $sellerId seller id
     */
    public function disableSellerProducts($sellerId)
    {
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
        $allStores = $this->getAllStores();
        $productIds = [];
        $sellerProducts = $this->getSellerProductData($sellerId);
        if ($sellerProducts->getSize() > 0) {
            foreach ($sellerProducts as $productInfo) {
                if ($productInfo->getMageproductId()) {
                    if ($this->checkIfAssociate($productInfo->getMageproductId())) {
                        continue;
                    }
                    $productIds[] = $productInfo->getMageproductId();
                    $productInfo->setStatus(2);
                    $productInfo->save();
                }
            }
            foreach ($allStores as $storeId) {
                $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                    ->updateAttributes($productIds, ['status' => $status], $storeId);
            }
        }
    }

    /**
     * check if product is child product.
     *
     * @param int $productId product id
     *
     * @return bool
     */
    public function checkIfAssociate($productId)
    {
        $product = $this->_objectManager
            ->create('Magento\ConfigurableProduct\Model\ResourceModel\Product\Type\Configurable')
            ->getParentIdsByChild($productId);
        if (isset($product[0])) {
            return true;
        }
        return false;
    }

    /**
     * disbale productIds.
     *
     * @param array $productIds product ids
     */
    public function disableProducts($productIds = [])
    {
        if (count($productIds) > 0) {
            $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_DISABLED;
            $allStores = $this->getAllStores();
            foreach ($allStores as $storeId) {
                $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                    ->updateAttributes($productIds, ['status' => $status], $storeId);
            }
        }
    }

    /**
     * enable seller products if vacation is over or undone.
     *
     * @param id $sellerId seller id
     */
    public function enableSellerProducts($sellerId)
    {
        $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
        $allStores = $this->getAllStores();
        $productIds = [];
        $sellerProducts = $this->getSellerProductData($sellerId);
        if ($sellerProducts->getSize() > 0) {
            foreach ($sellerProducts as $productInfo) {
                if ($productInfo->getMageproductId()) {
                    if ($this->checkIfAssociate($productInfo->getMageproductId())) {
                        continue;
                    }
                    $productIds[] = $productInfo->getMageproductId();
                    $productInfo->setStatus(1);
                    $productInfo->save();
                }
            }
            foreach ($allStores as $storeId) {
                $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                    ->updateAttributes($productIds, ['status' => $status], $storeId);
            }
        }
    }

    /**
     * enable products.
     *
     * @param array $productIds product ids
     */
    public function enableProducts($productIds = [])
    {
        if (count($productIds) > 0) {
            $status = \Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED;
            $allStores = $this->getAllStores();
            foreach ($allStores as $storeId) {
                $this->_objectManager->get('Magento\Catalog\Model\Product\Action')
                    ->updateAttributes($productIds, ['status' => $status], $storeId);
            }
        }
    }

    /**
     * function to get sellers products.
     *
     * @param int $sellerId seller id
     *
     * @return \Webkul\Marketplace\Model\Product
     */
    public function getSellerProductData($sellerId)
    {
        $model = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
            ->getCollection()
            ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        return $model;
    }

    /**
     * function to get sellers products.
     *
     * @param int $sellerId seller id
     *
     * @return \Webkul\Marketplace\Model\Product
     */
    public function getSellerDataByProduct($productId)
    {
        /*
         * Marketplace product collection
         * @var Webkul\Marketplace\Model\Product
         */
        $collection = $this->_objectManager->create('Webkul\Marketplace\Model\Product')
            ->getCollection()
            ->addFieldToFilter('mageproduct_id', $productId);
        if ($collection->getSize() > 0) {
            foreach ($collection as $seller) {
                $sellerCollection = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('seller_id', $seller->getSellerId());
                if ($sellerCollection->getSize() > 0) {
                    foreach ($sellerCollection as $sc) {
                        return $sc->getShopUrl();
                    }
                }
                break;
            }
        }
        return false;
    }
    /**
     * getAllStores function all stores in magento.
     *
     * @return array
     */
    public function getAllStores()
    {
        $storeIds = [];
        $stores = $this->_storeManager->getStores();
        foreach ($stores as $store) {
            $storeIds[] = $store->getData('store_id');
        }
        return $storeIds;
    }

    /**
     * get seller vacation data.
     *
     * @param int $sellerId seller id
     *
     * @return Webkul\MpSellerVacation\Model\ResourceModel\Vacation\Collection
     */
    public function getVacationdetails($sellerId)
    {
        /*
         * seller vacation collection
         */
        $vacation = $this->_objectManager
            ->create('\Webkul\MpSellerVacation\Model\ResourceModel\Vacation\Collection')
            ->addFieldToFilter('seller_id', ['eq' => $sellerId]);
        if ($vacation->getSize() > 0) {
            foreach ($vacation as $v) {
                return $v;
            }
        } else {
            return false;
        }
    }

    /**
     * function to get vacation status for specific product.
     *
     * @param int $product_id product ID
     *
     * @return bool
     */
    public function getProductvacationStatus($productId = false)
    {
        if ($productId) {
            $sellerData = false;
            /*
             * get product seller shop url according to product
             */
            $shopUrl = $this->getSellerDataByProduct($productId);
            if ($shopUrl) {
                /*
                 * get Seller data accroding to shop url
                 */
                $data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url', ['eq' => $shopUrl]);
                foreach ($data as $seller) {
                    $sellerData = $seller;
                }
                if ($sellerData) {
                    /*
                     * get vacation details of the product seller
                     */
                    $vacation = $this->getVacationdetails($sellerData->getSellerId());
                    if ($vacation) {
                        /*
                         * check if vacation is active
                         */
                        $isOnvacation = $this->checkDisableTime($vacation);

                        if ($isOnvacation) {
                            if ($this->getVacationMode($vacation) == 'add_to_cart_disable') {
                                return $this->getVacationMode($vacation);
                            } else {
                                return $this->getVacationMode($vacation);
                            }
                        }
                    }
                }
            }
            return false;
        }
        return false;
    }

    /**
     * update product status according to seller vacation status.
     *
     * @param int $productId
     *
     * @return string
     */
    public function updateProductVacationStatus($productId = null)
    {
        if ($productId) {
            $sellerData = false;
            /*
             * get product seller shop url according to product
             */
            $shopUrl = $this->getSellerDataByProduct($productId);
            if ($shopUrl) {
                /*
                 * get Seller data accroding to shop url
                 */
                $data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url', ['eq' => $shopUrl]);
                foreach ($data as $seller) {
                    $sellerData = $seller;
                }
                if ($sellerData) {
                    /*
                     * get vacation details of the product seller
                     */
                    $vacation = $this->getVacationdetails($sellerData->getSellerId());
                    if ($vacation) {
                        /*
                         * check if vacation is active
                         */
                        $isOnvacation = $this->checkDisableTime($vacation);
                        if ($isOnvacation) {
                            if ($this->getVacationMode($vacation) == 'product_disable') {
                                $this->disableProducts([$productId]);
                                $sellerData->setStatus(2);
                                $sellerData->save();
                                return 'disabled';
                            } else {
                                $this->enableProducts([$productId]);
                                $sellerData->setStatus(1);
                                $sellerData->save();
                                return 'enabled';
                            }
                        }
                    }
                }
            }
            return false;
        }
        return false;
    }
     /**
      * this is used to convert into ConfigTimezone form DefaultTimezone.
      *
      * @param string dateTime - time tobe converted.
      *
      * @param string timeZone inwhich you want to convert.
      *
      * @param string timeZone fromwhich.
      *
      * @return string datetime according to 2'nd Param.
      */
    public function converToTz($dateTime = "", $vacation = false)
    {
        $vacationMode = $this->getVacationMode($vacation);
        if ($vacationMode == 'product_disable') {
            $dateTime = date_format(date_create($dateTime), "Y-m-d");
        } else {
            $dateTime = date_format(date_create($dateTime), "Y-m-d H:i:s");
        }
        return $dateTime;
    }

    public function getProductInfo($itemCollection)
    {
        $productsInfo = [];
        foreach ($itemCollection as $item) {
            $productId = $item->getProductId();
            $shopUrl = $this->getSellerDataByProduct($productId);
            if ($shopUrl) {
                $sellerId = false;
                $data = $this->_objectManager->create('Webkul\Marketplace\Model\Seller')
                    ->getCollection()
                    ->addFieldToFilter('shop_url', ['eq' => $shopUrl]);
                foreach ($data as $seller) {
                    $sellerId = $seller->getSellerId();
                }
                if ($sellerId) {
                    $vacation = $this->getVacationdetails($sellerId);
                    if ($vacation) {
                        $status = $this->getVacationMode($vacation);
                        if ($status == 'add_to_cart_disable' && $this->checkDisableTime($vacation)) {
                            $storeCloseMsg = $this->getStoreCloseLabel();
                            $productsInfo[$productId] = $storeCloseMsg;
                        }
                    }
                }
            }
        }
        return $productsInfo;
    }
}

<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\Vendorcommission\Block\Adminhtml\Customer;


use Magento\Customer\Model\CustomerFactory;
use Webkul\Marketplace\Model\SellerFactory;
use Webkul\Marketplace\Model\SaleperpartnerFactory;
use Webkul\Marketplace\Model\SaleslistFactory;
use Webkul\Marketplace\Model\ProductFactory;

use Magento\Directory\Model\ResourceModel\Country\Collection as CountryCollection;

class Edit extends \Webkul\Marketplace\Block\Adminhtml\Customer\Edit
{
    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;
    /**
     * @var CountryCollection
     */
    protected $_country;
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $_currency;

    /**
     * @var CustomerFactory
     */
    protected $customerModel;

    /**
     * @var SellerFactory
     */
    protected $sellerModel;

    /**
     * @var SaleperpartnerFactory
     */
    protected $saleperpartner;

    /**
     * @var SaleslistFactory
     */
    protected $saleslistFactory;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @param \Magento\Backend\Block\Widget\Context     $context
     * @param \Magento\Framework\Registry               $registry
     * @param CountryCollection                         $country
     * @param \Magento\Directory\Model\Currency         $currency
     * @param \Webkul\Marketplace\Helper\Data           $helper
     * @param CustomerFactory                           $customerModel
     * @param SellerFactory                             $sellerModel
     * @param SaleperpartnerFactory                     $saleperpartner
     * @param SaleslistFactory                          $saleslistFactory
     * @param ProductFactory                            $productFactory
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        CountryCollection $country,
        \Magento\Directory\Model\Currency $currency,
        \Webkul\Marketplace\Helper\Data $helper,
        CustomerFactory $customerModel,
        SellerFactory $sellerModel,
        SaleperpartnerFactory $saleperpartner,
        SaleslistFactory $saleslistFactory,
        ProductFactory $productFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        $this->_helper = $helper;
        $this->_country = $country;
        $this->_currency = $currency;
        $this->customerModel = $customerModel;
        $this->sellerModel = $sellerModel;
        $this->saleperpartner = $saleperpartner;
        $this->saleslistFactory = $saleslistFactory;
        $this->productFactory = $productFactory;
        parent::__construct($context, $registry, $country, $currency, $helper, $customerModel, $sellerModel, $saleperpartner, $saleslistFactory, $productFactory, $data);
    }

    public function getObjectManager(){
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        return $objectManager;
    }
  
    public function getSellerInfoCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        $data = [];
        if ($customerId != '') {
            $collection = $this->getObjectManager()->create(
                'Webkul\Marketplace\Model\Seller'
            )->getCollection()
            ->addFieldToFilter('seller_id', $customerId)
            ->addFieldToFilter('store_id', $storeId);
            if (!count($collection)) {
                $collection = $this->getObjectManager()->create(
                    'Webkul\Marketplace\Model\Seller'
                )->getCollection()
                ->addFieldToFilter('seller_id', $customerId)
                ->addFieldToFilter('store_id', 0);
            }
            $user = $this->getObjectManager()->get(
                'Magento\Customer\Model\Customer'
            )->load($customerId);
            $name = explode(' ', $user->getName());
            foreach ($collection as $record) {
                $data = $record->getData();
                $bannerpic = $record->getBannerPic();
                $logopic = $record->getLogoPic();
                $countrylogopic = $record->getCountryPic();
                if (strlen($bannerpic) <= 0) {
                    $bannerpic = 'banner-image.png';
                }
                if (strlen($logopic) <= 0) {
                    $logopic = 'noimage.png';
                }
                if (strlen($countrylogopic) <= 0) {
                    $countrylogopic = '';
                }
            }
            $data['firstname'] = $name[0];
            $data['lastname'] = $name[1];
            $data['email'] = $user->getEmail();
            if (isset($bannerpic)) {
                $data['banner_pic'] = $bannerpic;
            }
            if (isset($logopic)) {
                $data['logo_pic'] = $logopic;
            }
            if (isset($countrylogopic)) {
                $data['country_pic'] = $countrylogopic;
            }

            return $data;
        }
    }
    /**
     * @return Mixed.
     */
    public function getCountryList()
    {
        return $this->_country->loadByStore()->toOptionArray(true);
    }
    public function getPaymentMode()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);
        $data = '';
        foreach ($collection as $record) {
            $data = $record->getPaymentSource();
        }

        return $data;
    }

    /**
     * @return Webkul\Marketplace\Model\Saleperpartner
     */
    public function getSalesPartnerCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Model\Saleperpartner'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }
    /**
     * @return Webkul\Marketplace\Model\Saleslist
     */
    public function getSalesListCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Model\Saleslist'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }
    /**
     * @return string
     */
    public function getConfigCommissionRate()
    {
        return $this->_helper->getConfigCommissionRate();
    }
    /**
     * @param Decimal $price
     *
     * @return [type] [description]
     */
    public function getCurrencySymbol()
    {
        $storeManager = $this->getObjectManager()->get('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->getObjectManager()->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();

        return $currencySymbol;
    }
    /**
     * @return Webkul\Marketplace\Model\Product
     */
    public function getProductCollection()
    {
        $customerId = $this->getRequest()->getParam('id');

        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('adminassign', 1);

        return $collection;
    }
    /**
     * @return Webkul\Marketplace\Model\Seller
     */
    public function getMarketplaceUserCollection()
    {
        $customerId = $this->getRequest()->getParam('id');
        $collection = $this->getObjectManager()->create(
            'Webkul\Marketplace\Model\Seller'
        )->getCollection()
        ->addFieldToFilter('seller_id', $customerId);

        return $collection;
    }

    public function getAllCustomerCollection()
    {
        $collection = $this->getObjectManager()->create(
            'Magento\Customer\Model\Customer'
        )->getCollection();

        return $collection;
    }
}

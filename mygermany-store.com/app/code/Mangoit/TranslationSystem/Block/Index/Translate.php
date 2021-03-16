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

namespace Mangoit\TranslationSystem\Block\Index;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderSeller\CollectionFactory as PreorderSellerCollection;

class Translate extends \Magento\Framework\View\Element\Template
{
	/**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    protected $_sellerProductCollectionFactory;
    protected $productFaq;
    protected $_storeManager;
    protected $_sellerCollectionFactory;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyModel;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct     $sellerProductCollectionFactory
     * @param array             $data
     */

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PreorderSellerCollection $preorderSellerCollectionFactory,
        \Magento\Directory\Model\Currency $currencyModel,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->scopeConfig = $scopeConfig;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->productFaq = $productFaq;
         $this->_storeManager = $storeManager;
         $this->_sellerCollectionFactory = $preorderSellerCollectionFactory;
         $this->currencyModel = $currencyModel;
        parent::__construct($context, $data);
    }

    public function getSellerProfileWords() {
        $mphelper = $this->marketplaceHelper;
        $isPartner = $mphelper->isSeller();
        $faqCollection = $this->productFaq->getCollection()->addFieldToFilter('vendor_id', $mphelper->getCustomerId());
        
        $partner = array();
        $wordcount = 0;
        if ($isPartner == 1) {
            $partner = $mphelper->getSeller();

            $contentArray = array();
            $contentArray['shop_title'] = $partner['shop_title'];
            $contentArray['company_locality'] = $partner['company_locality'];
            $contentArray['company_description'] = $partner['company_description'];
            $contentArray['meta_keyword'] = $partner['meta_keyword'];
            $contentArray['meta_description'] = $partner['meta_description'];
            $contentArray['return_policy'] = $partner['return_policy'];
            $contentArray['shipping_policy'] = $partner['shipping_policy'];

            $wordcount = str_word_count(strip_tags(implode(' ', $contentArray)));
        }
        return $wordcount;
    }

    public function getProductsContent($sellerid = 0){

        $sellerId = ($sellerid > 0) ? $sellerid : $this->marketplaceHelper->getCustomerId();

        $sellerProducts = $this->_sellerProductCollectionFactory
                ->create()
                ->addFieldToSelect('mageproduct_id')
                ->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )->getData();

        $productIds = array_column($sellerProducts, 'mageproduct_id');

        $mageProducts = $this->_productCollectionFactory
                ->create()
                ->addAttributeToSelect('*')
                ->addFieldToFilter(
                    'entity_id',
                    ['in' => $productIds]
                );
        $mphelper = $this->marketplaceHelper;
        $faqCollection = $this->productFaq->getCollection()->addFieldToFilter('vendor_id', $mphelper->getCustomerId());

        $productContentCounts = [];
        foreach ($mageProducts as $mageProduct) {
            $productContent['name'] = $mageProduct->getName();
            $productContent['meta_title'] = $mageProduct->getMetaTitle();
            $productContent['meta_description'] = $mageProduct->getMetaDescription();
            $productContent['description'] = $mageProduct->getDescription();
            $productContent['short_description'] = $mageProduct->getShortDescription();
            $productContent['meta_keyword'] = $mageProduct->getMetaKeyword();

            $wordcount = str_word_count(strip_tags(implode(' ', $productContent)));
            $productContentCounts[$mageProduct->getId()] = $wordcount;
        }

        return array_sum($productContentCounts);
    }

    public function getVendorFaqCount($sellerid = 0){

        $sellerId = ($sellerid > 0) ? $sellerid : $this->marketplaceHelper->getCustomerId();
        $storeManageObj = $this->_storeManager->getDefaultStoreView();
        $storeId = $storeManageObj->getStoreId();
        $faqCollection = $this->productFaq->getCollection()
        ->addFieldToFilter('vendor_id', $sellerId)
        ->addFieldToFilter('store_id', $storeId);

        $faqCount = [];

        foreach ($faqCollection as $faqData) {
            $FaqContent['title'] = $faqData->getTitle();
            $FaqContent['description'] = $faqData->getDescription();
            $wordcount = str_word_count(strip_tags(implode(' ', $FaqContent)));
            $faqCount[$faqData->getId()] = $wordcount;
        }

        return array_sum($faqCount);
    }

    public function getVendorPreorderMsgCount($sellerid = 0){

        $sellerId = ($sellerid > 0) ? $sellerid : $this->marketplaceHelper->getCustomerId();
        $storeManageObj = $this->_storeManager->getDefaultStoreView();
        $storeCode = $storeManageObj->getCode();
        $returnArr = $this->_sellerCollectionFactory->create()
            ->addFieldToFilter('seller_id', $sellerId)
            ->getFirstItem()
            ->getData();
        $wordcount = 0;
        if(isset($returnArr['custom_message'])) {
            $customMsgs = unserialize($returnArr['custom_message']);
            $wordcount = str_word_count(strip_tags(isset($customMsgs[$storeCode]) ? $customMsgs[$storeCode] : $customMsgs[0]));
        }

        return $wordcount;
    }

    public function isSectionVisible() {
    	return $this->marketplaceHelper->isSeller();
    }

    public function getPricePerWord() {
    	return $this->scopeConfig->getValue('translation/translationsettings/priceperword');
    }

    public function getFormatedCostPrice($price = 0)
    {
        $currencyCode = 'EUR';
        $currencySymbol = $this->currencyModel->load($currencyCode)->getCurrencySymbol();
        $precision = 2;   // for displaying price decimals 2 point
        //get formatted price by currency
        $formattedPrice = $this->currencyModel->format($price, ['symbol' => $currencySymbol, 'precision'=> $precision], false, false);
        return $formattedPrice;
    }
}

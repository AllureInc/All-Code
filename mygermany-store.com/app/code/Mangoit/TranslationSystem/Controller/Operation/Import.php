<?php
/**
 * A Magento 2 module named Mangoit/TranslationSystem
 * Copyright (C) 2018 Mango IT Solutions
 * 
 * This file is part of Mangoit/TranslationSystem.
 * 
 * Mangoit/TranslationSystem is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 * 
 */

namespace Mangoit\TranslationSystem\Controller\Operation;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Webkul\MarketplacePreorder\Api\PreorderSellerRepositoryInterface;

class Import extends \Magento\Framework\App\Action\Action
{

    protected $resultPageFactory;
    protected $_url;
    protected $_session;
    protected $translationHelper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $mageProductModel;

    protected $productFaq;

    protected $date;
    protected $_storeManager;
    protected $_misProductFaq;

    protected $_customerRepositoryInterface;
    protected $_sellerSearchInterface;
    protected $_sellerRepository;


    /**
     * Constructor
     *
     * @param \Magento\Framework\App\Action\Context  $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Customer\Model\Session $session,
        \Magento\Customer\Model\Url $url,
        \Mangoit\TranslationSystem\Helper\Data $translationHelper,
        \Magento\Catalog\Model\Product $mageProductModel,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Mangoit\Productfaq\Model\Misproductfaq $misProductFaq,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        PreorderSellerRepositoryInterface $sellerRepository,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_session = $session;
        $this->_url = $url;
        $this->translationHelper = $translationHelper;
        $this->mageProductModel = $mageProductModel;
        $this->productFaq = $productFaq;
        $this->date = $date;
        $this->_storeManager = $storeManager;
        $this->_misProductFaq = $misProductFaq;
        $this->_sellerSearchInterface = $searchCriteriaBuilder;
        $this->_sellerRepository = $sellerRepository;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        parent::__construct($context);
    }

    /**
     * Check customer authentication.
     *
     * @param RequestInterface $request
     *
     * @return \Magento\Framework\App\ResponseInterface
     */
    public function dispatch(RequestInterface $request)
    {
        $loginUrl = $this->_url->getLoginUrl();
        if (!$this->_session->authenticate($loginUrl)) {
            $this->_actionFlag->set('', self::FLAG_NO_DISPATCH, true);
        }
        return parent::dispatch($request);
    }

    /**
     * Execute view action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        if (!$this->marketplaceHelper->isSeller()) {
            $this->messageManager->addError(__('You entered wrong URL.'));
            return $this->resultRedirectFactory->create()->setPath('customer/account/index');
        }

        $validateData = $this->translationHelper->validateUploadedFiles();
        $filteredkey = array();

        if(!isset($validateData['csv_data'])){
            $this->messageManager->addError(__('Invalid CSV file.'));

            return $this->resultRedirectFactory->create()->setPath(
                '*/index/translate',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }

        if(isset($validateData['csv_data'][0])){
            $sysKeys = $validateData['csv_data'][0];

            $filteredkey = array_map(
                function ($str) {
                    return preg_replace("/[^A-Za-z0-9\-]/", "", $str);
                }, $sysKeys);
        }
        // print_r(array_map(function ($str) {return preg_replace("/[^A-Za-z0-9\-]/", "", $str);},$validateData['csv_data'][0]));
        try {
            $systemData = [];
            foreach ($validateData['csv_data'] as $csvRow) {
                $csvRow = array_combine($filteredkey, $csvRow);
                $systemKeys = [];
                foreach ($csvRow as $rowKey => $rowValue) {
                    if($rowKey == 'UniqueIdentifier') {
                        $systemKeys = explode('-', $rowValue);
                    }
                    if($rowKey != 'UniqueIdentifier' && $rowKey != 'Germanygerman'){
                        if($rowValue != ''){
                            if(isset($systemKeys[0]) && $systemKeys[0] == 'shop') {
                                if(isset($systemKeys[1]) && is_numeric((int)$systemKeys[1])) {
                                    $systemData[$rowKey]['shop'][$systemKeys[1]] = $rowValue;
                                }
                            } elseif (isset($systemKeys[0]) && $systemKeys[0] == 'product') {
                                if(isset($systemKeys[1]) && is_numeric((int)$systemKeys[1])) {
                                    if(isset($systemKeys[2]) && $systemKeys[2] != '') {
                                        $systemData[$rowKey]['product'][$systemKeys[1]][$systemKeys[2]] = $rowValue;
                                    }
                                }
                            } elseif (isset($systemKeys[0]) && $systemKeys[0] == 'faqs') {
                                if(isset($systemKeys[1]) && is_numeric((int)$systemKeys[1])) {
                                    if(isset($systemKeys[2]) && $systemKeys[2] != '') {
                                        $systemData[$rowKey]['faqs'][$systemKeys[1]][$systemKeys[2]] = $rowValue;
                                    }
                                }
                            }
                        }
                    }
                }
            }

            $sellerId = $this->marketplaceHelper->getCustomerId();
            $storeIdsArr = $this->translationHelper->getStoreIds();
            $storeCodesArr = $this->translationHelper->getStoreCodes();

            $searchCriteria = $this->_sellerSearchInterface->addFilter(
                'seller_id',
                $sellerId,
                'eq'
            )->create();
            $items = $this->_sellerRepository->getList($searchCriteria);

            $entityId = 0;
            foreach ($items->getItems() as $value) {
                $entityId = $value['id'];
                break;
            }
            $customMessages = [];
            if ($entityId) {
                $savedCustomMsgs = $this->_sellerRepository->getById($entityId)->getCustomMessage();
                if($savedCustomMsgs) {
                    $customMessages = unserialize($savedCustomMsgs);
                }
            }
            
            $sellerProductsData = $this->marketplaceHelper->getSellerProductData()->getData();
            $sellerProductIds = array_column($sellerProductsData, 'mageproduct_id');
            foreach ($systemData as $storeCode => $storeData) {
                $storeId = $storeIdsArr[$storeCode];
                foreach ($storeData as $dataTyp => $dataValues) {
                    if($dataTyp == 'shop') {

                        /*
                         * If the imported CSV file contains data for custom msg then add it to
                         * customMessages array and unset from dataValues to save shop values.
                         */
                        if(isset($dataValues['preorder_msg'])) {
                            $stCd = $storeCodesArr[$storeCode];
                            $customMessages[$stCd] = $dataValues['preorder_msg'];
                            unset($dataValues['preorder_msg']);
                        }

                        $model = $this->_objectManager->create(
                            'Webkul\Marketplace\Model\Seller'
                        )->getCollection()
                        ->addFieldToFilter('seller_id', $sellerId)
                        ->addFieldToFilter('store_id', $storeId);
                        if (count($model)) {
                            $sellerData = $model->getFirstItem();
                            foreach ($dataValues as $key => $value) {
                                $sellerData->setData($key, $value);
                            }
                            $sellerData->save();
                        }
                    } elseif ($dataTyp == 'product') {
                        $productsToUpdate = array_keys($dataValues);
                        $collection = $this->mageProductModel->getCollection();
                        $collection->addAttributeToSelect('entity_id');
                        $collection->addAttributeToFilter('entity_id', ['in' => $productsToUpdate]);
                        // $collection->addAttributeToFilter('visibility', ['in' => [4]]);
                        // $collection->addAttributeToFilter('status', ['neq' => SellerProduct::STATUS_DISABLED]);
                        // $collection->addStoreFilter($storeId);

                        $collection->setPageSize(10);

                        $pages = $collection->getLastPageNumber();
                        $currentPage = 1;
                     
                        do {
                            $collection->setCurPage($currentPage);
                            $collection->load();
                            foreach ($collection as $product) {
                                $product->setStoreId($storeId)->load($product->getId());
                                $productId = $product->getId();
                                if(isset($dataValues[$productId])){
                                    foreach ($dataValues[$productId] as $key => $value) {
                                        $product->setData($key, $value);
                                    }
                                    $product->save();
                                }
                            }
                     
                            $currentPage++;
                            $collection->clear();
                        } while ($currentPage <= $pages);
                    } elseif ($dataTyp == 'faqs') {
                        $faqsToUpdate = array_keys($dataValues);
                        $sellerDetails = $this->marketplaceHelper->getSellerData();
                        $shoptitle = $sellerDetails->getFirstItem()->getShopTitle();
                        $date = $this->date->gmtDate();
                        $customerEmail = $this->_session->getCustomer()->getEmail();
                        foreach ($dataValues as $faqKey => $faqVal) {
                            $misFaqObj = $this->_misProductFaq->load($faqKey,'default_faq_id');
                            if ($misFaqObj->getId()) {
                                $unSerializedFaqIds = unserialize($misFaqObj->getStorewiseFaqIds());
                                $toLoadFaqId = $unSerializedFaqIds[$storeId];
                                $faqObj = $this->productFaq->load($toLoadFaqId);
                                $productData = $this->mageProductModel->load($faqObj->getProductId());
                                if (!empty($productData->debug()) && (isset($faqVal['title'])) && (isset($faqVal['description']))) {
                                    $faqObj->setProductId($faqObj->getProductId());//product Id
                                    $faqObj->setTitle($faqVal['title']);
                                    $faqObj->setDescription($faqVal['description']);
                                    $faqObj->setPublishDate($date);
                                    $faqObj->setIsActive(0);
                                    $faqObj->setPostedBy($shoptitle);
                                    $faqObj->setEmailId($customerEmail);
                                    $faqObj->setVendorId($sellerId);
                                    $faqObj->setPublishDate($date);
                                    $faqObj->setUpdatedAt($date);
                                    $faqObj->setAdminNotification(1);
                                    $faqObj->setStoreId($storeId);
                                    $faqObj->save();
                                    
                                }
                            }
                        }
                    }
                }
            }

            if ($entityId) {
                $preOrderModel = $this->_sellerRepository->getById($entityId);
                $preOrderModel->setCustomMessage(serialize($customMessages));

                $this->_sellerRepository->save($preOrderModel);
            }

            $sellerCustomerRecord = $this->_customerRepositoryInterface->getById($sellerId);
            $sellerCustomerRecord->setCustomAttribute('is_translated',1); 
            $this->_customerRepositoryInterface->save($sellerCustomerRecord);
            $faqTranslated = $this->productFaq->getCollection('vendor_id',$sellerId);
            foreach ($faqTranslated as $faqTValue) {
                $faqTValue->setIsTranslated(1);
                $faqTValue->save();
            }
            $this->messageManager->addSuccess(__('Translation data has been saved.'));
            return $this->resultRedirectFactory->create()->setPath(
                '*/index/translate',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } catch (\Exception $e) {
            $this->messageManager->addError($e->getMessage());

            return $this->resultRedirectFactory->create()->setPath(
                '*/index/translate',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        if ($validateData['error']) {
            $this->messageManager->addError(__($validateData['msg']));
            return $this->resultRedirectFactory->create()->setPath('*/*/view');
        }
        return $this->resultPageFactory->create();
    }
}
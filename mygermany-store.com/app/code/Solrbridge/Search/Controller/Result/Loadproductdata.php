<?php
/**
 *
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Controller\Result;

use Magento\Framework\App\Action\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Search\Model\QueryFactory;

class Loadproductdata extends \Solrbridge\Search\Controller\Result
{
    /**
     * @var \Magento\Catalog\Helper\Product\View
     */
    protected $helper;

    /**
     * @var \Magento\Framework\Controller\Result\ForwardFactory
     */
    protected $productRepository;
    
    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;
    
    /**
     * @var QueryFactory
     */
    private $_queryFactory;
    
    /**
     * @var \Magento\Framework\App\CacheInterface
     */
    protected $cache;

    /**
     * Constructor
     *
     * @param Context $context
     * @param \Magento\Catalog\Helper\Product\View $viewHelper
     * @param \Magento\Framework\Controller\Result\ForwardFactory $resultForwardFactory
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        \Solrbridge\Search\Helper\Data $viewHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        StoreManagerInterface $storeManager,
        QueryFactory $queryFactory,
        \Magento\Framework\App\CacheInterface $cache
    ) {
        parent::__construct($context);
        $this->helper = $viewHelper;
        $this->productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_queryFactory = $queryFactory;
        $this->cache = $cache;
    }
    
    protected function getProductThumb($product)
    {
        $thumbSize = $this->helper->getAutocompleteThumbSize();
        $imagewidth = $thumbSize['w'];
        $imageheight = $thumbSize['h'];
        $image_url = $this->_objectManager->get('\Magento\Catalog\Helper\Image')->init($product, 'product_page_image_small')->setImageFile($product->getFile())->resize($imagewidth, $imageheight)->getUrl();
        return $image_url;
    }
    
    protected function updateSearchTerm($queryText)
    {
        if ($queryText) {
            $queryParamName = $this->helper->getQueryParamName();
            $this->getRequest()->setParam($queryParamName, $queryText);
            
            /* @var $query \Magento\Search\Model\Query */
            $query = $this->_queryFactory->get();

            $storeId = $this->_storeManager->getStore()->getId();
            $query->setStoreId($storeId);

            $queryText = $query->getQueryText();
            
            if ($this->helper->isMinQueryLength()) {
                $query->setId(0)->setIsActive(1)->setIsProcessed(1);
            } else {
                $query->saveIncrementalPopularity();
            }
        }
    }

    /**
     * Product view action
     *
     * @return \Magento\Framework\Controller\Result\Forward|\Magento\Framework\Controller\Result\Redirect
     */
    public function execute()
    {
        $productIds = $this->getRequest()->getParam('productids');
        $queryText = $this->getRequest()->getParam('query_text', null);
        
        $cacheKey = 'solrbridge_search_autocomplete_';
        $cacheKey .= sha1(@implode('_', $productIds).$queryText);
        $cacheKey .= $this->_storeManager->getStore()->getId();
        
        $response = $this->cache->load($cacheKey);
        if ($response) {
            echo $response;
            exit();
        }
        
        $this->updateSearchTerm($queryText);
        $responseData = array();
        if (!empty($productIds)) {
            $priceHelper = $this->_objectManager->get('Magento\Framework\Pricing\Helper\Data');
            $productIds = explode(',', $productIds);
            foreach ($productIds as $productId) {
                $product = $this->productRepository->getById($productId);
                /*@TOBEDELETE-if ($product->getTypeId() == 'configurable') {
                    //echo $product->getPriceInfo()->getPrice('lowest_price');
                    /*
                    foreach ($product->getPriceInfo()->getPrices() as $priceObject) {
                        echo get_class($priceObject);
                    }*
                }*/
                
                $price = floatval($product->getPrice());
                $finalPrice = floatval($product->getFinalPrice());
                $minimalPrice = floatval($product->getMinimalPrice());
                //Has special price
                $showSpecialPrice = 0;
                if ($price > 0 && $finalPrice < $price) {
                    if ($product->getPriceInfo()->getPrice('special_price')->isScopeDateInInterval()) {
                        $showSpecialPrice = 1;
                    }
                } else {
                    //logic here
                }
                $responseData[$productId] = array(
                    'price' => $priceHelper->currency($price, true, false),
                    'minimal_price' => $priceHelper->currency($minimalPrice, true, false),
                    'final_price' => $priceHelper->currency($finalPrice, true, false),
                    'thumb' => $this->getProductThumb($product),
                    'show_special_price' => $showSpecialPrice
                );
            }
        }
        $response = json_encode($responseData, true);
        $this->cache->save($response, $cacheKey, ['solrbridge_search_query'], 3600);
        echo $response;
        exit();
    }
}

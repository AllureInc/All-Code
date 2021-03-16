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

class ProductOnRakuten extends \Magento\Framework\App\Helper\AbstractHelper
{
    public $rktnClient;

    /*
    \Magento\Catalog\Model\ProductFactory
     */
    private $productFactory;

    /*
    Data
     */
    private $helper;

    /*
    \Mangoit\RakutenConnector\Model\ProductMap
     */
    private $productMap;


    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        Data $helper,
        \Mangoit\RakutenConnector\Model\ProductMap $productMap,
        \Mangoit\RakutenConnector\Logger\Logger $logger,
        ProductMapRepositoryInterface $productMapRepo,
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        parent::__construct($context);
        $this->productFactory = $productFactory;
        $this->storeManager = $storeManager;
        $this->helper = $helper;
        $this->productMap = $productMap;
        $this->logger = $logger;
        $this->productMapRepo = $productMapRepo;
        $this->objectManager = $objectManager;
    }

    /**
     * mange magento product to sync to rakuten
     * @param  array $params
     * @return array
     */
    public function manageMageProduct($productIds)
    {
        $result = null;
        $postProductData = [];
        $exportErrorsData = [];
        $totalCount = count($productIds);
        $errorCount = 0;
        // $amzCurrencyCode = $this->helper->config['currency_code'];
        // $currencyRate = $this->helper->getCurrencyRate($amzCurrencyCode);

        foreach ($productIds as $productId) {
            $product = $this->productFactory->create()->load($productId);

            if ($product->getEntityId()) {
                // $exportProType = $this->helper
                //     ->getProductAttrValue($product, 'identification_label');
                // $exportProValue = $this->helper
                //     ->getProductAttrValue($product, 'identification_value');
                $mwsProduct = $this->objectManager->create('Mangoit\RakutenConnector\Helper\MwsProduct');
                $mwsProduct->mageProductId = $product->getId();
                $mwsProduct->sku = $product->getSku();
                $mwsProduct->name = $product->getName();
                $actualPrice = $product->getPrice();
                // $actualPrice = empty($currencyRate) ? $product->getPrice() : ($product->getPrice()*$currencyRate);
                $newPrice = str_replace(',', '', number_format($actualPrice, 2));
                $mwsProduct->price = $newPrice;
                $mwsProduct->description = $product->getDescription();
                $productImgs = [];
                foreach ($product->getMediaGalleryImages() as $image) {
                    $prodImg = [];
                    // print_r($image->getData());
                    if($image->getMediaType() == 'image') {
                        $prodImg['url'] = $image->getUrl();
                        $prodImg['default'] = ($product->getImage() == $image->getFile()) ? 1 : 0;
                        $productImgs[] = $prodImg;
                    }
                }
                $mwsProduct->imagesData = $productImgs;
                // $mwsProduct->productId = $exportProValue;
                // $mwsProduct->productIdType = $exportProType;
                // $mwsProduct->conditionType = 'New';
                $mwsProduct->quantity = $product->getQuantityAndStockStatus()['qty'];
                // $mwsProduct->mageProductObj = $product;

                if ($mwsProduct->validate()) {
                    $postProductData[] = $mwsProduct;
                } else {
                    $exportErrorsData[] = $mwsProduct->getValidationErrors();
                    $errorCount++;
                }
            } else {
                $errorCount++;
            }
        }
        $exportedProducts = $totalCount - $errorCount;

        if (!empty($postProductData)) {
            // $result = $this->rktnClient->postProduct($postProductData);
            $result = $this->rktnClient->postProductOnRkWS($postProductData);
            $this->saveDataInTable($postProductData, $result);
        }

        empty($errorCount) ? '' : $this->logger->info('Helper ProductOnRakuten manageMageProduct : error log '.json_encode($exportErrorsData));
        
        return ['count' => $exportedProducts, 'error_count'=>$errorCount];
    }

    /**
     * save exported data in table
     *
     * @param object $submitedData
     * @param array $mwsResponse
     * @return void
     */
    public function saveDataInTable($submitedData, $mwsResponse)
    {
        if (isset($mwsResponse)) {
            foreach ($submitedData as $subProduct) {
                $product = $this->productFactory->create()
                            ->load($subProduct->mageProductId);
                $cats = $product->getCategoryIds();
                $firstCategoryId = null;
                if (count($cats)) {
                    $firstCategoryId = $cats[0];
                }

                $resultData = isset($mwsResponse[$subProduct->mageProductId]) ? $mwsResponse[$subProduct->mageProductId] : [];
                $productRes = isset($resultData['product']) ? $resultData['product'] : [];
                $data = [
                    'magento_pro_id' => $product->getEntityId(),
                    'mage_cat_id' => $firstCategoryId,
                    'name' => $product->getName(),
                    'product_type' => $product->getTypeId(),
                    'rakuten_pro_id' => isset($productRes['product_id']) ? $productRes['product_id'] : '',
                    'seller_id' => $this->helper->getCustomerId(),
                    'rkt_product_id' => $subProduct->mageProductId,
                    // 'feedsubmission_id' => $mwsResponse['FeedSubmissionId'],
                    'export_status' =>'0',
                    'error_status' =>'0',
                    'pro_status_at_rkt' =>'Pending',
                    'product_sku' => $subProduct->sku
                ];
                $record = $this->productMap;
                $record->setData($data)->save();
            }
        }
    }

    /**
     * get product quantity related data
     * @param  object $product
     * @return array
     */
    public function updateQtyData($product)
    {
        $updateQuantityArray = [
            $product->getSku() => $product->getQuantityAndStockStatus()['qty']
        ];
        return $updateQuantityArray;
    }

    /**
     * get product price related data
     * @param  object $product
     * @return array
     */
    public function updatePriceData($product)
    {
        // $amzCurrencyCode = $this->helper->config['currency_code'];
        $amzCurrencyCode = 'EUR';
        $currencyRate = $this->helper->getCurrencyRate($amzCurrencyCode);
        $price = empty($currencyRate) ? round($product->getPrice(), 2) : round(($product->getPrice()*$currencyRate), 2);
        $updatePriceArray = [
            $product->getSku() => $price
        ];
        return $updatePriceArray;
    }

    /**
     * check exported product status
     *
     * @param array $feedIds
     * @return void
     */
    public function checkProductFeedStatus($feedIds)
    {
        $productFeedStatus = [];
        $result = $this->feedSubmitionResult($feedIds);
    }

    /**
     * proccessed exported product response
     *
     * @param [type] $feed
     * @param [type] $feedResponse
     * @return void
     */
    public function processFeedResult($feed, $feedResponse)
    {
        try {
            $this->logger->info('feedresponse '.json_encode($feedResponse));
            $response = [];
            $updatedRecods = 0;
            $failedErrorCodes = ['8058','8560','8047','8105','6024'];
            foreach ($feedResponse as $feedArray) {
                $errorCode = '';
                $errorMsg = '';
                $productAsign = null;
                $productStatus = null;
                $mapProductData = $this->productMapRepo->getBySku($feedArray['product_sku']);
                if ($mapProductData->getSize()) {
                    if (in_array($feedArray['error_code'], $failedErrorCodes)) {
                        $productStatus = 'Failed';//failed
                        $errorMsg = $feedArray['error_msg']. '(error code '.$feedArray['error_code']. ')';
                    } else {
                        $amzProData = $this->rktnClient->getMyPriceForSKU([$feedArray['product_sku']]);
                        if (isset($amzProData['GetMyPriceForSKUResult']['Product'])) {
                            $productStatus = 'Exported';//active
                            $amzProductForSku = $amzProData['GetMyPriceForSKUResult']['Product'];
                            $productAsign = $amzProductForSku['Product']['Identifiers']['MarketplaceASIN']['ASIN'];
                        } else {
                            $productStatus = 'Exported';//inactive
                        }
                    }
                    foreach ($mapProductData as $proData) {
                        $proData->setExportStatus('1');
                        $proData->setErrorStatus($errorMsg);
                        $proData->setProStatusAtRkt($productStatus);
                        $proData->setRakutenProId($productAsign);
                        $proData->save();
                    }
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProductOnRakuten processFeedResult : '.$e->getMessage());
        }
    }

    /**
     * send feed submit request
     */
    public function feedSubmitionResult($feedIds)
    {
        try {
            $feedResponse = [];
            foreach ($feedIds as $key => $feed) {
                $feedResult = $this->rktnClient->getFeedSubmissionResult($feed);
                $feedResponse = $this->convertTxtToArray($feedResult);
                $this->processFeedResult($feed, $feedResponse);
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProductOnRakuten feedSubmitionResult : '.$e->getMessage());
        }
        return $feedResponse;
    }

    /**
     * convert text to array
     *
     * @param string $content
     * @return array
     */
    public function convertTxtToArray($content)
    {
        try {
            $reportContent = str_replace([ "\n" , "\t" ], [ "[NEW*LINE]" , "[tAbul*Ator]" ], $content);
            $reportArr = explode("[NEW*LINE]", $reportContent);
            $i = 4;
            $exportErrors = [];
            // $reportHeadingArr = explode("[tAbul*Ator]", utf8_encode($reportArr[4]));
            for ($i =5; $i < count($reportArr); $i++) {
                $errorReport = explode("[tAbul*Ator]", utf8_encode($reportArr[$i]));
                if (isset($errorReport[1])) {
                    $exportErrors[] = [
                        'product_sku' => $errorReport[1],
                        'error_code' => $errorReport[2],
                        'error_msg' => $errorReport[4]
                    ];
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProductOnRakuten convertTxtToArray : '.$e->getMessage());
        }
        return $exportErrors;
    }

    /**
     * check product status by sku
     *
     * @param array $amazProSku
     * @return void
     */
    public function checkProductStatusBySku($amazProSku)
    {
        try {
            $exportedAmzIds = [];
            $response = $this->rktnClient->getCompetitivePricingForSKU($amazProSku);
            if (isset($response['GetCompetitivePricingForSKUResult'])) {
                foreach ($response['GetCompetitivePricingForSKUResult'] as $result) {
                    if (isset($result['Product'])) {
                        $asinData = $result['Product']['Identifiers']['MarketplaceASIN'];
                        $skuData = $result['Product']['Identifiers']['SKUIdentifier'];
                        $exportedAmzIds[$skuData['SellerSKU']] = $asinData['ASIN'];
                    }
                }
            }
            foreach ($exportedAmzIds as $sku => $asin) {
                $mapProductData = $this->productMapRepo->getBySku($sku);
                foreach ($mapProductData as $proData) {
                    $proData->setExportStatus('1');
                    $proData->setProStatusAtRkt('Exported');
                    $proData->setRakutenProId($asin);
                    $proData->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProductOnRakuten checkProductStatusBySku : '.$e->getMessage());
        }
    }

    public function updateProductsOnRktnByMageId($mageIds)
    {
        $result = null;
        $postProductData = [];
        $exportErrorsData = [];
        // $totalCount = count($productIds);
        $errorCount = 0;
        try {
            $exportedAmzIds = [];
            // $response = $this->rktnClient->updateProductDetailsOnRktn($rktnProSku);
            // if (isset($response['GetCompetitivePricingForSKUResult'])) {
            //     foreach ($response['GetCompetitivePricingForSKUResult'] as $result) {
            //         if (isset($result['Product'])) {
            //             $asinData = $result['Product']['Identifiers']['MarketplaceASIN'];
            //             $skuData = $result['Product']['Identifiers']['SKUIdentifier'];
            //             $exportedAmzIds[$skuData['SellerSKU']] = $asinData['ASIN'];
            //         }
            //     }
            // }
            foreach ($mageIds as $rktnId => $productId) {
                $product = $this->productFactory->create()->load($productId);

                if ($product->getEntityId()) {
                    // $exportProType = $this->helper
                    //     ->getProductAttrValue($product, 'identification_label');
                    // $exportProValue = $this->helper
                    //     ->getProductAttrValue($product, 'identification_value');
                    $mwsProduct = $this->objectManager->create('Mangoit\RakutenConnector\Helper\MwsProduct');
                    $mwsProduct->mageProductId = $product->getId();
                    $mwsProduct->sku = $product->getSku();
                    $mwsProduct->name = $product->getName();
                    $mwsProduct->rktnProdId = $rktnId;
                    $actualPrice = $product->getPrice();
                    // $actualPrice = empty($currencyRate) ? $product->getPrice() : ($product->getPrice()*$currencyRate);
                    $newPrice = str_replace(',', '', number_format($actualPrice, 2));
                    $mwsProduct->price = $newPrice;
                    $mwsProduct->description = $product->getDescription();
                    $productImgs = [];
                    foreach ($product->getMediaGalleryImages() as $image) {
                        $prodImg = [];
                        // print_r($image->getData());
                        if($image->getMediaType() == 'image') {
                            $prodImg['url'] = $image->getUrl();
                            $prodImg['default'] = ($product->getImage() == $image->getFile()) ? 1 : 0;
                            $productImgs[] = $prodImg;
                        }
                    }
                    $mwsProduct->imagesData = $productImgs;
                    // $mwsProduct->productId = $exportProValue;
                    // $mwsProduct->productIdType = $exportProType;
                    // $mwsProduct->conditionType = 'New';
                    $mwsProduct->quantity = $product->getQuantityAndStockStatus()['qty'];
                    // $mwsProduct->mageProductObj = $product;

                    if ($mwsProduct->validate()) {
                        $postProductData[] = $mwsProduct;
                    } else {
                        $exportErrorsData[] = $mwsProduct->getValidationErrors();
                        $errorCount++;
                    }
                } else {
                    $errorCount++;
                }
            }

            foreach ($postProductData as $productData) {
                $response = $this->rktnClient->updateProductDetailsOnRktn($productData);

                $mapProductData = $this->productMapRepo->getBySku($productData->sku);
                foreach ($mapProductData as $proData) {
                    $proData->setExportStatus('1');
                    $proData->setProStatusAtRkt('Exported');
                    $proData->setRakutenProId($productData->rktnProdId);
                    $proData->save();
                }
                // print_r($response);
            }
            // foreach ($exportedAmzIds as $sku => $asin) {
            //     $mapProductData = $this->productMapRepo->getBySku($sku);
            //     foreach ($mapProductData as $proData) {
            //         $proData->setExportStatus('1');
            //         $proData->setProStatusAtRkt('Exported');
            //         $proData->setRakutenProId($asin);
            //         $proData->save();
            //     }
            // }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProductOnRakuten updateProductsOnRktnBySku : '.$e->getMessage());
        }
    }
}

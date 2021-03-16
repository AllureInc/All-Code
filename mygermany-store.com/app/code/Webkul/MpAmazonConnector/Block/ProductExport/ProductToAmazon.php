<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\ProductExport;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductToAmazon extends \Magento\Framework\View\Element\Template
{
    /**
     * Webkul\MpAmazonConnector\Model\ResourceModel\Productmap\CollectionFactory
     * @var $_mapedProcollection
     */
    protected $mappedProduct;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface $mappedProductRepository,
        ProductRepositoryInterface $productRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        CollectionFactory $productCollectionFactory,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Webkul\Marketplace\Model\Product $mpProduct,
        array $data = []
    ) {
        $this->mappedProductRepository = $mappedProductRepository;
        $this->customerSession = $customerSession;
        $this->imageHelper = $context->getImageHelper();
        $this->productRepository = $productRepository;
        $this->helper = $helper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_productVisibility = $productVisibility;
        $this->mpProduct = $mpProduct;
        parent::__construct($context, $data);
    }

    /**
     * getMappedProducts
     * @return array
     */
    public function getMappedProducts()
    {
        if (!($sellerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->mappedProduct) {
            $mappedProId = $this->helper->getSellerProductIds($sellerId);

            $this->mappedProduct = $this->_productCollectionFactory
                                ->create()
                                ->addAttributeToSelect('*')
                                ->addFieldToFilter(
                                    'type_id',
                                    ['in' => ['simple']]
                                );

            $this->mappedProduct->addFieldToFilter(
                'entity_id',
                ['in'=>$mappedProId]
            );
            $this->mappedProduct->setVisibility($this->_productVisibility->getVisibleInSiteIds());
            $filter = $this->getRequest()->getParams();
            if (isset($filter['s'])) {
                $this->mappedProduct = $this->mappedProduct->addFieldToFilter('name', ['like'=>'%'.$filter['s'].'%']);
            }
        }
        return $this->mappedProduct;
    }

    /**
     * getProfilerUrl
     * @return string
     */
    public function getProfilerUrl()
    {
        return $this->getUrl('mpamazonconnect/producttoamazon/profiler', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * getProductIds
     * @return string
     */
    public function getProductIds()
    {
        $params = $this->getRequest()->getParams();
        return isset($params['amz_mass_export'])? $params['amz_mass_export']:'';
    }

    /**
     * getProductDetail
     * @param int $productId which detail want
     * @return false| Magento\Catalog\Model\Product
     */
    public function getProductDetail($productId)
    {
        if ($productId) {
            return $this->productRepository->getById($productId);
        }
        return false;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getMappedProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ebayconnect.product.list.pager'
            )->setCollection(
                $this->getMappedProducts()
            );
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * getProImgUrl
     * @param Magento\Catalog\Model\Product $product
     * @return string
     */
    public function getProImgUrl($products)
    {
        return $this->imageHelper->init($products, 'product_page_image_small')
                                    ->setImageFile($products->getFile())
                                    ->getUrl();
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
            $response = $this->amzClient->getCompetitivePricingForSKU($amazProSku);
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
                    $proData->setProStatusAtAmz('1');
                    $proData->setAmazonProId($asin);
                    $proData->save();
                }
            }
        } catch (\Exception $e) {
            $this->logger->info('Helper ProdudtOnAmazon checkProductStatusBySku : '.$e->getMessage());
        }
    }
}

<?php
namespace Cnnb\GoogleReviewSnippet\Plugin;

use Exception;
use Magento\Catalog\Model\ProductFactory;
use Magento\CatalogInventory\Api\Data\StockItemInterface;
use Magento\CatalogInventory\Api\StockRegistryInterface;
use Magento\CatalogInventory\Model\Stock\StockItemRepository;
use Magento\Framework\App\Request\Http;
use Magento\Framework\DataObject;
use Magento\Framework\Event\Manager;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Module\Manager as ModuleManager;
use Magento\Framework\Pricing\Helper\Data as PriceHelper;
use Magento\Framework\Registry;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Page\Config as PageConfig;
use Magento\Framework\View\Page\Config\Renderer;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollection;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Magento\Search\Helper\Data as SearchHelper;
use Magento\Store\Model\StoreManagerInterface;
use Cnnb\GoogleReviewSnippet\Helper\Data as HelperData;
use Cnnb\GoogleReviewSnippet\Logger\Logger;

/**
 * Class SeoRender
 * @package Cnnb\GoogleReviewSnippet\Plugin
 */
class SeoRender
{
    const GOOLE_SITE_VERIFICATION = 'google-site-verification';
    const MSVALIDATE_01           = 'msvalidate.01';
    const P_DOMAIN_VERIFY         = 'p:domain_verify';
    const YANDEX_VERIFICATION     = 'yandex-verification';

    /**
     * @var PageConfig
     */
    protected $pageConfig;

    /**
     * @var Http
     */
    protected $request;

    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var StockItemRepository
     */
    protected $stockItemRepository;

    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var ReviewFactory
     */
    protected $reviewFactory;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var ProductFactory
     */
    protected $productFactory;

    /**
     * @var ManagerInterface
     */
    protected $messageManager; 

    /**
     * @var StockRegistryInterface
     */
    protected $stockState;

    /**
     * @var SearchHelper
     */
    protected $_searchHelper;

    /**
     * @var PriceHelper
     */
    protected $_priceHelper;

    /**
     * @var Manager
     */
    protected $_eventManager;

    /**
     * @var DateTime
     */
    protected $_dateTime; 

    /**
     * @var TimezoneInterface
     */
    protected $_timeZoneInterface;

    /**
     * @var ReviewCollection
     */
    protected $_reviewCollection;

    /**
     * @var ModuleManager
     */
    protected $_moduleManager;

    protected $logger;

    /**
     * SeoRender constructor.
     *
     * @param PageConfig $pageConfig
     * @param Http $request
     * @param HelperData $helpData
     * @param StockItemRepository $stockItemRepository
     * @param Registry $registry
     * @param ReviewFactory $reviewFactory
     * @param StoreManagerInterface $storeManager
     * @param UrlInterface $urlBuilder
     * @param ProductFactory $productFactory
     * @param ManagerInterface $messageManager
     * @param StockRegistryInterface $stockState
     * @param SearchHelper $searchHelper
     * @param PriceHelper $priceHelper
     * @param Manager $eventManager
     * @param DateTime $dateTime
     * @param TimezoneInterface $timeZoneInterface
     * @param ReviewCollection $reviewCollection
     * @param ModuleManager $moduleManager
     */
    function __construct(
        PageConfig $pageConfig,
        Http $request,
        HelperData $helpData,
        StockItemRepository $stockItemRepository,
        Registry $registry,
        ReviewFactory $reviewFactory,
        StoreManagerInterface $storeManager,
        UrlInterface $urlBuilder,
        ProductFactory $productFactory,
        ManagerInterface $messageManager,
        StockRegistryInterface $stockState,
        SearchHelper $searchHelper,
        PriceHelper $priceHelper,
        Manager $eventManager,
        DateTime $dateTime,
        TimezoneInterface $timeZoneInterface,
        ReviewCollection $reviewCollection,
        ModuleManager $moduleManager,
        Logger $logger
    ) {
        $this->pageConfig          = $pageConfig;
        $this->request             = $request;
        $this->helperData          = $helpData;
        $this->stockItemRepository = $stockItemRepository;
        $this->registry            = $registry;
        $this->_storeManager       = $storeManager;
        $this->reviewFactory       = $reviewFactory;
        $this->_urlBuilder         = $urlBuilder;
        $this->productFactory      = $productFactory;
        $this->messageManager      = $messageManager;
        $this->stockState          = $stockState;
        $this->_searchHelper       = $searchHelper;
        $this->_priceHelper        = $priceHelper;
        $this->_eventManager       = $eventManager;
        $this->_dateTime           = $dateTime;
        $this->_timeZoneInterface  = $timeZoneInterface;
        $this->_reviewCollection   = $reviewCollection;
        $this->_moduleManager      = $moduleManager;
        $this->logger              = $logger;
    }

    /**
     * @param Renderer $subject
     */
    public function beforeRenderMetadata(Renderer $subject)
    {
        $this->logger->info(" Method: beforeRenderMetadata ");
        if ($this->helperData->isGoogleReviewModuleEnabled()) {
            $this->logger->info(" Method: beforeRenderMetadata | isGoogleReviewModuleEnabled | Enabled"); 
            $pages = [
                'catalogsearch_result_index',
                'cms_noroute_index',
                'catalogsearch_advanced_result'
            ];
            if (in_array($this->getFullActionName(), $pages)) {
                $this->logger->info(" Method: beforeRenderMetadata | No Index No Follow | Applied");
                $this->pageConfig->setMetadata('robots', 'NOINDEX,NOFOLLOW');
            }
        } else {
            $this->logger->info("== Module Disabled ===");
        }
    }

    /**
     * @param Renderer $subject
     * @param $result
     *
     * @return string
     */
    public function afterRenderHeadContent(Renderer $subject, $result)
    {
        $this->logger->info("------------ Start ---------------");
        if ($this->helperData->isGoogleReviewModuleEnabled()) {
            $this->logger->info(" Method: afterRenderHeadContent | isGoogleReviewModuleEnabled | Enabled");
            $fullActionname = $this->getFullActionName();
            /*$result .= $this->getLogoData();  */

            switch ($fullActionname) {
                case 'catalog_product_view':
                    if ($this->helperData->getIsProductDataEnabled()) {
                        $this->logger->info(" Method: afterRenderHeadContent | Product Detail Page");
                        $productStructuredData = $this->showProductStructuredData();
                        if ($productStructuredData != false) {
                            $this->logger->info(" ### Product Structured Data ### : ");
                            $this->logger->info($productStructuredData);
                            $result .= $productStructuredData; 
                        }
                    } else {
                        $this->logger->info(" -------- Product Data Structure is not enabled ----------");
                    }

                    break;
                case 'cms_index_index':
                    $this->logger->info(" Method: afterRenderHeadContent | CMS Page");
                    try {
                        if ($this->helperData->isBusinessStructureEnabled()) {
                            $this->logger->info(" Method: afterRenderHeadContent | isBusinessStructureEnabled | Enabled");
                            $businessStructureData = $this->showBusinessStructuredData();
                            $result .= $businessStructureData;
                            $this->logger->info(" ### Business Structured Data ### : ");
                            $this->logger->info($businessStructureData);
                        }
                    } catch (Exception $e) {
                        $this->logger->info('Exception | '.$e->getMessage());
                    }
                    break;
            }
        } else {
            $this->logger->info("=========== Google Review Snippet is not enabled ==========");
        }

        $this->logger->info("------------ Ends ---------------");
        return $result;
    }

    /**
     * Get full action name
     * @return string
     */
    public function getFullActionName()
    {
        return $this->request->getFullActionName();
    }

    /**
     * Get current product
     * @return mixed
     */
    public function getProduct()
    {
        return $this->registry->registry('current_product');
    }

    /**
     * Get Url
     *
     * @param string $route
     * @param array $params
     *
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }

    /**
     * @param $productId
     *
     * @return StockItemInterface
     * @throws NoSuchEntityException
     */
    public function getProductStock($productId)
    {
        return $this->stockItemRepository->get($productId);
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getReviewCount()
    {
        if (!$this->getProduct()->getRatingSummary()) {
            $this->getEntitySummary($this->getProduct());
        }

        return $this->getProduct()->getRatingSummary()->getReviewsCount();
    }

    /**
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getRatingSummary()
    {
        if (!$this->getProduct()->getRatingSummary()) {
            $this->getEntitySummary($this->getProduct());
        }

        return $this->getProduct()->getRatingSummary()->getRatingSummary();
    }

    /**
     * @param $product
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getEntitySummary($product)
    {
        $this->reviewFactory->create()->getEntitySummary($product, $this->_storeManager->getStore()->getId());
    }

    /**
     * Show product structured data
     * @return string
     *
     */
    public function showProductStructuredData()
    {
        $currentProduct = $this->getProduct();
        $enabledProductTypes = $this->helperData->getIsProductDataProductTypesEnabled();

        if ($currentProduct && in_array($currentProduct->getTypeId(), $enabledProductTypes)) {
            try {
                $productId = $currentProduct->getId() ?: $this->request->getParam('id');

                $product         = $this->productFactory->create()->load($productId);
                $availability    = $product->isAvailable() ? 'InStock' : 'OutOfStock';
                $stockItem       = $this->stockState->getStockItem(
                    $product->getId(),
                    $product->getStore()->getWebsiteId()
                );
                $priceValidUntil = $currentProduct->getSpecialToDate();

                $productStructuredData = [
                    '@context'    => 'http://schema.org/',
                    '@type'       => 'Product',
                    'name'        => $currentProduct->getName(),
                    'description' => trim(strip_tags($currentProduct->getDescription())) ?: 'Default Description',
                    'sku'         => $currentProduct->getSku(),
                    'url'         => $currentProduct->getProductUrl(),
                    'image'       => $this->getUrl('pub/media/catalog') . 'product' . $currentProduct->getImage(),
                    'offers'      => [
                        '@type'         => 'Offer',
                        'priceCurrency' => $this->_storeManager->getStore()->getCurrentCurrencyCode(),
                        'price'         => $currentProduct->getPriceInfo()->getPrice('final_price')->getValue(),
                        'itemOffered'   => $stockItem->getQty(),
                        'availability'  => 'http://schema.org/' . $availability,
                        'offerCount'  => '1',
                        'url'           => $currentProduct->getProductUrl()
                    ],
                    'availability'  => 'http://schema.org/' . $availability,
                    'url'           => $currentProduct->getProductUrl()
                ];

                $productStructuredData['gtin14'] = "12345678901234";
                $productStructuredData['mpn'] = '925872';
                $productStructuredData['isbn'] = '1451648537';

                $this->logger->info(" Product Type is: ".$currentProduct->getTypeId());
                if ($currentProduct->getTypeId() != 'simple') {
                    $productStructuredData = $this->addProductStructuredDataByType(
                        $currentProduct->getTypeId(),
                        $currentProduct,
                        $productStructuredData
                    );
                }

                $priceValidUntil = $currentProduct->getSpecialToDate();
                if (!empty($priceValidUntil)) {
                    $productStructuredData['offers']['priceValidUntil'] = $priceValidUntil;
                } else {
                    $productStructuredData['offers']['priceValidUntil'] = date("Y-m-d");
                }

                $brand_attribute = $this->helperData->getProductBrand();
                $brandValue = $product->getResource()->getAttribute($brand_attribute)->getFrontend()->getValue($product);
                $productStructuredData['brand']['@type'] = 'Brand';
                $productStructuredData['brand']['name']  = $brandValue ?: ''.$this->helperData->getProductBrandValue();

                $collection = $this->_reviewCollection->create()
                    ->addStatusFilter(
                        Review::STATUS_APPROVED
                    )->addEntityFilter(
                        'product',
                        $product->getId()
                    )->setDateOrder();
                if ($collection->getData()) {
                    foreach ($collection->getData() as $review) {
                        $this->logger->info("# 419 | Adding Review");
                        $productStructuredData['review'][] = [
                            '@type'  => 'Review',
                            'author' => $review['nickname']
                        ];
                    }
                } else {
                    /*---- default review ---*/
                    $productStructuredData['review'] = [
                        '@type' => 'Review',
                        'reviewRating' => [
                            '@type'=> 'Rating',
                            'ratingValue'=> ''.$this->helperData->getProductRatingValue(),
                            'bestRating'=> ''.$this->helperData->getProductBestRating(),
                        ],
                        'author' => [
                            '@type' => 'Person',
                            'name' => ''.$this->helperData->getProductRatingAuthor()
                        ]
                    ];
                    $this->logger->info("# 439 | Added Review");
                }

                if ($this->getReviewCount()) {
                    $productStructuredData['aggregateRating']['@type']       = 'AggregateRating';
                    $productStructuredData['aggregateRating']['bestRating']  = 100;
                    $productStructuredData['aggregateRating']['worstRating'] = 0;
                    $productStructuredData['aggregateRating']['ratingValue'] = $this->getRatingSummary();
                    $productStructuredData['aggregateRating']['reviewCount'] = $this->getReviewCount();
                    $this->logger->info("# 448 | Added Aggregate Ratings");

                    $this->logger->info(print_r($this->getRatingSummary(), true));
                }  else {
                    $productStructuredData['aggregateRating']['@type']       = 'AggregateRating';
                    $productStructuredData['aggregateRating']['bestRating']  = $this->helperData->getProductBestRating();
                    $productStructuredData['aggregateRating']['worstRating'] = 0;
                    $productStructuredData['aggregateRating']['ratingValue'] = $this->helperData->getProductRatingValue();
                    $productStructuredData['aggregateRating']['reviewCount'] = $this->helperData->getProductRatingCount();
                    $this->logger->info("# 455 | Added Default Aggregate Ratings");
                }

                $objectStructuredData = new DataObject(['mpdata' => $productStructuredData]);
                $productStructuredData = $objectStructuredData->getMpdata();

                return $this->helperData->createStructuredData(
                    $productStructuredData,
                    '<!-- Product Structured Data by CNNB Starts -->',
                    '<!-- Product Structured Data by CNNB Ends -->'
                );
            } catch (Exception $e) {
                $this->logger->info($e->getMessage());
                $this->messageManager->addError(__('Can not add structured data'));
            }
        } else {
            return false;
        }
    }

    /**
     * add Bundle Product Structured Data
     *
     * @param $currentProduct
     * @param $productStructuredData
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function getBundleProductStructuredData($currentProduct, $productStructuredData)
    {
        $productStructuredData['offers']['@type']     = 'AggregateOffer';
        try {
            $productStructuredData['offers']['highPrice'] = $currentProduct->getPriceInfo()->getPrice('regular_price')->getMaximalPrice()->getValue();
            $productStructuredData['offers']['lowPrice']  = $currentProduct->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue();
        } catch (Exception $exception) {
            $productStructuredData['offers']['highPrice'] = 0;
            $productStructuredData['offers']['lowPrice']  = 0;
        }
        unset($productStructuredData['offers']['price']);
        $offerData              = [];
        $typeInstance           = $currentProduct->getTypeInstance();
        $childProductCollection = $typeInstance->getSelectionsCollection(
            $typeInstance->getOptionsIds($currentProduct),
            $currentProduct
        );
        foreach ($childProductCollection as $child) {
            $imageUrl = $this->_storeManager->getStore()->getBaseUrl(UrlInterface::URL_TYPE_MEDIA)
                . 'catalog/product' . $child->getImage();

            $offerData[] = [
                '@type' => 'Offer',
                'name'  => $child->getName(),
                'price' => $this->_priceHelper->currency($child->getPrice(), false),
                'sku'   => $child->getSku(),
                'image' => $imageUrl
            ];
        }
        if (!empty($offerData)) {
            $productStructuredData['offers']['offers'] = $offerData;
        }

        return $productStructuredData;
    }

    /**
     * get Business Structured Data
     *
     * @return string
     */
    public function showBusinessStructuredData() 
    {
        $busines_data = $this->helperData->createStructuredData(
            $this->helperData->getBusinessData(),
            '<!-- Business Structured Data by CNNB Starts -->',
            '<!-- Business Structured Data by CNNB Ends-->'
        );
        $this->logger->info(" ");
        $this->logger->info("business data");
        $this->logger->info($busines_data);
        return $busines_data;
    }

    public function getLogoData()
    {
        $logoData = $this->helperData->createStructuredData(
            $this->helperData->getLogoData(),
            '<!-- Logo Structured Data by CNNB Starts -->',
            '<!-- Logo Structured Data by CNNB Ends-->'            
        );

        $this->logger->info(" ");
        $this->logger->info("Logo data");
        $this->logger->info($logoData);
        return $logoData;
    }

    /**
     * get Sitelinks Searchbox Structured Data
     *
     * @return string
     */
    public function showSiteLinksStructuredData()
    {
        $siteLinksStructureData = [
            '@context'        => 'http://schema.org',
            '@type'           => 'WebSite',
            'url'             => $this->_urlBuilder->getBaseUrl(),
            'potentialAction' => [
                '@type'       => 'SearchAction',
                'target'      => $this->_searchHelper->getResultUrl() . '?q={searchbox_target}',
                'query-input' => 'required name=searchbox_target'
            ]
        ];

        return $this->helperData->createStructuredData(
            $siteLinksStructureData,
            '<!-- Sitelinks Searchbox Structured Data by CNNB Starts-->',
            '<!-- Sitelinks Searchbox Structured Data by CNNB Ends -->'
        );
    }

    /**
     * @param $productType
     * @param $currentProduct
     * @param $productStructuredData
     *
     * @return mixed
     * @throws NoSuchEntityException
     */
    public function addProductStructuredDataByType($productType, $currentProduct, $productStructuredData)
    {
        switch ($productType) {
            /*case 'grouped':
                $productStructuredData = $this->getGroupedProductStructuredData(
                    $currentProduct,
                    $productStructuredData
                );
                break;*/
            case 'bundle':
                $productStructuredData = $this->getBundleProductStructuredData($currentProduct, $productStructuredData);
                break;
            /*case 'downloadable':
                $productStructuredData = $this->getDownloadableProductStructuredData(
                    $currentProduct,
                    $productStructuredData
                );
                break;
            case 'configurable':
                $productStructuredData = $this->getConfigurableProductStructuredData(
                    $currentProduct,
                    $productStructuredData
                );
                break;*/
        }

        return $productStructuredData;
    }
}

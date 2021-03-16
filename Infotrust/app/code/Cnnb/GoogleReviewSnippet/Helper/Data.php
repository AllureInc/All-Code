<?php
namespace Cnnb\GoogleReviewSnippet\Helper;

use Magento\Theme\Block\Html\Header\Logo;
use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\UrlInterface;
use Magento\Directory\Model\Region;
use Cnnb\GoogleReviewSnippet\Logger\Logger;

/** 
 *  
 */
class Data extends AbstractHelper
{
    const GOOGLE_REVIEW_ENABLED = 'google_review/general/enabled';
    const GOOGLE_BUSINESS_ENABLED = 'google_review/info/enable';
    const GOOGLE_BUSINESS_NAME = 'google_review/info/business_name';
    const GOOGLE_BUSINESS_TYPE = 'google_review/info/business_type';
    const GOOGLE_BUSINESS_COUNTRY_ID = 'google_review/info/country_id';
    const GOOGLE_BUSINESS_REGION_ID = 'google_review/info/region_id';
    const GOOGLE_BUSINESS_POSTCODE = 'google_review/info/postcode';
    const GOOGLE_BUSINESS_CITY = 'google_review/info/city';
    const GOOGLE_BUSINESS_REVIEW_VALUE = 'google_review/info/business_review_value';
    const GOOGLE_BUSINESS_BEST_REVIEW_VALUE = 'google_review/info/business_review_best';
    const GOOGLE_BUSINESS_REVIEW_COUNT = 'google_review/info/business_review_count';
    const GOOGLE_BUSINESS_REVIEW_AUTHOR = 'google_review/info/business_review_author';
    const GOOGLE_BUSINESS_STREET_LINE = 'google_review/info/street_line';
    const GOOGLE_BUSINESS_TELEPHONE = 'google_review/info/telephone';
    const GOOGLE_BUSINESS_IMAGE_UPLOAD = 'google_review/info/image_upload';
    const GOOGLE_BUSINESS_IS_AGGREGATE_ENABLED = 'google_review/info/enable_aggregate_for_business';
    const GOOGLE_BUSINESS_IS_SITELINK_ENABLED = 'google_review/info/enable_site_link_search';
    const GOOGLE_PRODUCT_DATA_ENABLED = 'google_review/product/enabled';
    const GOOGLE_PRODUCT_DATA_PRODUCT_TYPES_ENABLED = 'google_review/product/product_types';
    const GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_BEST_RATING = 'google_review/product/product_default_best_rating';
    const GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_RATING_VALUE = 'google_review/product/product_default_rating_value';
    const GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_RATING_AUTHOR = 'google_review/product/product_default_rating_author';
    const GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_RATING_COUNT = 'google_review/product/product_default_rating_count';
    const GOOGLE_PRODUCT_DATA_PRODUCT_BRAND = 'google_review/product/product_brand';
    const GOOGLE_PRODUCT_DATA_PRODUCT_BRAND_VALUE = 'google_review/product/default_product_brand';

    protected $scopeConfig;

    /**
     * @var UrlInterface
     */
    protected $urlBuilder;

    protected $logo;

    protected $region;

    protected $logger;

    public function __construct(
        UrlInterface $urlBuilder,
        Region $region,
        Logger $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->urlBuilder  = $urlBuilder;
        $this->region      = $region;
        $this->logger      = $logger;
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
        return $this->urlBuilder->getUrl($route, $params);
    }

    public function isGoogleReviewModuleEnabled($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_REVIEW_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function isBusinessStructureEnabled($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    } 

    public function getBusinessName($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_NAME,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessType($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_TYPE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessCountryId($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_COUNTRY_ID,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessRegionId($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_REGION_ID,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessPostcode($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_POSTCODE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessCity($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_CITY,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessReviewValue($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_REVIEW_VALUE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessBestReviewValue($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_BEST_REVIEW_VALUE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessBestReviewCount($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_REVIEW_COUNT,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessBestReviewAuthor($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_REVIEW_AUTHOR,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessStreetLine($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_STREET_LINE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessTelephone($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_TELEPHONE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessImage($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_IMAGE_UPLOAD,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessIsBusinessAggregateEnabled($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_IS_AGGREGATE_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getBusinessIsSiteLinkEnabled($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_BUSINESS_IS_SITELINK_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getIsProductDataEnabled($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getProductBestRating($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_BEST_RATING,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getProductRatingValue($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_RATING_VALUE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getProductRatingAuthor($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_RATING_AUTHOR,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getProductRatingCount($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_DEFAULT_RATING_COUNT,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getIsProductDataProductTypesEnabled($store_id = null)
    {
        return explode(',', $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_TYPES_ENABLED,
            ScopeInterface::SCOPE_STORE,
            $store_id
        ));
    }

    public function getProductBrand($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_BRAND,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    public function getProductBrandValue($store_id = null)
    {
        return $this->scopeConfig->getValue(
            self::GOOGLE_PRODUCT_DATA_PRODUCT_BRAND_VALUE,
            ScopeInterface::SCOPE_STORE,
            $store_id
        );
    }

    /**
     * Create structure data script
     *
     * @param $data
     * @param string $prefixComment
     * @param string $subfixComment
     *
     * @return string
     */
    public function createStructuredData($data, $prefixComment = '', $subfixComment = '')
    {
        $applicationLdJson = $prefixComment;
        $applicationLdJson .= '<script type="application/ld+json">' . json_encode(
            $data,
            JSON_UNESCAPED_SLASHES
        ) . '</script>';
        $applicationLdJson .= $subfixComment;

        return $applicationLdJson;
    }

    public function getBusinessData()
    {
        $region_id = $this->getBusinessRegionId();
        $region_model = $this->region->load($region_id);
        $image = $this->urlBuilder->getBaseUrl().'pub/media/GoogleReviewSnippet/'.$this->getBusinessImage();
        $data = [
            '@context' => 'http://schema.org/',
            '@type'    => ''.$this->getBusinessType(),
            '@id'    => ''.$this->getBusinessType(),
            'image' => [''.$image],
            'logo' => ''.$image,
            'name'    => ''.$this->getBusinessName(),
            'address'    => [
                '@type' => 'PostalAddress',
                'streetAddress' => ''.$this->getBusinessStreetLine(),
                'addressLocality' => ''.$region_model->getName(),
                'addressRegion' => ''.$region_model->getCode(),
                'postalCode' => ''.$this->getBusinessPostcode(),
                'addressCountry' => ''.$this->getBusinessCountryId()
            ],
            'url'=> ''.$this->getUrl(),
            'priceRange' => '$$$',
            'telephone' => ''.$this->getBusinessTelephone()
        ]; 

        if ($this->getBusinessIsBusinessAggregateEnabled()) {
           /* $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => ''.$this->getBusinessReviewValue(),
                'bestRating' => ''.$this->getBusinessBestReviewValue(),
                'ratingCount' => ''.$this->getBusinessBestReviewCount(),
                'author' => [
                    '@type' => 'Person',
                    'name'  => ''.$this->getBusinessBestReviewAuthor()
                ]
            ];*/

            $data['aggregateRating'] = [
                '@type' => 'AggregateRating',
                'ratingValue' => ''.$this->getBusinessReviewValue(),
                'bestRating' => ''.$this->getBusinessBestReviewValue(),
                'ratingCount' => ''.$this->getBusinessBestReviewCount(),
                'itemReviewed' => [
                    '@type' => 'Organization',
                    'image' => ''.$image,
                    'name'  => ''.$this->getBusinessName(),
                    'telephone' => ''.$this->getBusinessTelephone(),
                    'address'    => [
                        '@type' => 'PostalAddress',
                        'streetAddress' => ''.$this->getBusinessStreetLine(),
                        'addressLocality' => ''.$region_model->getName(),
                        'addressRegion' => ''.$region_model->getCode(),
                        'addressCountry' => ''.$this->getBusinessCountryId(),
                        'postalCode' => ''.$this->getBusinessPostcode()
                    ]
                ],
                'author' => [
                    '@type' => 'Person',
                    'name'  => ''.$this->getBusinessBestReviewAuthor()
                ]
            ];
        } 
        
        return $data;
    }

    public function getLogoData()
    {
        $data = [
            '@context'=> 'https://schema.org',
            '@type' => 'Organization',
            'url' => ''.$this->getUrl(),
            'logo' => ''.$this->urlBuilder->getBaseUrl().'pub/media/GoogleReviewSnippet/'.$this->getBusinessImage()
        ];

        return $data;
    }    
}
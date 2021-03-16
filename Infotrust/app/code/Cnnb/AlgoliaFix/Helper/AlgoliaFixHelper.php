<?php
/**
 * @category    AlgoliaFix
 * @package     Cnnb_AlgoliaFix
 *
 */
namespace Cnnb\AlgoliaFix\Helper;

use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Psr\Log\LoggerInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class AlgoliaFixHelper extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var $productloader
     */
    protected $_productloader;

    /**
     * @var storeManager
     */
    protected $storeManager;

    /**
     * @var $postDataHelper
     */
    protected $_postDataHelper;

    /**
     * @var $escaper
     */
    protected $escaper; 

    /**
     * @var $productRepository
     */
    protected $productRepository; 

    /**
     * @var $productCollection
     */
    protected $productCollection;

    /**
     * @var $logger
     */
    protected $logger; 

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var $_productCollectionFactory
     */
    protected $_productCollectionFactory; 

    /**
     * @var $_searchCriteriaInterface
     */
    protected $_searchCriteriaInterface; 

    /**
     * @var $_ruleFactory
     */
    protected $_ruleFactory;

    /**
     * Path to configuration, getting set product from date
     */
    const XML_PATH_PRODUCT_NEW_FROM = 'cnnb_algoliafix_settings/new_product_attribute_mapping/mapping_from';

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Helper\PostHelper $postDataHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        LoggerInterface $logger,
        Escaper $escaper,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        SearchCriteriaInterface $searchCriteriaInterface,
        \Magento\CatalogRule\Model\RuleFactory $ruleFactory
    ) {
        $this->_productloader = $_productloader;
        $this->storeManager = $storeManager;
        $this->_postDataHelper = $postDataHelper;
        $this->escaper = $escaper ?? ObjectManager::getInstance()->get(Escaper::class);
        $this->productRepository = $productRepository;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_searchCriteriaInterface = $searchCriteriaInterface;
        $this->_ruleFactory = $ruleFactory;
        parent::__construct($context);
    }

    /**
     * Function for getting product object using product id
     * @return array
     */
    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }

    /**
     * Function for add param for wish list
     * @return array
     */
    public function getAddParams($item_id)
    {
        $url = $this->storeManager->getStore()->getUrl('wishlist/index/add');
        if ($item_id) {
            $params['product'] = $item_id;
        }
        return $this->_postDataHelper->getPostData(
            $this->escaper->escapeUrl($url),
            $params
        );
    }

    /**
     * Function for get All Product Data with All Attributes
     * @return array
     */
    public function getAllProductData($productId)
    {
        return $this->productRepository->getById($productId);
    }

    /**
     * Function for get product collection
     * @return array
     */
    public function getProductCollectionWithAttribute($attribute_code, $attribute_value, $data)
    {
        $collectionObj = ObjectManager::getInstance()->get(CollectionFactory::class);
        $collection = $collectionObj->create()->addAttributeToSelect('*')->addAttributeToFilter($attribute_code, $attribute_value)->load();
        if ($collection->getSize()) {
            foreach ($collection as $product) {
                $data[$attribute_code][] = $product->getEntityId();
            }
        }
        return $data;
    }

    /**
     * Function for get product collection (filterd by 'new' & 'sale')
     * @return array
     */
    public function getNewAndSaleProductsData()
    {
        $data = [];
        $collection = $this->_productCollectionFactory->create();
        $attribute_from = $this->_scopeConfig->getValue(
            self::XML_PATH_PRODUCT_NEW_FROM,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        );
        $this->logger->info(' Attribute from | '.$attribute_from);
        $data['new'] = [];
        $data['sale'] = [];

        if($attribute_from == 'news_from_date') {
            $todayDate  = date('Y-m-d', time());
            $collectionForNew = clone $collection;
            $collectionForNew->addAttributeToFilter($attribute_from, array('date' => true, 'to' => $todayDate));
            $data['new'] = $collectionForNew->getAllIds(); 
        } else {
            $data['new'] = [];
            $this->logger->info(' Please map "Set product as from date" from admin setting to get new products. ');
        }
        $data['new'] = array_unique($data['new']);

        /* Getting products having special price */
        $collectionForSale = clone $collection;
        $collectionForSale->addAttributeToFilter('special_price',  ['neq' => '']);
        foreach ($collectionForSale->getAllIds() as $key => $value) {
            array_push($data['sale'], $value);
        }
        /* Getting products having special price ends */
       
        /* Getting the catalog rule product ids */
        $catalogRule = $this->_ruleFactory->create();
        $websiteId = $this->storeManager->getStore()->getWebsiteId();//current Website Id
        $resultProductIds = [];
        $catalogRuleCollection = $catalogRule->getCollection();
        $catalogRuleCollection->addIsActiveFilter(1);//filter for active rules only
        foreach ($catalogRuleCollection as $catalogRule) {
            $productIdsAccToRule = $catalogRule->getMatchingProductIds();
            foreach ($productIdsAccToRule as $productId => $ruleProductArray) {
                if (!empty($ruleProductArray[$websiteId])) {
                   array_push($data['sale'], $productId);
                }
            }
        }
        $data['sale'] = array_unique($data['sale']);
        /* Getting the catalog rule product ids ends */

        return $data;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->storeManager->getStore()->getId();
    }
}

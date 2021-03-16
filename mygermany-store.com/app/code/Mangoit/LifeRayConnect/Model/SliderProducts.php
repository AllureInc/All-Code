<?php
namespace Mangoit\LifeRayConnect\Model;
use Mangoit\LifeRayConnect\Api\SlidersProductInterface;
use Magento\Framework\App\ObjectManager;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Exception\NoSuchEntityException;



class SliderProducts implements SlidersProductInterface
{

    /**
     * @var orderInterface
     */
    protected $orderInterface;
    /**
    * @var \Magento\Framework\Controller\Result\JsonFactory
    */
    protected $resultJsonFactory;
    /**
     * @var LoggerInterface
     */
    private $logger;

    protected $_reviewFactory;

    protected $_localeDate;

    protected $_resourceFactory;

    protected $_productCollectionFactory;

    /**
     * @var customerRepositoryInterface
    */    
    protected $_customerRepositoryInterface;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $priceFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param PriceModifier $priceModifier
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param GroupManagementInterface $groupManagement
     * @param GroupRepositoryInterface $groupRepository
     * @param LoggerInterface $logger
     */
    public function __construct(
        \Magento\Sales\Api\Data\OrderInterface $orderInterface,
        \Magento\Review\Model\ReviewFactory $reviewFactory,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $timezoneInterface,
        LoggerInterface $logger,
        \Magento\Reports\Model\ResourceModel\Report\Collection\Factory $resourceFactory,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface
    ) {
        $this->_reviewFactory = $reviewFactory;
        $this->_resourceFactory = $resourceFactory;
        $this->_localeDate = $timezoneInterface;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->orderInterface = $orderInterface;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->logger = $logger;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
    }
    /**
     * It will return product collection based on type
     *
     * @api
     * @param string $type Type of product slider
     * @return string collection of products
     */
    public function slidersproduct($type) {
        $returnResultArray = [];
        $path = 'catalog/placeholder/image_placeholder';
        $objectManager = ObjectManager::getInstance();
        $productList = $objectManager->create('Mangoit\Advertisement\Block\Adminhtml\Advcontent');
        // $returnProductIdArray = [];
        $limit = 10;
        $loadedModel =  $objectManager->create('Magento\Catalog\Model\Product');
        $resourceCollection = $this->_resourceFactory->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
        $store = $objectManager->create('Magento\Store\Model\StoreManagerInterface')->getStore();
        $placeholder =  $store->getConfig($path);
        $type = strtolower($type);
        if ($type == 'top') {
             $returnProductIdArray = $this->getTopProducts($store);
        } else if ($type == 'best') {
            $returnProductIdArray = $this->getBestSellerProductIds();
        } else if ($type == 'new') {
            $returnProductIdArray = $this->getNewProductsIds();
        }

        if ( ($type == 'top') || ($type == 'best') || ($type == 'new') )  {
            foreach ($returnProductIdArray as $value) {
                $loadedModelData = $loadedModel->load($value);
                $url = $loadedModelData->getProductUrl();
                $price = $loadedModelData->getPrice();
                $imgUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA) . 'catalog/product' . $loadedModelData->getImage();
                /*if (empty($loadedModelData->getImage())) {
                    $imgUrl = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA).'catalog/product/placeholder'.$placeholder;
                }*/
                $returnResultArray[] = array('imageURL' => $imgUrl, 'redirectURL'=> $url, 'price'=> $price);
                $loadedModelData->unsetData();
            }
            // return ['status'=> '200', 'data' => json_encode($returnResultArray)];
            return ['status'=> '200', 'data' => $returnResultArray];
            
        } else {
            throw new \Magento\Framework\Exception\NoSuchEntityException(__('Requested type is not exist plese try with top, best and new .'));
        }

        
        
    }

    public function getBestSellerProductIds()
    { 
        $limit = 10;
        $productIds = [];
        $returnProductIdArray = [];
        $resourceCollection = 
        $this->_resourceFactory->create('Magento\Sales\Model\ResourceModel\Report\Bestsellers\Collection');
        foreach ($resourceCollection as $value) {
            $productIds[] = $value->getProductId();
        }
        $collection = $this->_productCollectionFactory->create();
        $collection->addAttributeToFilter(
                'entity_id',
                [
                    'in' => $productIds
                ]
            )->setPageSize(
                $limit
            );

        foreach ($collection->getData() as $item) {
            $returnProductIdArray[] = $item['entity_id'];
        }
        return $returnProductIdArray;
    }

    public function getNewProductsIds()
    {
        $limit = 10;
        $productIds = [];
        $returnProductIdArray = [];
        $collection = $this->_productCollectionFactory->create();
        $todayStartOfDayDate = $this->_localeDate->date()->setTime(0, 0, 0)->format('Y-m-d H:i:s');
        $todayEndOfDayDate = $this->_localeDate->date()->setTime(23, 59, 59)->format('Y-m-d H:i:s');
        $collection->addAttributeToFilter(
                'news_from_date',
                [
                    'or' => [
                        0 => ['date' => true, 'to' => $todayEndOfDayDate],
                        1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                ],
                'left'
            )->addAttributeToFilter(
                'news_to_date',
                [
                    'or' => [
                        0 => ['date' => true, 'from' => $todayStartOfDayDate],
                        1 => ['is' => new \Zend_Db_Expr('null')],
                    ]
                ],
                'left'
            )->addAttributeToFilter(
                [
                    ['attribute' => 'news_from_date', 'is' => new \Zend_Db_Expr('not null')],
                    ['attribute' => 'news_to_date', 'is' => new \Zend_Db_Expr('not null')],
                ]
            )->addAttributeToSort(
                'news_from_date',
                'desc'
            )->setPageSize(
                $limit
            );

        foreach ($collection->getData() as $item) {
            $returnProductIdArray[] = $item['entity_id'];
        }
        return $returnProductIdArray;
    }

    public function getTopProducts($store)
    {
        $collection = $this->_productCollectionFactory->create()
            ->addAttributeToSelect('*')
            ->load();

        $rating = array();
        foreach ($collection as $product) {
            $this->_reviewFactory->create()->getEntitySummary($product, $store->getId());
            $ratingSummary = $product->getRatingSummary()->getRatingSummary();
            if($ratingSummary!=null){
                // $rating[$product->getId()] = $ratingSummary;
                $rating[] = $product->getId();
            }
        }
        arsort($rating);

        $limit = 10;
        if(count($rating) > $limit) {
            $rating = array_slice($rating, 0, $limit, true);
        }
        return $rating;

        // print_r($rating);
    } 
}
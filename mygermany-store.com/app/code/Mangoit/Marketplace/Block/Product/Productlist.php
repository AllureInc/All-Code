<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */

namespace Mangoit\Marketplace\Block\Product;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Webkul\Marketplace\Helper\Data as MpHelper;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as MpProductCollection;
use Webkul\Marketplace\Model\SaleslistFactory as MpSalesList;

class Productlist extends \Webkul\Marketplace\Block\Product\Productlist
{
    /**
     * @var \Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var PriceCurrencyInterface
     */
    protected $_priceCurrency;

    /** @var \Magento\Catalog\Model\Product */
    protected $_productlists;

    /**
     * @var MpHelper
     */
    protected $mpHelper;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute
     */
    protected $eavAttribute;

    /**
     * @var MpProductCollection
     */
    protected $mpProductCollection;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $productFactory;

    /**
     * @var MpSalesList
     */
    protected $mpSalesList;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param Context                                   $context
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param MpHelper                                  $mpHelper
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute
     * @param MpProductCollection                       $mpProductCollection
     * @param \Magento\Catalog\Model\ProductFactory     $productFactory
     * @param MpSalesList                               $mpSalesList
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $productCollectionFactory,
        PriceCurrencyInterface $priceCurrency,
        MpHelper $mpHelper,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute $eavAttribute,
        MpProductCollection $mpProductCollection,
        \Magento\Catalog\Model\ProductFactory $productFactory,
        MpSalesList $mpSalesList,
        array $data = [],
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_imageHelper = $context->getImageHelper();
        $this->_priceCurrency = $priceCurrency;
        $this->mpHelper = $mpHelper;
        $this->eavAttribute = $eavAttribute;
        $this->mpProductCollection = $mpProductCollection;
        $this->productFactory = $productFactory;
        $this->mpSalesList = $mpSalesList;
        $this->_objectManager = $objectManager;
        parent::__construct($context, $customerSession, $productCollectionFactory, $priceCurrency , $mpHelper, $eavAttribute, $mpProductCollection, $productFactory, $mpSalesList, $data, $objectManager);
    }

    protected function _construct()
    {
        parent::_construct();
        $this->pageConfig->getTitle()->set(__('My Product List'));
    }

    /**
     * Get formatted by price and currency.
     *
     * @param   $price
     * @param   $currency
     *
     * @return array || float
     */
    public function getFormatedPrice($price, $currency)
    {
        return $this->_priceCurrency->format(
            $price,
            true,
            2,
            null,
            $currency
        );
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllProducts()
    {
        $storeId = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getCurrentStoreId();
        $websiteId = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        )->getWebsiteId();
        if (!($customerId = $this->_customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->_productlists) {
            $paramData = $this->getRequest()->getParams();
            $filter = '';
            $filterStatus = '';
            $filterDateFrom = '';
            $filterDateTo = '';
            $from = null;
            $to = null;

            $id = '';
            $cat = '';

            //MIS changes
            if (isset($paramData['id'])) {
                $id = $paramData['id'] != '' ? $paramData['id'] : '';
            }

            if (isset($paramData['price_from']) && ($paramData['price_from'] != '')) {
                $price_from = $paramData['price_from'] != '' ? $paramData['price_from'] : '';
            }
            if (isset($paramData['price_to']) && ($paramData['price_to'] != '')) {
                $price_to = $paramData['price_to'] != '' ? $paramData['price_to'] : '';
            }
            if (isset($paramData['cat'])) {
                $cat = $paramData['cat'] != '' ? $paramData['cat'] : '';
            }

            if (isset($paramData['s'])) {
                $filter = $paramData['s'] != '' ? $paramData['s'] : '';
            }
            if (isset($paramData['status'])) {
                $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
            }
            if (isset($paramData['from_date'])) {
                $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
            }
            if (isset($paramData['to_date'])) {
                $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
            }
            
            $catalog_product_entity_decimal = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
            )->getTable('catalog_product_entity_decimal');

            $catalog_category_product = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
            )->getTable('catalog_category_product');



            if ($filterDateTo) {
                $todate = date_create($filterDateTo);
                $to = date_format($todate, 'Y-m-d 23:59:59');
            }

            if (!$to) {
                $to = date('Y-m-d 23:59:59');
            }
            if ($filterDateFrom) {
                $fromdate = date_create($filterDateFrom);
                $from = date_format($fromdate, 'Y-m-d H:i:s');
            }

            $eavAttribute = $this->_objectManager->get(
                'Magento\Eav\Model\ResourceModel\Entity\Attribute'
            );
            $proAttId = $eavAttribute->getIdByCode('catalog_product', 'name');
            $proStatusAttId = $eavAttribute->getIdByCode('catalog_product', 'status');

            $catalogProductEntity = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
            )->getTable('catalog_product_entity');

            $catalogProductEntityVarchar = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
            )->getTable('catalog_product_entity_varchar');

            $catalogProductEntityInt = $this->_objectManager->create(
                'Webkul\Marketplace\Model\ResourceModel\Product\Collection'
            )->getTable('catalog_product_entity_int');


            /* Get Seller Product Collection for current Store Id */

            $storeCollection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = '.$storeId.' AND 
                cpev.value like "%'.$filter.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $storeCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = '.$storeId.' AND 
                cpei.attribute_id = '.$proStatusAttId
            );


            $storeCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );

            if ($from && $to) {
                $storeCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $storeCollection->getSelect('*')->group('mageproduct_id');

            $storeProductIDs = $storeCollection->getAllIds();

            /* Get Seller Product Collection for 0 Store Id */

            $adminStoreCollection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection();

            $adminStoreCollection->addFieldToFilter(
                'seller_id',
                $customerId
            )->addFieldToSelect(
                ['mageproduct_id']
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityVarchar.' as cpev',
                'main_table.mageproduct_id = cpev.entity_id'
            )->where(
                'cpev.store_id = 0 AND 
                cpev.value like "%'.$filter.'%" AND 
                cpev.attribute_id = '.$proAttId
            );

            $adminStoreCollection->getSelect()->join(
                $catalogProductEntityInt.' as cpei',
                'main_table.mageproduct_id = cpei.entity_id'
            )->where(
                'cpei.store_id = 0 AND 
                cpei.attribute_id = '.$proStatusAttId
            );

            if ($filterStatus) {
                $adminStoreCollection->getSelect()->where(
                    'cpei.value = '.$filterStatus
                );
            }
           
            $adminStoreCollection->getSelect()->join(
                $catalogProductEntity.' as cpe',
                'main_table.mageproduct_id = cpe.entity_id'
            );
            if ($from && $to) {
                $adminStoreCollection->getSelect()->where(
                    "cpe.created_at BETWEEN '".$from."' AND '".$to."'"
                );
            }

            $adminStoreCollection->getSelect()->group('mageproduct_id');

            $adminProductIDs = $adminStoreCollection->getAllIds();

            $productIDs = array_merge($storeProductIDs, $adminProductIDs);

            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )
            ->getCollection()
            ->addFieldToFilter(
                'seller_id',
                $customerId
            );

            //Custom Code 28 August 2018
            $attrRepo = $this->_objectManager->create(
                'Magento\Catalog\Model\Product\Attribute\Repository'
            );
            // $proAttId = $attrRepo->get('name');

 
            if ($id) {
                //ID wise Filter
                $collection->addFieldToFilter(
                    'mageproduct_id',
                    ['eq' => $id]
                );
            } else {
                $collection->addFieldToFilter(
                    'mageproduct_id',
                    ['in' => $productIDs]
                );
            }
            $collection->setOrder('mageproduct_id');

            try {
                //Price Column in collection
                $priceId = $eavAttribute->getIdByCode('catalog_product', 'price');
                $collection->getSelect()->join(
                    $catalog_product_entity_decimal.' as product_entity',
                    'main_table.mageproduct_id = product_entity.entity_id AND product_entity.attribute_id='.$priceId,
                    ['price' => 'value']
                );

                //name Column in collection
                $proAttId = $eavAttribute->getIdByCode('catalog_product', 'name');

                $collection->getSelect()->join(
                    $catalogProductEntityVarchar.' as product_entity_varchar',
                    'main_table.mageproduct_id = product_entity_varchar.entity_id AND product_entity_varchar.attribute_id='.$proAttId,
                    ['product_name' => 'value']
                );
            } catch (Exception $e) {
                echo $e->getMessage();
            }
            //Price Filter
            if (isset($price_from) && (isset($price_to))) {
                $collection->addFieldToFilter('product_entity.value', ['gteq' => $price_from]);
                $collection->addFieldToFilter('product_entity.value', ['lteq' => $price_to]);
            } 
            //Category field in Collection
            $collection->getSelect()->join(
                $catalog_category_product.' as at_category_id',
                'main_table.mageproduct_id = at_category_id.product_id',
                ['category_id']
            );
            if ($cat) {
                $collection->addFieldToFilter('category_id', ['eq' => $cat]);
            }
            
            $collection->getSelect()->group('mageproduct_id');
            if (isset($paramData['order'])) {
                if ($paramData['order'] == 'DESC') {
                    $collection->getSelect()->order('product_name DESC');
                    $collection->setOrder('product_name', 'DESC');
                } else if($paramData['order'] == 'ASC') {
                    $collection->getSelect()->order('product_name');
                }
            }

            // Product name filter
            if (isset($paramData['order'])) {
                if ($paramData['order'] == 'ASC') {
                    $collection->getSelect()->order('product_name');
                } else {
                    $collection->getSelect()->order('product_name DESC');
                    $collection->setOrder('product_name', 'DESC');
                }
            } 

            //Order status filter
            if ($filterStatus) {
               $collection->addFieldToFilter('status', ['eq' => $filterStatus]);
            } 
            //Custom code 28 August end
            $this->_productlists = $collection;
        }
        
        return $this->_productlists;
    }
    public function getCategoryCollection()
    {
        $categoryCollection = $this->_objectManager->get('\Magento\Catalog\Model\ResourceModel\Category\CollectionFactory');
        $categories = $categoryCollection->create();
        $categories->addAttributeToSelect('*');
        $categoriesArray = [];
        foreach ($categories as $category) {
            if ($category->getLevel() > 1) {
                $categoriesArray[$category->getId()] = $category->getName();
                // echo $category->getUrl() . '<br />';
            }
        }
        return $categoriesArray;
    }
   
}

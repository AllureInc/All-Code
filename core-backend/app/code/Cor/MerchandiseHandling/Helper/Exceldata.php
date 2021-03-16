<?php
/**
 * Module: Cor_MerchandiseHandling
 * Helper File
 * Helper methods for generating excel report.
 */
namespace Cor\MerchandiseHandling\Helper;
/**
 * Main Class
 */
class Exceldata extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;
    protected $_simpleProductsHeader;
    protected $_configurableProductsHeader;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    /**
     * Class Constructor
     */
    function __construct( 
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Psr\Log\LoggerInterface $logger
    ){
        $this->_objectManager = $objectmanager;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Method which returns artist name
     * @return string
     */
    public function getArtistDetails($artist_id)
    {
        $artist = '';
        $model = $this->_objectManager->create('Cor\Artist\Model\Artist');
        $model->load($artist_id);
        if (count($model->getData()) > 0) {
            $artist = $model->getArtistName();
        }
        return $artist;
    }

    /**
     * Method which returns event details
     * @return array
     */
    public function getEventDetails($event_id)
    {
        $event = [];
        $model = $this->_objectManager->create('Cor\Eventmanagement\Model\Event');
        $model->load($event_id);
        if (count($model->getData()) > 0) {
            $event['name'] = $model->getEventName();
            $event['street'] = $model->getEventStreet();
            $event['city'] = $model->getEventCity();
            $event['state'] = $model->getEventState();
            $event['zip'] = $model->getEventZip();
            $event['country'] = $model->getEventCountry();
            $event['start_date'] = $model->getEventStartDate();
            $event['end_date'] = $model->getEventEndDate();
            $event['tax_values'] = $model->getTaxValues();

        }
        return $event;
    }

    /**
     * Method which returns product sales details
     * @return array
     */
    public function getProductsData($artist_id, $event_id)
    {
        $this->_logger->info('Generating excel report');
        $masterFileHeader = array('Price','Count In','Add','Total In','Comp','Count Out','Total Sold','Gross');
        $taxFileHeader = $this->getTaxCategoryHeaders();
        $productCollection = $this->getProductsCollection($artist_id, $event_id);
        $artistProductBlock =  $this->_objectManager->create('Cor\MerchandiseHandling\Block\Adminhtml\ArtistProducts');
        foreach ($productCollection as $_products) {
            $product_id = $_products['entity_id'];
            $cor_category = $_products['cor_category'];
            $product = $this->getProducts($product_id);
            $product_visibility = $product->getVisibility();
            $productType = $product->getTypeId();
            $headerAttributes = [];
            $productsData = [];
            if($productType == 'simple') 
            {
                $data = [
                        'id' => $product->getEntityId(),
                        'name' => $product->getName(),
                        'price' => number_format($product->getPrice(), 2),
                        'parent_id' => '0',
                        'header_options'=> $headerAttributes,
                        'type'=> 'simple'
                   ]; 
                $this->_logger->info('');
                $this->_logger->info('Product name '.$product->getName());
                $resultdata[$productType][] =  $this->getMerchandiseData($artist_id, $event_id, $data, $cor_category);
            } 
            else{
                if ($productType == 'configurable') {
                    $this->_logger->info('');
                    $childData =  $artistProductBlock->getConfigurableChildProducts($product);
                    $merchandiseData = array();  
                    $header_options = array_values($childData['header_options']);
                    $configHeader[] = array_merge($header_options, $masterFileHeader);
                    $configTaxHeader[]['tax_data'] = array_merge($header_options, $taxFileHeader);
                    foreach ($childData['child_products'] as $childProducts) {
                        $this->_logger->info('');
                        $this->_logger->info('Product name '.$childProducts['product']['name']);
                        $data = [];
                        $data = [
                            'id'=> $childProducts['product']['entity_id'],
                            'header_options'=> $childProducts['options'],
                            'price'=> $childProducts['product']['price'],
                            'parent_id'=> $product->getEntityId(),
                            'type'=> 'config'
                        ];
                        $merchandiseData[] =  $this->getMerchandiseData($artist_id, $event_id, $data, $cor_category); 
                    }
                    $finalConfigProduct = array_merge($configHeader, $configTaxHeader, $merchandiseData);
                    $resultdata[$productType][$product->getName()] = $finalConfigProduct;
                    unset($configHeader);
                    unset($configTaxHeader);

                } 
                if (($productType == 'bundle') || ($productType == 'grouped')) {
                    $this->_logger->info('');
                    $productId = $product->getEntityId();
                    if ($productType == 'bundle') {
                        $this->_logger->info('Product name '.$product->getName());
                        $childData =  $artistProductBlock->getBundleProducts($product);
                    } else {
                        $this->_logger->info('Product name '.$product->getName());
                        $childData =  $artistProductBlock->getGroupProducts($product);
                    }
                    $bundleData = array();  
                    foreach ($childData as $childProducts) {
                        $this->_logger->info('');
                        $this->_logger->info('Product name '.$childProducts->getName());
                        $name = $childProducts->getEntityId();
                        $data = [];
                        $data = [
                            'id'=> $childProducts->getEntityId(),
                            'name' => $childProducts->getName(),
                            'header_options'=> $headerAttributes,
                            'price'=> number_format($childProducts->getPrice(), 2),
                            'parent_id'=> $product->getEntityId()
                        ];
                        $bundleData[] =  $this->getMerchandiseData($artist_id, $event_id, $data, $cor_category);
                    }
                    $resultdata[$productType][$product->getName()] = $bundleData;
                }
                if ($productType == 'virtual') 
                {
                    $this->_logger->info('');
                    $this->_logger->info('Product name '.$product->getName());
                    $data = [
                        'id' => $product->getEntityId(),
                        'name' => $product->getName(),
                        'price' => number_format($product->getPrice(), 2),
                        'parent_id' => '0',
                        'header_options'=> $headerAttributes,
                        'type'=> 'simple'
                    ]; 
                    $resultdata[$productType][] =  $this->getMerchandiseData($artist_id, $event_id, $data, $cor_category);
                } 
            }
        }
        return $resultdata;
    }

    /**
     * Method which returns product collections based on event and artist id
     * @return object
     */
    public function getProductsCollection($artist_id, $event_id) 
    {
        $eventFilter = [['attribute' => 'cor_events', 'finset' => $event_id]];
        $productModel = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
        $productFactory = $productModel->create();
        $productFactory->addAttributeToSelect('entity_id');
        $productFactory->addAttributeToSelect('cor_category');
        $productFactory->addAttributeToFilter($eventFilter);
        $productFactory->addAttributeToFilter('cor_artist', $artist_id);
        $productFactory->addAttributeToFilter('visibility', ['neq' => 1]);
        return $productFactory;
    }

    /**
     * Method which returns model of loaded product
     * @return object
     */
    public function getProducts($id) 
    {
        $model = $this->_objectManager->create('\Magento\Catalog\Model\Product')->load($id);
        return $model;
    }

    /**
     * Method which returns saved merchandise handling data in array
     * @return array
     */
    public function getMerchandiseData($artist_id, $event_id, $data, $cor_category)
    {
        $resultdata = [];
        /* For tax details */
        $tax = $this->getProductTaxDetails($event_id, $cor_category);
        /* */
        $model = $this->_objectManager->create('\Cor\MerchandiseHandling\Model\Merchandise');
        $collection = $model->getCollection();
        $filteredCollection = $collection
        ->addFieldToFilter('artist_id', ['eq'=> $artist_id])
        ->addFieldToFilter('event_id', ['eq'=> $event_id])
        ->addFieldToFilter('product_parent_id', ['eq'=> $data['parent_id']])
        ->addFieldToFilter('product_id', ['eq'=> $data['id']]);

        $header_options = [];
        if (isset($data['header_options']) && (!empty($data['header_options']))) {
                $header_options = $data['header_options'];
                foreach ($header_options as $key => $value) {
                    $resultdata[$key] = $value;
                }
        }
        if (count($filteredCollection->getData()) > 0) {
            foreach ($filteredCollection->getData() as $item) {
                if (isset($data['name'])) {
                    $resultdata['name'] = $data['name']; 
                }
                $resultdata['price'] = $data['price'];
                $resultdata['count_in'] = $item['count_in'];
                $resultdata['add_on'] = $item['add_on'];
                $resultdata['total'] = $item['total'];
                $resultdata['comp'] = $item['comp'];
                $resultdata['count_out'] = $item['count_out'];
                $resultdata['total_sold'] = $item['total_sold'];
                $resultdata['gross_sale'] = $item['gross_sale']; 
                $this->_logger->info('Product price '.$resultdata['price']);
                $this->_logger->info('Product count in '.$resultdata['count_in']);
                $this->_logger->info('Product add on '.$resultdata['add_on']);
                $this->_logger->info('Product total in '.$resultdata['total']);
                $this->_logger->info('Product comp '.$resultdata['comp']);
                $this->_logger->info('Product count out '.$resultdata['count_out']);
                $this->_logger->info('Product total sold '.$resultdata['total_sold']);
                $this->_logger->info('Product gross sale '.$resultdata['gross_sale']);
                $resultdata =  $this->getTaxCalaculation($resultdata, $tax, $cor_category);
            }
        } else {
            if (isset($data['header_options']) && (!empty($data['header_options']))) {
                $header_options = $data['header_options'];
                foreach ($header_options as $key => $value) {
                    $resultdata[$key] = $value;
                }
            }
            if (isset($data['name'])) {
                $resultdata['name'] = $data['name'];
            }
            $resultdata['price'] = $data['price'];
            $resultdata['count_in'] = 0;
            $resultdata['add_on'] = 0;
            $resultdata['total'] = 0;
            $resultdata['comp'] = 0;
            $resultdata['count_out'] = 0;
            $resultdata['total_sold'] = 0;
            $resultdata['gross_sale'] = 0;
            $this->_logger->info('Product price '.$resultdata['price']);
            $this->_logger->info('Product count in '.$resultdata['count_in']);
            $this->_logger->info('Product add on '.$resultdata['add_on']);
            $this->_logger->info('Product total in '.$resultdata['total']);
            $this->_logger->info('Product comp '.$resultdata['comp']);
            $this->_logger->info('Product count out '.$resultdata['count_out']);
            $this->_logger->info('Product total sold '.$resultdata['total_sold']);
            $this->_logger->info('Product gross sale '.$resultdata['gross_sale']);
            $resultdata =  $this->getTaxCalaculation($resultdata, $tax, $cor_category);
        }
        return $resultdata;
    }
    
    /**
    * Method which returns return tax percentage according to category
    * @return integer
    */
    public function getProductTaxDetails($event_id, $cor_category)
    {
        $returnTaxValue = 0;
        $event = $this->getEventDetails($event_id);
        if ( (isset($event['tax_values'])) &&  (!empty($event['tax_values'])) ) 
        {
            $eventTax = json_decode($event['tax_values'], true);
            if (isset($eventTax[$cor_category]) && (!empty($eventTax[$cor_category]))) 
            {
                $returnTaxValue = $eventTax[$cor_category];
            }
        }
        return $returnTaxValue; 
    }

    /**
    * Method which return product data with tax
    * @return array
    */
    public function getTaxCalaculation($resultdata, $tax, $cor_category)
    {
        $total_tax = 0;
        $calculatedTax =  (((int) $resultdata['gross_sale'] * $tax)/100);
        $this->_logger->info('Event tax '.$tax);
        $this->_logger->info('Product calculated tax '.$calculatedTax);
        $resultdata['tax_rate'] = $tax;
        $category = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory')->getCollection();
        foreach ($category->getData() as $categorydata) {
            if ($categorydata['id'] == $cor_category) {
                $resultdata[$categorydata['id']] = $calculatedTax;
                $total_tax = $total_tax + $calculatedTax;
            } else {
                $resultdata[$categorydata['id']] = 0;
                $total_tax = $total_tax + 0;
            }
        }
        $resultdata['total_tax'] = $total_tax;
        $this->_logger->info('Product total tax '.$total_tax);
        return $resultdata;
    }  

    /**
    * Method which return tax category headers
    * @return array
    */
    public function getTaxCategoryHeaders()
    {
        $category = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory')->getCollection();        
        $taxFileHeader[] = 'Price';
        $taxFileHeader[] = 'Total Sold';
        $taxFileHeader[] = 'Gross';
        $taxFileHeader[] = 'Tax Rate';
        foreach ($category->getData() as $categorydata) {
            $taxFileHeader[] = ''.$categorydata['category_name'];
        }
        $taxFileHeader[] = 'Total Tax';
        return $taxFileHeader;
    }

    /**
    * Method which return category ids
    * @return array
    */
    public function getCategoriesIds()
    {
        $storeValue = $this->_objectManager->create('Magento\Framework\App\Config\ScopeConfigInterface');
        $apparel_id = $storeValue->getValue('artist_category/category_id/apparel_tax');
        $music_id = $storeValue->getValue('artist_category/category_id/music_tax');
        $other_id = $storeValue->getValue('artist_category/category_id/other_tax');
        $categoryArray = [
            'apparel'=> $apparel_id,
            'music'=> $music_id,
            'other'=> $other_id
            ];
        return $categoryArray;
    }

    /**
    * Method which return total categories tax
    * @return array
    */
    public function getTotalOfCategories($totalRowTax, $total_category_tax)
    {
        $category = $this->_objectManager->create('Cor\Artistcategory\Model\Artistcategory')->getCollection();
        foreach ($category->getData() as $item) {
           $totalRowTax[] =  $total_category_tax[$item['id']];
           $this->_logger->info('total '.$item['category_name'].' amount '.$total_category_tax[$item['id']]);
        }
        return $totalRowTax;
    }

}
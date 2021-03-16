<?php 
namespace Mangoit\Sellerapi\Model;

use Mangoit\Sellerapi\Api\SellerProductInterface; 
use Magento\Framework\App\RequestInterface;
use Magento\Tax\Model\Calculation\Rate;
use Magento\Framework\Webapi\Exception;

class SellerProduct implements SellerProductInterface {

	protected $resultJsonFactory;
	protected $_block;
	protected $_webkulHelper;
	protected $_storeManager;
	protected $_session;
	protected $_requestInterface;
	protected $_helper;
	protected $_productRepositoryInterface;
    /**
     * @var Rate
     */
    protected $_taxModelConfig;

	/**
     * @var \Webkul\Marketplace\Model\ResourceModel\Product\Collection
     */
    protected $_sellerProduct;

    /**
     * @var \Magento\Framework\Json\EncoderInterface
     */
    protected $jsonEncoder;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var ObjectManagerInterface
     */
    protected $_customerObserver;

    /**
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\Json\DecoderInterface
     */
    protected $_jsonDecoder;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_date;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    protected $_mediaGalleryProcessor;

    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_entityAttribute;

    protected $_urlInterface;
    protected $_directoryList;
    protected $_file;
    protected $_categoryLinkRepository;
    protected $_attributeModel;
    protected $_saveImage;
    protected $_attributeFactory;


	public function __construct(
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Mangoit\Sellerapi\Block\Seller\View $block,
		\Webkul\Marketplace\Helper\Data $webkulHelper,
		\Magento\Store\Model\StoreManagerInterface $storeManager,
		\Magento\Customer\Model\Session $session,
		\Mangoit\Sellerapi\Helper\Data $helper,
        \Mangoit\Sellerapi\Helper\Saveimage $saveImage,
		\Magento\Catalog\Api\ProductRepositoryInterface $productRepositoryInterface,
        \Webkul\Marketplace\Model\ResourceModel\Product\Collection $sellerProduct,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Framework\Registry $coreRegistry,
        \Webkul\Marketplace\Observer\AdminhtmlCustomerSaveAfterObserver $customerObserver,
        \Magento\Framework\Json\DecoderInterface $jsonDecoder,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\Product\Gallery\Processor $mediaGalleryProcessor,
        \Magento\Framework\UrlInterface $urlInterface, 
        \Magento\Framework\App\Filesystem\DirectoryList $directoryList,
        \Magento\Framework\Filesystem\Io\File $file,
        \Magento\Catalog\Api\CategoryLinkManagementInterface $categoryLinkRepository,
        \Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute $attributeModel,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Catalog\Model\ResourceModel\Eav\Attribute $attributeFactory,
        Rate $taxModelConfig,
		RequestInterface $requestInterface
    ) {
        $this->resultJsonFactory = $resultJsonFactory;
        $this->_block = $block;
        $this->_webkulHelper = $webkulHelper;
        $this->_storeManager = $storeManager;
        $this->_session = $session;
        $this->_requestInterface = $requestInterface;
        $this->_productRepositoryInterface = $productRepositoryInterface;
        $this->_helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_sellerProduct = $sellerProduct;
        $this->jsonEncoder = $jsonEncoder;
        $this->_objectManager = $objectManager;
        $this->_coreRegistry = $coreRegistry;
        $this->_customerObserver = $customerObserver;
        $this->_jsonDecoder = $jsonDecoder;
        $this->_date = $date;
        $this->_mediaGalleryProcessor = $mediaGalleryProcessor;
        $this->_urlInterface = $urlInterface;
        $this->_directoryList = $directoryList;
        $this->_file = $file;
        $this->_categoryLinkRepository = $categoryLinkRepository;
        $this->_entityAttribute = $entityAttribute;
        $this->_attributeModel = $attributeModel;
        $this->_saveImage = $saveImage;
        $this->_attributeFactory = $attributeFactory;
        $this->_taxModelConfig = $taxModelConfig;
    }


	/**
	 * {@inheritdoc}
	 */
	public function addSellerProduct($seller_id, \Magento\Catalog\Api\Data\ProductInterface $product) 
	{
        $current_token = $this->getBearerToken();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance(); // Instance of object manager
        $resource = $objectManager->get('Magento\Framework\App\ResourceConnection');
        $connection = $resource->getConnection();
        $tableName = $resource->getTableName('catalog_product_entity_media_gallery_value'); //gives table name with prefix
        /*For validating the attributes*/
        $this->isProductAttributeExistOrNot($product);
        
        /*------------------ Configurable Product code ------------------ */

        if ($product->getTypeId() == 'configurable') {

            /*un-comment from here*/

            /*$productSku = $product->getSku();
            $configurableAttributes = explode(',', $product->getConfigOptionsAttr());
            $configurableChildSku = explode(',', $product->getConfigChildSku());
            foreach ($configurableAttributes as $attribute_code) {
                $json_option = $this->_block->getAttributeData($attribute_code);
                $result = $this->createConfigOptions($productSku, $json_option);
                if ($result) {
                	foreach ($configurableChildSku as $child_sku) {
                		$this->assignChildProducts($productSku, $child_sku);
                	}
                } else {
                	echo "<br> option failed";
                }
            }*/
        }

        /*------------------ Configurable Product code ends ------------------ */
        $is_existing_product = false;
        $is_seller = $this->_block->isSeller($seller_id);
        $customer = $this->_objectManager->create('Magento\Customer\Model\Customer')->load($seller_id); 
        $categoryLinkRepository = $this->_objectManager->create('Magento\Catalog\Model\CategoryLinkRepository');


        $product_array_data = $product->toArray();
        if (isset($product_array_data['custom_attributes']['special_price'])) {
            if ($product_array_data['custom_attributes']['special_price']->getValue() != 0 || $product_array_data['custom_attributes']['special_price']->getValue() !== null) {
                $product->setSpecialPrice(null);
            }
            # code...
        }

       if ($customer->getSellerApiToken() && ($customer->getSellerApiToken() == $current_token)) {
        /*if (true) {*/
            /* Check seller is exist or not.*/
            if ($is_seller) {

                $media_gallary_exist = false;        

                /* If getImage is exist then we will get it's values to a variable after then we will remove that 'getImage' object. */
                $product->setStoreId(0);
                $product_images = '';
                if ($product->getImage() !== null) {
                    $product_images = $product->getImage();
                    $first_img = explode(',', $product_images);
                    /*$thumbnail_path = $this->_saveImage->saveBaseImageToProduct($first_img[0]);
                    $product->setThumbnail($thumbnail_path);*/
                    $media_gallary_exist = true;
                    $product->setImage('');
                }
                $result_data = [];
                $productRepo = $this->_productRepositoryInterface;
                $productRepoExisting = $this->_productRepositoryInterface;

        		try {
                    $product_array_data = $product->toArray(); 
                    /* You need to save product by using the repository. */
                    $collection = $this->_objectManager->create('Magento\Catalog\Model\Product')->getCollection()->addFieldToFilter('sku', array('eq'=> $product->getSku()));
                    $saved_product_id = 0;
                    if (count($collection->getData()) > 0) {
                        foreach ($collection as $item) {
                            $saved_product_id = $item->getEntityId();
                            foreach ($item->getCategoryIds() as $categoryId) {
                                $categoryLinkRepository->deleteByIds($categoryId,$product->getSku());
                            }
                        }

                        $this->updateProductBySku($product->getSku(), $product->debug(), $saved_product_id);
                        
                    } else {
                        $productRepo->save($product);
                    }

                    if ($product->getTypeId() == 'configurable') {
                    	$this->setConfigurationData($product);
                    }

        			$savedProduct = $this->_productRepositoryInterface->get($product->getSku());

        			$productIds = $this->getSellerAssignedProductsJson($seller_id);
        			$productids = array_flip($this->_jsonDecoder->decode($productIds));
        			array_push($productids, $savedProduct->getId());
        			$productids = $this->jsonEncoder->encode(array_flip($productids));
        			$product_ids_array = $productids;
        			try {
        				$result_data = $this->assignProduct($seller_id, $product_ids_array);
                        if (isset($result_data['error'])) {
            				if ($result_data['error'] == 0) {
                                if ($media_gallary_exist) {

                                /* Remove all previously saved images */
                                $delete_value_sql = "SELECT value_id
                                FROM catalog_product_entity_media_gallery_value where entity_id= ".$savedProduct->getId()." AND disabled = 0";
                                $value_id_array = $connection->query($delete_value_sql);

                                $deleteImgSql = "DELETE FROM catalog_product_entity_media_gallery_value WHERE entity_id= ".$savedProduct->getId()." AND disabled= 0";
                                $img_arra = $connection->query($deleteImgSql);
                                if ($value_id_array->rowCount() > 0) {
                                    foreach ($value_id_array->fetchAll() as $key => $value) {
                                        try {
                                            $delete_sql = "DELETE 
                                            FROM catalog_product_entity_media_gallery
                                            WHERE value_id = ".$value['value_id'];
                                            $connection->query($delete_sql);
                                        } catch (Exception $e) {
                                            print_r($e->getMessage());
                                        }
                                    }
                                }
                                /* -- Remove all previously saved images ends -- */

                                /* This will save the image urls into the folders and then it will assign to the product. */
                                    $thumbnail_image = $this->setImageToProduct($product_images, $savedProduct);
                                    $productObject = $this->_objectManager->create('Magento\Catalog\Model\Product')->load($savedProduct->getId());
                                    $productObject->setStoreId(0);
                                    $productObject->setThumbnail($thumbnail_image);
                                    $productObject->setSmallImage($thumbnail_image);
                                    $productObject->setImage($thumbnail_image);
                                    $productObject->save();
                                }

                                if ($product->getTypeId() == 'configurable') {
                                	$deleteSQL =  "DELETE FROM catalog_product_entity_varchar WHERE entity_id = ".$savedProduct->getId()." AND attribute_id IN (87,88,89) AND value = 'no_selection'";
                                	$connection->query($deleteSQL);
                                }
                                
                                if ($is_existing_product == true) {
                                    $result_data['message'] = "Product has been updated and assigned to the seller.";
                                }
            					return ['message'=> $result_data['message']];
            				} else {
            					return ['message'=> $result_data['message']];					
            				}
                            # code...
                        }
        				$productIds = $this->getSellerAssignedProductsJson($seller_id);	
        			} catch (Exception $e) {
        				print_r($e->getMessage());
        			}
        		} catch (Exception $e) {
        			print_r($e->getMessage());
        		}
            } else {
                return ['message'=> 'Seller is not exist. '];       
            }
        } else {
            throw new Exception(__('Please regenerate your authentication token. Your current authentication token has been expired.'));
        }
         
	}

    /* This will set the images to the products.*/
    public function setImageToProduct($product_images, $savedProduct) 
    {
        $tmpdir = $this->_saveImage->getMediaDirTmpDir();
        $thumbnail = '';
        $counter = 0;
        $downloaded_img_array = [];
        if ($product_images !== null) {
            $product_images = explode(',', $product_images);

            foreach ($product_images as $imageUrl) {
                $downloaded_img_array[] = $this->_saveImage->saveImageToProduct($imageUrl);
            }

            if (!empty($downloaded_img_array)) {
                foreach ($downloaded_img_array as $value) {
                    try {
                        $savedProduct->addImageToMediaGallery('tmp/'.$value, array('image','small_image','thumbnail'), false, false);
                        
                    } catch (Exception $e) {
                        print_r($e->getMessage());
                    }
                }
                
                 $savedProduct->save();
            }
        }

        if (count($savedProduct->getMediaGallery()['images']) > 0 ) {
            foreach ($savedProduct->getMediaGallery()['images'] as $item) {
                $thumbnail = $item['file'];
            }
        }

        return $thumbnail;
    } 

    public function isProductAttributeExistOrNot($product)
    {
        $product_repo = $this->_productRepositoryInterface;
        $ignore_validation = ['sku', 'name', 'attribute_set_id', 'price', 'status', 'visibility', 'type_id', 'weight', 'image', 'description', 'tax_class_id', 'special_price', 'special_from_date', 'special_to_date'];
        $product_array = $product->debug(); 
        /* Validate product images */
        if ($product->getImage() !== null) {
            $product_images = explode(',', $product->getImage());
            foreach ($product_images as $value) {
                if (filter_var($value, FILTER_VALIDATE_URL) == false) {
                    throw new Exception(__('Please enter image urls in the "image". For example: "https://pasteboard.co/ImdWr81.png,https://pasteboard.co/ImdWMnM.png" '));
                }
            }
        } else {
            throw new Exception(__('Please enter image urls in the "image". For example: "https://pasteboard.co/ImdWr81.png,https://pasteboard.co/ImdWMnM.png" '));
        }

        /*validate special price */
        if ($product->getSpecialPrice() !== null) {
            if ($product->getSpecialFromDate() == null) {
                throw new Exception(__('Please enter date in "special_from_date". '));
            }
            if ($product->getSpecialToDate() == null) {
                throw new Exception(__('Please enter date in "special_to_date". '));
            }

        }
    }

    public function downloadImages($product_images, $product_id)
    {
        $image_file_array = [];
        $filePath = "/catalog/product/";
        $image_path = $this->_directoryList->getPath('media').$filePath.$product_id;
        foreach ($product_images as $value) {
            $url = $value;
            $image_content = file_get_contents($value);
            $ioAdapter = $this->_file;
            $ioAdapter->mkdir($image_path, 0777);
            if (is_dir($image_path)) {
                try{
                    $fileName = basename($url);
                    $ioAdapter->open(array('path'=>$image_path));
                    $ioAdapter->write($fileName, $image_content, 0777);
                    $image_file_array[] = $fileName;
                } catch(Exception $e) {
                    print_r($e->getMessage());
                }
            } else {
                $product->addImageToMediaGallery($image_directory, null, false, false);
            }

        }
        return ['file_path'=> $image_path, 'file_name'=> $image_file_array];
    }

	/**
     * @return array
     */
    protected function getSellerAssignedProducts($seller_id)
    {
        $products = $this->_sellerProduct->getAllAssignProducts(
            '`adminassign`= 1 AND `seller_id`='.$seller_id
        );

        return $products;
    }

    /**
     * @return string
     */
    public function getSellerAssignedProductsJson($seller_id)
    {
        $products = $this->getSellerAssignedProducts($seller_id);
        if (!empty($products)) {
            return $this->jsonEncoder->encode(array_flip($products));
        }

        return '{}';
    }

    public function assignProduct($sellerId, $productIds)
    {
    	$result_data = [];
        $productids = array_flip($this->_jsonDecoder->decode($productIds));

        // get all assign products
        $assignProductIds = $this->getSellerAssignedProducts($sellerId);
        // set product status to 2 to unassign products from seller

        $coditionArr = [];
        foreach ($assignProductIds as $key => $id) {
            $condition = '`mageproduct_id`='.$id;
            array_push($coditionArr, $condition);
        }

        if (count($coditionArr)) {
            $coditionData = implode(' OR ', $coditionArr);
            $this->_sellerProduct->setProductData(
                $coditionData,
                ['adminassign' => 2]
            );
        }

        // set product status to 1 to assign selected products from seller
        $productCollection = $this->_objectManager->create(
            'Magento\Catalog\Model\Product'
        )->getCollection()
        ->addFieldToFilter(
            'entity_id',
            ['in' => $productids]
        );
        $successMessage = ''; 
        foreach ($productCollection as $product) {
            $proid = $product->getId();
            $userid = '';
            $collection = $this->_objectManager->create(
                'Webkul\Marketplace\Model\Product'
            )->getCollection()
            ->addFieldToFilter(
                'mageproduct_id',
                $proid
            );

            $flag = 1;
            foreach ($collection as $coll) {
                $flag = 0;
                if ($sellerId != $coll['seller_id']) {
                	$result_data['error'] = 1;
                	$result_data['message'] = 'The product with id '.$proid.' is already assigned to other seller.';
                } else {
                    $coll->setAdminassign(1)->save();
                    $result_data['error'] = 0;
                    $result_data['message'] = 'Product has been updated and assigned to the seller.'; 
                }

            }

            if ($flag) {
                $collection1 = $this->_objectManager->create(
                    'Webkul\Marketplace\Model\Product'
                );
                $collection1->setMageproductId($proid);
                $collection1->setSellerId($sellerId);
                $collection1->setStatus($product->getStatus());
                $collection1->setAdminassign(1);
                $collection1->setCreatedAt($this->_date->gmtDate());
                $collection1->setUpdatedAt($this->_date->gmtDate());
                $collection1->save();
                $result_data['error'] = 0;
            	$result_data['message'] = 'Products has been created and successfully assigned to seller.';
            }
        }

        return $result_data;
    }

    public function unassignProduct($sellerId, $productIds)
    {
        $productids = $productIds;
        $collection = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Product'
        )->getCollection()
        ->addFieldToFilter(
            'mageproduct_id',
            ['in' => $productids]
        )
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        foreach ($collection as $coll) {
            $coll->delete();
        }
    }

    /*public function getProductBySku($configurableChildSku ,$configurableAttributes)
    {
        $attributes_array = [];
        $attribute_codes = [];
        $attributes_id = [];
        $product_ids = [];
        if (!empty($configurableChildSku)) {
            foreach ($configurableChildSku as $sku) {
                $childProduct = $this->_productRepositoryInterface->get($sku);
                $product_ids[] = $childProduct->getId();
                if (!empty($configurableAttributes)) {
                    $position = 0;
                    foreach ($configurableAttributes as $attribute_code) {
                        $attributeData = $this->_block->getAttributeInfo($attribute_code);
                        $attribute_id = $attributeData->getAttributeId();
                        $attribute_label = $attributeData->getFrontendLabel();
                        if (!in_array($attribute_code, $attribute_codes)) {
                            $attribute_codes[] = $attribute_code;
                        }
                        if (!in_array($attribute_id, $attributes_id)) {
                            $attributes_id[] = $attribute_id;
                        }
                        $attributes_array[$attribute_id]['attribute_id'] = $attribute_id;
                        $attributes_array[$attribute_id]['code'] = $attribute_code;
                        $attributes_array[$attribute_id]['label'] = $attribute_label;
                        $attributes_array[$attribute_id]['position'] = $position;
                        $attributes_array[$attribute_id][$childProduct->getCustomAttribute($attribute_code)->getValue()] = array('include'=> 1, 'value_index'=> $childProduct->getCustomAttribute($attribute_code)->getValue());
                        $position++;
                    }
                }
                
            }
            
        }
        // return $attributes_array;
        $final_array = [
            'attributes_array'=> $attributes_array,
            'attribute_codes'=> $attribute_codes,
            'attributes_id'=> $attributes_id,
            'product_ids'=> $product_ids
        ];
        return $final_array;
    }*/

    public function getAllActiveTaxClass()
    {
        $taxRates = $this->_taxModelConfig->getCollection()->getData();
        $taxArray = array();
        foreach ($taxRates as $tax) {
            $taxRateId = $tax['tax_calculation_rate_id'];
            $taxCode = $tax["code"];
            $taxRate = $tax["rate"];
            /*$taxName = $taxCode.'('.$taxRate.'%)';*/
            $taxName = $taxCode;
            $taxArray[$taxRateId] = $taxName;
        }
        return $taxArray;
    }

    public function updateProductBySku($sku, $productData, $saved_product_id)
    {
        $product_model = $this->_objectManager->create('Magento\Catalog\Model\Product');  
        $product_set_data_array = [];
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $url = $urlInterface->getBaseUrl();
        $auth_token = $this->getBearerToken();
        $data = json_decode(file_get_contents('php://input'), true);
        foreach ($data['product']['custom_attributes'] as $key => $value) {
            if ($value['attribute_code'] == 'image') {
                unset($data['product']['custom_attributes'][$key]);
            }

            if ($value['attribute_code'] == 'special_price') {
                if ($value['value'] == 0 || $value['value'] === null) {
                   unset($data['product']['custom_attributes'][$key]); 
                   unset($data['product']['custom_attributes']['special_from_date']); 
                   unset($data['product']['custom_attributes']['special_to_date']); 
                }
            }

            if (in_array($value['attribute_code'], ['tax_class_id', 'fsk_product_type', 'restricted_product', 'product_cat_type'])) {
                $product_set_data_array[$value['attribute_code']] = $value['value'];
            }
        }

        try {
            $accessToken =  $auth_token;
            $setHeaders = array('Content-Type:application/json','Authorization:Bearer '.$accessToken);
             
            $url = $url."rest/V1/products/";
            $apiUrl = str_replace(" ","%20",$url);
            $ch = curl_init();
            $data_string = json_encode($data);
            $ch = curl_init();
            curl_setopt($ch,CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json", 
                    "Content-Lenght: " . strlen($data_string), 
                    "Authorization: Bearer ".$accessToken ));
            $token = curl_exec($ch);
            curl_close($ch);

            $product_model->load($saved_product_id);
            $product_model->setFskProductType($product_set_data_array['fsk_product_type']);
            $product_model->setTaxClassId($product_set_data_array['tax_class_id']);
            $product_model->setRestrictedProduct($product_set_data_array['restricted_product']);
            $product_model->setProductCatType($product_set_data_array['product_cat_type']);
            $product_model->save(); 
            return true;
            
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }
    }

    /** 
     * Get header Authorization
     * */
    public function getAuthorizationHeader(){
        $headers = null;
        if (isset($_SERVER['Authorization'])) {
            $headers = trim($_SERVER["Authorization"]);
        }
        else if (isset($_SERVER['HTTP_AUTHORIZATION'])) { //Nginx or fast CGI
            $headers = trim($_SERVER["HTTP_AUTHORIZATION"]);
        } elseif (function_exists('apache_request_headers')) {
            $requestHeaders = apache_request_headers();
            // Server-side fix for bug in old Android versions (a nice side-effect of this fix means we don't care about capitalization for Authorization)
            $requestHeaders = array_combine(array_map('ucwords', array_keys($requestHeaders)), array_values($requestHeaders));
            //print_r($requestHeaders);
            if (isset($requestHeaders['Authorization'])) {
                $headers = trim($requestHeaders['Authorization']);
            }
        }
        return $headers;
    }
    /**
     * get access token from header
     * */
    public function getBearerToken() {
        $headers = $this->getAuthorizationHeader();
        // HEADER: Get the access token from the header
        if (!empty($headers)) {
            if (preg_match('/Bearer\s(\S+)/', $headers, $matches)) {
                return $matches[1];
            }
        }
        return null;
    }

    public function deletePreviousImages($sku, $entry_id)
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $url = $urlInterface->getBaseUrl();
        $auth_token = $this->getBearerToken();

        try {
            $accessToken =  $auth_token;
            $setHeaders = array('Content-Type:application/json','Authorization:Bearer '.$accessToken);
             
            $url = $url."V1/products/".$sku."/media/".$entry_id;
            $apiUrl = str_replace(" ","%20",$url);
            $data_string = json_encode($entry_id);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "DELETE");
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json", 
                    "Content-Lenght: " . strlen($data_string), 
                    "Authorization: Bearer ".$accessToken ));
            $result = curl_exec($ch);
            curl_close($ch);

            return $result;
            
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }
    }

    public function setConfigurationData($product)
    {
    	$productSku = $product->getSku();
        $configurableAttributes = explode(',', $product->getConfigOptionsAttr());
        $configurableChildSku = explode(',', $product->getConfigChildSku());
        foreach ($configurableAttributes as $attribute_code) {
            $json_option = $this->_block->getAttributeData($attribute_code);
            $result = $this->createConfigOptions($productSku, $json_option);
            if ($result) {
            	foreach ($configurableChildSku as $child_sku) {
            		$this->assignChildProducts($productSku, $child_sku);
            	}
            } else {
            	echo "<br> option failed";
            }
        }
    }

    public function createConfigOptions($sku, $option)
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $url = $urlInterface->getBaseUrl();
        $auth_token = $this->getBearerToken();

        try {
            $accessToken =  $auth_token;
            $setHeaders = array('Content-Type:application/json','Authorization:Bearer '.$accessToken);
             
            $url = $url."rest/V1/configurable-products/".$sku."/options";
            $apiUrl = str_replace(" ","%20",$url);
            $data_string = json_encode($option);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json", 
                    "Content-Lenght: " . strlen($data_string), 
                    "Authorization: Bearer ".$accessToken ));
            $result = curl_exec($ch);
            /*if (!curl_errno($ch)) {
			  $info = curl_getinfo($ch);
			  echo 'Took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
			}*/
            curl_close($ch);
            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }
    }

    public function assignChildProducts($sku, $child_sku)
    {
        $urlInterface = \Magento\Framework\App\ObjectManager::getInstance()->get('Magento\Framework\UrlInterface');
        $url = $urlInterface->getBaseUrl();
        $auth_token = $this->getBearerToken();

        try {
            $accessToken =  $auth_token;
            $setHeaders = array('Content-Type:application/json','Authorization:Bearer '.$accessToken);
             
            $url = $url."rest/V1/configurable-products/".$sku."/child";
            /* V1/configurable-products/configurable-16/child */
            $apiUrl = str_replace(" ","%20",$url);
            $data_string = json_encode(['childSku'=> ''.$child_sku]);
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POST, 1);
            curl_setopt($ch, CURLOPT_POSTFIELDS, $data_string);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array(
                    "Content-Type: application/json", 
                    "Content-Lenght: " . strlen($data_string), 
                    "Authorization: Bearer ".$accessToken ));
            $result = curl_exec($ch);
            /*if (!curl_errno($ch)) {

			  $info = curl_getinfo($ch);
			  echo 'Took ', $info['total_time'], ' seconds to send a request to ', $info['url'], "\n";
			}*/
            curl_close($ch);
            return true;
            
        } catch (Exception $e) {
            print_r($e->getMessage());
            return false;
        }
    }
}
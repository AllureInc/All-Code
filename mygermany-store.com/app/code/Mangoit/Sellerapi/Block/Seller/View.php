<?php
namespace Mangoit\Sellerapi\Block\Seller;

use Magento\Framework\UrlInterface;

class View extends \Magento\Framework\View\Element\Template
{
    protected $_session;
    protected $_webkulHelper;
    protected $_tokenModelFactory;
    protected $urlInterface;
    protected $_storeManager;
    protected $_category;
    protected $_customer;
    protected $_logger;
    protected $_customerRepositoryInterface;
    protected $_messageManager;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */
    protected $_entityAttribute;
    /**
     * Eav Entity Attribute Collection
     *
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    protected $_entityAttributeCollection;
     

     
    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection
     */ 
    protected $_attributeOptionCollection;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $webkulHelper,
        \Magento\Integration\Model\Oauth\TokenFactory $tokenModelFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Catalog\Model\Category $category,
        \Magento\Customer\Model\Customer $customer,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection $entityAttributeCollection,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\Collection $attributeOptionCollection,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $session,
        \Magento\Framework\Message\ManagerInterface $messageManager
    )
    {
        $this->_session = $session;
        $this->_webkulHelper = $webkulHelper;
        $this->_tokenModelFactory = $tokenModelFactory;
        $this->urlInterface = $context->getUrlBuilder();
        $this->_storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_category = $category;
        $this->_customer = $customer;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->_entityAttributeCollection = $entityAttributeCollection; 
        $this->_entityAttribute = $entityAttribute;
        $this->_attributeOptionCollection = $attributeOptionCollection;
        $this->_logger = $logger;
        $this->_messageManager = $messageManager;
        parent::__construct($context);
    }

    public function getLoggedInCustomer()
    {
        $data = [];
        if ($this->_webkulHelper->isCustomerLoggedIn()) {
            if ($this->_webkulHelper->isSeller()) {
                $sellerData = $this->_webkulHelper->getSellerData();
                $data['customer_id'] = $this->_webkulHelper->getCustomerId();
                foreach ($sellerData->getData() as $item) {
                    $data['is_seller'] = true;
                    $data['seller_id'] = $item['seller_id'];
                }
            } else {
                $data['is_seller'] = false;
                $data['seller_id'] = null;
            }
        } else {
            $data['is_seller'] = false;
            $data['seller_id'] = null;
            $this->_messageManager->addError("Please login first.");
        }

        return $data;
    }

    /**
     * Get single product attribute data 
     *
     * @return Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection
     */
    public function getProductAttributeByCode($code) {
        $this->_entityAttributeCollection->clear();
        $this->_entityAttributeCollection->getSelect()->join(
                    ['eav_entity_type'=>$this->_entityAttributeCollection->getTable('eav_entity_type')],
                    'main_table.entity_type_id = eav_entity_type.entity_type_id',
                    ['entity_type_code'=>'eav_entity_type.entity_type_code']);                
        
        $attribute = $this->_entityAttributeCollection
                          ->setCodeFilter($code)
                          ->addFieldToFilter('entity_type_code', 'catalog_product')
                          ->getFirstItem();
        
        return $attribute;
    }

    /**
     * Get attribute info by attribute code and entity type
     *
     * @param mixed $entityType can be integer, string, or instance of class Mage\Eav\Model\Entity\Type
     * @param string $attributeCode
     * @return \Magento\Eav\Model\Entity\Attribute
     */
    public function getAttributeInfo($attributeCode)
    {
        $entityType = 'catalog_product';
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $entityAttributeObj = $objectManager->create('Magento\Eav\Model\Entity\Attribute');
        /*echo "<pre>";
        print_r(get_class_methods($entityAttributeObj->loadByCode($entityType, $attributeCode)));
        die();*/
        return $entityAttributeObj->loadByCode($entityType, $attributeCode);
    }

    public function getAttributeData($attribute_code)
    {
        $position = 1;
        /*$attributeData = $this->getAttributeInfo($attribute_code);*/
        
        
        $attributeData = $this->getAttributeInfo($attribute_code);
        $attribute_id = $attributeData->getAttributeId();
        $attribute_label = $attributeData->getFrontendLabel();
        $attribute_pos = $position;
        $attribute_value_index = rand(10,20);
        $position++;
        $array_json = [
            'option'=> [
                "attribute_id"=> "".$attribute_id,
                "label"=> "".$attribute_label,
                "position"=> "".$attribute_pos,
                "is_use_default"=> 0,
                "values"=> [
                    [
                        "value_index"=> $attribute_value_index
                    ]
                ]
            ]
        ];
        // print_r($array_json);
        // die('11111111');
        return $array_json;
    }

    public function getAttributeDataWithChild($attribute_code, $child, $position)
    {
        $attributeData = $this->getAttributeInfo($attribute_code);
        
        
        $attributeData = $this->getAttributeInfo($attribute_code);
        $attribute_id = $attributeData->getAttributeId();
        $attribute_label = $attributeData->getFrontendLabel();
        $attribute_pos = $position;
        $attribute_value_index = $child->getCustomAttribute($attribute_code)->getValue();
        
        $array_json = [
            'option'=> [
                "attribute_id"=> "".$attribute_id,
                "label"=> "".$attribute_label,
                "position"=> "".$attribute_pos,
                "is_use_default"=> true,
                "values"=> [
                    [
                        'value_index'=> (int) $attribute_value_index
                    ]
                ]
            ]
        ];
        // print_r($array_json);
        // die('11111111');
        return $array_json;
    }

    public function runCurlOperation($api_url, $postFieldData)
    {
        $base_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $apiUrl = $base_url."".$api_url;
        try{
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($postFieldData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, 
                CURLOPT_HTTPHEADER, 
                array(
                    "Content-Type: application/json", 
                    "Content-Lenght: " . strlen(json_encode($postFieldData)), 
                    "Authorization: Bearer aem75y1ct9hbh9s88wd40yjdidb86pk8" )
            );
            echo "<br> ".json_encode($postFieldData);
            $result_curl = curl_exec($ch);
            echo "<br> result_curl :";
            print_r($result_curl);
            return true;
        } catch (Exception $e) {
            print_r($e->getMessage());
            echo "<br> exception occured";
        }
    }

    /*public function createOptionForConfigurableProduct($productSku, $data)
    {
        $position = 1;
        echo "<br> block: ";
        print_r($data);
        foreach ($data as $value) {
            $attributeData = $this->getProductAttributeByCode($value);
            $attribute_id = $attributeData->getAttributeId();
            $attribute_label = $attributeData->getFrontendLabel();
            $attribute_pos = $position;
            $attribute_value_index = rand(10,20);
            $position++;
            $array_json = [
                'option'=> [
                    "attribute_id"=> "".$attribute_id,
                    "label"=> $attribute_label,
                    "position"=> $attribute_pos,
                    "is_use_default"=> false,
                    "values"=> [[
                        "value_index"=> $attribute_value_index
                    ]]
                ]
            ];

            print_r(json_encode($array_json));
            die();
            $base_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
            $apiUrl = $base_url."rest/V1/configurable-products/".$productSku."/options";
            try{
                echo "<br> Loop: ".$attribute_pos;
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL, $apiUrl);
                curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($array_json));
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                curl_setopt($ch, 
                    CURLOPT_HTTPHEADER, 
                    array(
                        "Content-Type: application/json", 
                        "Content-Lenght: " . strlen(json_encode($array_json)), 
                        "Authorization: Bearer rgx5lchbh8l6ihxkywva1d5aql353wvh" )
                );

                $result_curl = curl_exec($ch);
                echo "result_curl: ";
                // print_r($result_curl);
            } catch (Exception $e) {
                // print_r($e->getMessage());
            }

            // print_r($attributeData->getData());
        }
    }*/

    public function getAuthorisedTokenKey($customer_id)
    {
        $this->_logger->info("### Seller API Module ##");
        $this->_logger->info("### getAuthorisedTokenKey ##");
        $this->_logger->info("### customer_id: ".$customer_id);
        $base_url = $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $apiUrl = $base_url."rest/V1/integration/admin/token";
        $this->_logger->info("### apiUrl: ".$apiUrl);

        $userData = array("username" => "mygermany_sellers", "password" => "mygermany123");

        try {
            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $apiUrl);
            curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($userData));
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
            curl_setopt($ch, CURLOPT_HTTPHEADER, array("Content-Type: application/json", "Content-Lenght: " . strlen(json_encode($userData))));
             
            $token = curl_exec($ch);
            $this->_logger->info("### token: ".$token);
            
        } catch (Exception $e) {
            $this->_logger->info("### Exception: ".$e->getMessage());
        }

        if (curl_errno($ch)) {
            $error_msg = curl_error($ch);
            
            $this->_logger->info("### error_msg: ".$error_msg);
            return ['error'=> true, 'msg'=> $error_msg];
            exit();
        }


        $data = $this->getLoggedInCustomer();
        $this->_logger->info("### data: ".json_encode($data));
        /*echo str_replace('"','', $token);*/
        /*echo json_decode($token, 1);*/
        if (isset($data['customer_id'])) 
        {
            $customerInterface = $this->_customerRepositoryInterface;
            $customer = $customerInterface->getById($data['customer_id']);
            $customer->setCustomAttribute('seller_api_token', 
                str_replace('"','', $token));
            $this->_logger->info("### Customer token: ".str_replace('"','', $token));
            $this->_customerRepositoryInterface->save($customer);
            $this->_logger->info("### Customer saved ###");
            $this->_logger->info("### customer array: ".json_encode($customer->__toArray()));
            return $this->getSellerAuthorizedToken();
        }

        return json_decode($token, 1);
    }

    public function getSellerAuthorizedToken()
    {
        $data = $this->getLoggedInCustomer();
        if (isset($data['customer_id']))  {
            $customer = $this->_customer->load($data['customer_id']);
            if ($customer->getSellerApiToken()) {
                return $customer->getSellerApiToken();
            } else {
                return '';
            }
        }

    }

    public function getAllowedCategoryIds($seller_id)
    {
        $category_data = [];
        $seller = $this->getSeller($seller_id);
        if ($seller['allowed_categories']) {
            $categoryids = $seller['allowed_categories'];
        } else {
            $categoryids = $this->scopeConfig->getValue(
                'marketplace/product_settings/categoryids',
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
            );
        }

        $categoryIdArray = explode(',', $categoryids);

        /*print_r($categoryids);
        die('..');*/
        /*var_dump(!empty($categoryIdArray));
        echo "<pre>";
        print_r($categoryIdArray);
        die();*/
        if (!empty($categoryIdArray[0])) {
            foreach ($categoryIdArray as $item) {
                $category = $this->_category->load($item);
                $category_data[] = ['category_id'=> $item, 'category_name'=> $category->getName()];
            }
        } else {
            /*echo "here";
            die();*/
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $categoryCollection = $objectManager->create('Magento\Catalog\Model\Category')->getCollection();

            foreach ($categoryCollection->getData() as $category){
                /*print_r($category);entity_id*/
                if (!in_array($category['entity_id'], array(1,2))) {
                    $category = $this->_category->load($category['entity_id']);
                    $category_data[] = ['category_id'=> $category->getId(), 'category_name'=> $category->getName()];
                    # code...
                }
                /*$category_data[] = [
                    'category_id'=> $category['id'], 
                    'category_name'=> $category['name']
                ];*/
            }
        }

        return $category_data;
    }

    public function getSeller($seller_id)
    {
        $data = [];
        $bannerpic = '';
        $logopic = '';
        $countrylogopic = '';
        $sellerId = $seller_id;
        $model = $this->_webkulHelper->getSellerCollectionObj($sellerId);
        foreach ($model as $value) {
            $data = $value->getData();
            $bannerpic = $value->getBannerPic();
            $logopic = $value->getLogoPic();
            $countrylogopic = $value->getCountryPic();
            if (strlen($bannerpic) <= 0) {
                $bannerpic = 'banner-image.png';
            }
            if (strlen($logopic) <= 0) {
                $logopic = 'noimage.png';
            }
            if (strlen($countrylogopic) <= 0) {
                $countrylogopic = '';
            }
        }
        $data['banner_pic'] = $bannerpic;
        $data['logo_pic'] = $logopic;
        $data['country_pic'] = $countrylogopic;

        return $data;
    }

    public function isSeller($customer_id)
    {
        $sellerStatus = 0;
        $model = $this->_webkulHelper->getSellerCollectionObj($customer_id);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }

        return $sellerStatus;
    }
}
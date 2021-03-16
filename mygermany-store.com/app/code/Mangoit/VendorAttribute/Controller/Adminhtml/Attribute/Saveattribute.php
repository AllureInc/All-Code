<?php
namespace Mangoit\VendorAttribute\Controller\Adminhtml\Attribute;

/**
* 
*/
use Magento\Customer\Model\Session;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

class Saveattribute extends \Magento\Backend\App\Action
{
    protected $_date;
    protected $_formKeyValidator;
    protected $_customerSession;
    protected $_objectManager;

    public function __construct(
        Context $context,
        Session $customerSession,
        FormKeyValidator $formKeyValidator,
        PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager
    )
    {
        $this->_objectManager = $objectmanager;
        $this->_customerSession = $customerSession;
        $this->_formKeyValidator = $formKeyValidator;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);      
    }

    /**
    *  @return _customerSession
    */
    protected function _getSession()
    {
        return $this->_customerSession;
    }

    /**
    * Method for check duplicate attribute code
    *  @return boolean
    */
    public function alreadyExist($attrCode)
    {
        $flag = 1;
        $model = $this->_objectManager->create(
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
            );
        $data = $model->getCollection();
        foreach ($data->getData() as $key => $value) {
            if ($attrCode == $value['attribute_code']) {
                $flag = 0;
            }
        }

         return $flag;
    }

    /**
    * Method for removing duplicates options
    *  @return array
    */
    public function sortAllValues($wholedata)
    {
        $adminCount = 0;
        $storeCount = 0;
        $sortedOption = [];
        $adminOptions = [];
        $storeOptions = [];
        foreach ($wholedata['attroptions'] as $key => $value) {
            $adminOptions[$key] = ucwords(strtolower($value['admin']));
            $storeOptions[$key] = ucwords(strtolower($value['store']));
        }

        foreach (array_unique($adminOptions) as $key => $value) {
            $sortedOption [] =  $wholedata['attroptions'][$key];
        }

        $wholedata['attroptions'] = $sortedOption;
        return $wholedata;
    }

    /**
    *  Main Method 
    */
    public function execute()
    {

        $wholedata = $this->getRequest()->getParams();
        /* code for uniqe options of dropdown attribute */
        if (isset($wholedata['attroptions']) && (!empty($wholedata['attroptions']))) 
        {
            $wholedata = $this->sortAllValues($wholedata);          
        }

        /* check attribute code is already exist or not */
        $returnFlag = $this->alreadyExist($wholedata['attribute_code']);
        // $returnFlag = 1;

        if ($returnFlag == 0) { // For validation$
            $this->messageManager->addError(
                __('Attribute Code already exists')
            );
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
  
        // step 2
        /* No need to edit this code from here */
        $attributes = $this->_objectManager->get('Magento\Catalog\Model\Product')->getAttributes();
        // var_dump($attributes);
        $allattrcodes = [];

        foreach ($attributes as $a) {
            $allattrcodes[] = $a->getEntityType()->getAttributeCodes();
        }

        if (!empty($allattrcodes)
            && in_array($wholedata['attribute_code'], $allattrcodes)
        ) {
            $this->messageManager->addError(
                __('Attribute Code already exists')
            );
            // die('already exist');
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        } else {
            if (array_key_exists('attroptions', $wholedata)) {
                foreach ($wholedata['attroptions'] as $c) {
                    $data1['.'.$c['admin'].'.'] = [$c['admin'],$c['store']];
                }
            } else {
                $data1 = [];
            }
            if (empty($wholedata['attribute_code'])) {
                $wholedata['attribute_code'] = $this->generateAttrCode(
                    $wholedata['attribute_label']
                );
            }
            if (!empty($wholedata['attribute_code'])) {
                $validatorRegx = new \Zend_Validate_Regex(
                    ['pattern' => '/^[a-z][a-z_0-9]{0,30}$/']
                );
                if (!$validatorRegx->isValid($wholedata['attribute_code'])) {
                    $this->messageManager->addError(
                        __(
                            'Attribute code "%1" is invalid. Please use only letters (a-z), ' .
                            'numbers (0-9) or underscore(_) in this field, first character should be a letter.',
                            $wholedata['attribute_code']
                        )
                    );
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );
                }
            }
            $attributeData = [
                            'attribute_code' => $wholedata['attribute_code'],
                            'is_global' => '1',
                            'frontend_input' => $wholedata['frontend_input'],
                            'default_value_text' => '',
                            'default_value_yesno' => '0',
                            'default_value_date' => '',
                            'default_value_textarea' => '',
                            'is_unique' => '0',
                            'is_required' => $wholedata['val_required'],
                            'apply_to' => '0',
                            'is_configurable' => '1',
                            'is_searchable' => '1',
                            'is_visible_in_advanced_search' => '1',
                            'is_comparable' => '1',
                            'is_used_for_price_rules' => '0',
                            'is_wysiwyg_enabled' => '0',
                            'is_html_allowed_on_front' => '1',
                            'is_visible_on_front' => '1',
                            'used_in_product_listing' => '1',
                            'used_for_sort_by' => '0',
                            'frontend_label' => [$wholedata['attribute_label']],
                            'is_used_in_grid'=> '1',
                            'is_filterable_in_grid'=> '1',
            ];
            $model = $this->_objectManager->create(
                'Magento\Catalog\Model\ResourceModel\Eav\Attribute'
            );

            if (!isset($attributeData['is_configurable'])) {
                $attributeData['is_configurable'] = 1;
            }
            if (!isset($attributeData['is_filterable'])) {
                $attributeData['is_filterable'] = 1;
            }
            if (!isset($attributeData['is_filterable_in_search'])) {
                $attributeData['is_filterable_in_search'] = 1;
            }
            if (($model->getIsUserDefined()===null) || $model->getIsUserDefined() != 0) {
                $attributeData['backend_type'] = $model->getBackendTypeByInput(
                    $attributeData['frontend_input']
                );
            }
            $defaultValueField = $model->getDefaultValueByInput(
                $attributeData['frontend_input']
            );
            if ($defaultValueField) {
                $attributeData['default_value'] = $this->getRequest()->getParam(
                    $defaultValueField
                );
            }
            $model->addData($attributeData);
            $data['option']['value'] = $data1;
            if ($wholedata['frontend_input'] == 'select') {
                $model->addData($data);
            }
            if ($wholedata['frontend_input'] == 'multiselect') {
                            $model->addData($data);
                            $model->setBackendModel('Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend');
                        }
            $entityTypeID = $this->_objectManager->create(
                 'Magento\Eav\Model\Entity'
            )
            ->setType('catalog_product')
            ->getTypeId();
            $model->setEntityTypeId($entityTypeID);
            $model->setIsUserDefined(1);

            $model->save(); // un-comment this line

            /* No need to edit this code till here */
            
            /* model of vendor attribute table */
            $newdata = $this->_objectManager->create('Mangoit\VendorAttribute\Model\Attributemodel');

            $helperData = $this->_objectManager->create('Mangoit\VendorAttribute\Helper\Data');
            $emailHelper = $this->_objectManager->create('Mangoit\VendorAttribute\Helper\Data');
            $blockData = $this->_objectManager->create('Mangoit\VendorAttribute\Block\Adminhtml\Makeglobal');

            $customerId = 0;
            $dropdownValues = [];
            $today = date("Y-m-d H:i:s");
            $newdata->setVendorId($customerId);
            $newdata->setAttributeId($model->getAttributeId());
            $attrId = $model->getAttributeId();
            $newdata->setAttributeCode($wholedata['attribute_code']);
            $newdata->setAttributeLabel($wholedata['attribute_label']);
            $newdata->setAttributeType($wholedata['frontend_input']);  
            $newdata->setAssociatedAttribute($wholedata['attribute_ids']);
            //********** Code for values of previous attribute ***********

            $attrIdsArray = explode(',', $wholedata['attribute_ids']);
            $attrCodesArray = explode(',', $wholedata['attribute_codes']);

            // $dropdownValues which contains all label and values of new attr.
            foreach ($attrCodesArray as $key => $value) {  
                $attrPrevOption = $blockData->getAttributeCollection($value);
                foreach ($attrPrevOption as $optKey => $optValue) {
                    if ($optKey > 0) {
                        array_push($dropdownValues, array('label' => $optValue['label'], 'value' => $optValue['value']));
                    }
                }

            }
            $newdata->setCreatedAt($today);
            
            $newdata->save(); // un-comment this line

            $customerModel =  $this->_objectManager->create('Magento\Customer\Model\Customer');           

            foreach ($attrIdsArray as $key => $value) {
                $newdata->load($value, 'attribute_id');
                $newdata->setIsVisible('0');
                $newdata->setParantAttributeId($attrId); 
                
                $newdata->save(); // un-comment this line
            }

            $attributeCode = $wholedata['attribute_code'];
            $attributeGroupCode = 'general';

            $helperData->addAttributeToAllAttributeSets($attributeCode,$attributeGroupCode);

            $counter = 0;
            $productModel = $this->_objectManager->create('\Magento\Catalog\Model\Product');

            $valueArray = [];

            $productCollectionFactory = $this->_objectManager->create('\Magento\Catalog\Model\ResourceModel\Product\CollectionFactory');
            $productCollection = $productCollectionFactory->create()->addAttributeToSelect('*');
          
            $eavSetupFactory = $this->_objectManager->create('Magento\Eav\Setup\EavSetupFactory');
            $dataSetup = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
            $eavSetup = $eavSetupFactory->create(['setup' => $dataSetup]);

            $productIds = array();
            $seasonAttributeOptions = array();
            $manufacturerAttributeOptions = array();
            // die('........');          

            $productModel = $this->_objectManager->create('\Magento\Catalog\Model\Product'); // For load product

            /* This will return true if any product have previous attribute */
            /*
            * $attrCodesArray will contains all the selected attributes
            * $newdata will contain the saved new attribute of vrndor_attribute table
            */
            $flag = $this->checkAttrInProducts($attrCodesArray, $productCollection, $newdata);
            // die(' ....flag....');
            try {
                if ($flag == true) {
                    /* This will replace previous attribute with new one */
                    $this->changeAttributeOfConfigurableProduct($flag, $attrCodesArray, $productCollection, $wholedata['attribute_code'], $newdata, $eavSetup);

                    /* For Selected Attribute which we are making global */
                    foreach ($attrCodesArray as $key => $value) { 
                        /* For checking in which product current attribute has value  */
                        foreach ($productCollection as $product) { 

                            $productId = $product->getEntityId();
                            if ($product['type_id'] != 'configurable') {                            
                                $productModel->load($productId);   
                                /* value is attribute code */                             
                                if ($productModel->getCustomAttribute($value)) {
                                    $attrValue = $product->getCustomAttribute($value)->getValue();
                                    /* get associated attribute value of product */
                                    array_push($valueArray,  array($productId => $attrValue));
                                    if ($wholedata['frontend_input'] == 'select') {
                                        /* This Function will return array of label and values of new Attribute of dropdown type */
                                        
                                        $setAttrValue = $this->getAttributeLabel($attrValue, $dropdownValues, $wholedata['attribute_code'], $blockData);

                                        $product->setData($wholedata['attribute_code'],$setAttrValue)->save();  // For set values in new attr            
                                    } elseif ($wholedata['frontend_input'] == 'multiselect') {
                                        
                                            $setAttrValue = $this->getSeletedAttributesValues($attrValue, $dropdownValues, $wholedata['attribute_code'], $blockData);
                                            $product->setData($wholedata['attribute_code'],$setAttrValue)->save();              
                                        
                                    }else {
                                        $product->setData($wholedata['attribute_code'],$attrValue)->save();
                                    }

                                    $product->setCustomAttribute($wholedata['attribute_code'], $attrValue);
                                   
                                }
                               
                            }                       
                            $productModel->unsetData();
            
                        }

                        $counter++;
                        $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $value);

                    } 
                } else {
                    foreach ($attrCodesArray as $key => $value) { // For Selected Attribute which we are making global
                        foreach ($productCollection as $product) { // For checking in which product current attribute has value 
                            if ($product['type_id'] != 'configurable') {
                                $productId = $product->getEntityId();
                                $productModel->load($productId);                       
                                if ($productModel->getCustomAttribute($value)) {
                                    $attrValue = $product->getCustomAttribute($value)->getValue();
                                    array_push($valueArray,  array($productId => $attrValue));
                                    if ($wholedata['frontend_input'] == 'select') {
                                        $setAttrValue = $this->getAttributeLabel($attrValue, $dropdownValues, $wholedata['attribute_code'], $blockData);

                                        $product->setData($wholedata['attribute_code'],$setAttrValue)->save();  // For set values in new attr            
                                    } elseif ($wholedata['frontend_input'] == 'multiselect') {
                                        
                                            $setAttrValue = $this->getSeletedAttributesValues($attrValue, $dropdownValues, $wholedata['attribute_code'], $blockData);
                                            $product->setData($wholedata['attribute_code'],$setAttrValue)->save();              
                                        
                                    }else {
                                        $product->setData($wholedata['attribute_code'],$attrValue)->save();
                                    }
                                    $product->setCustomAttribute($wholedata['attribute_code'], $attrValue);
                                }
                                $productModel->unsetData();
                            }
                        }
                    $eavSetup->removeAttribute(\Magento\Catalog\Model\Product::ENTITY, $value);

                    }

                }
                
            } catch (Exception $e) {
                $this->messageManager->addError(
                    __('Something went wrong.')
                );
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
                    ['_secure' => $this->getRequest()->isSecure()]
                );                
            }
            try {
                
                $this->sendEmail($attrIdsArray, $newdata, $customerModel, $emailHelper, $wholedata);
            } catch (Exception $e) {
                 $this->messageManager->addError(
                    __('email not send.')
                );
                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/',
                    ['_secure' => $this->getRequest()->isSecure()]
                ); 
            }

            $this->messageManager->addSuccess(
                __('Attribute Created Successfully')
            );
            return $this->resultRedirectFactory->create()->setPath(
                '*/*/',
                ['_secure' => $this->getRequest()->isSecure()]
            );

        }

    }



    public function changeAttributeOfConfigurableProduct($flag, $attrCodesArray, $productCollection, $oldAttributeCode, $newdata,$eavSetup)
    {
                foreach ($attrCodesArray as $key => $value) { // For Selected Attribute which we are making global
                    foreach ($productCollection as $product) { // For checking in which product current attribute has value 
                        
                        $productId = $product->getEntityId();
                        if ($product['type_id'] == 'configurable') {
                            $hasData = false; 
                            $eavModel = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute');
                            $eavModel->load($oldAttributeCode, 'attribute_code');
                            $newAttributeId = $eavModel->getAttributeId();
                            echo "<br> newAttributeId ".$newAttributeId;
                            $configAttributeModel = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
                            $newConfigAttributeModel = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
                            $hasData = $this->checkConfigProductHasAttr($productId, $newdata, $value);
                            // var_dump($hasData);
                            if ($hasData) {
                                /**
                                * This will update the attribute id of super attribute of configurable product attribute table
                                *
                                */
                                $newdata->load($value, 'attribute_code');
                                $previosAttrId = $newdata->getAttributeId();
                                // echo "<br> previosAttrId : ".$previosAttrId;
                                $collection = $configAttributeModel->getCollection();
                                foreach ($collection->getData() as $item) {
                                    if ($previosAttrId == $item['attribute_id']) {
                                        $newConfigAttributeModel->load($item['product_id'], 'product_id');
                                        $newConfigAttributeModel->setAttributeId($newAttributeId);
                                        $newConfigAttributeModel->save();
                                        $newConfigAttributeModel->unsetData();
                                    }
                                }
                                $configAttributeModel->unsetData();
                                $newdata->unsetData();
                            }
                            $eavModel->unsetData();

                        }                               
                    }
                } 
 
    }

    public function checkConfigProductHasAttr($productId, $newdata, $value)
    {
        $configAttributeModel = $this->_objectManager->create('Magento\ConfigurableProduct\Model\Product\Type\Configurable\Attribute');
        $configAttributeModel->load($productId, 'product_id');
        $newdata->load($value, 'attribute_code');
        if ($newdata->getAttributeId() == $configAttributeModel->getAttributeId() ) {
            $newdata->unsetData();
            return true;
        } else {
            $newdata->unsetData();
            return false; 
        }
        $configAttributeModel->unsetData();
        
    }

    public function checkAttrInProducts($attrCodesArray, $productCollection, $newdata)
    {
        $flag = false;
        foreach ($attrCodesArray as $key => $value) { 
                foreach ($productCollection as $product) { 
                    $productId = $product->getEntityId();

                    if ($product['type_id'] == 'configurable') {
                            $flag = $this->checkConfigProductHasAttr($productId, $newdata, $value);
                            if ($flag == true) {
                                break;
                            }
                    }
            }
        }
        return $flag;

    }

    public function sendEmail($attrIdsArray, $newdata, $customerModel, $emailHelper, $wholedata)
    {
        // This function is using for send Email.
        foreach ($attrIdsArray as $value) {
            $newdata->load($value, 'attribute_id');
            $customerModel->load($newdata->getVendorId());
            $vendorName = $customerModel->getFirstname();
            $vendorEmail = $customerModel->getEmail();
            $oldAttr = $newdata->getAttributeLabel();
            $newAttr = $wholedata['attribute_label'];

            $sellerStoreId = $customerModel->getCreatedIn();

            if ($sellerStoreId == 'Germany') {
                $sellerStoreId = 7;
            } else {
                $sellerStoreId = 1;
            }

            $emailHelper->sendEmailToVendor($vendorName, $vendorEmail, $oldAttr, $newAttr,$sellerStoreId);
        }
    }

    public function getAttributeLabel($attrValue, $dropdownValues, $attributeCodeNew, $blockData)
    {
        // This Function will change the label and attribute value of product by comparing the old attribute lable with new attribute lable. After comparing we get that value and set it to the product.

        $attrNewLable = "";
        $attrNewValue = 0;
        $attrOptNew = $blockData->getAttributeCollection($attributeCodeNew);

        foreach ($dropdownValues as $key => $value) {
            if ($value['value'] == $attrValue) {
                $attrNewLable = $value['label'];
            }
        }

        foreach ($attrOptNew as $key => $value) {
           if (strtolower($value['label']) == strtolower($attrNewLable)) {
               $attrNewValue = $value['value'];
           }
        }

        return $attrNewValue;
    }

    public function getSeletedAttributesValues($attrValue, $dropdownValues, $attributeCodeNew, $blockData)
    {
        $labelArray = [];
        $valueNewArray = [];
        $attrOptNew = $blockData->getAttributeCollection($attributeCodeNew);
        $attrCodes = explode(',', $attrValue);
        foreach ($attrCodes as $key => $value) {
            foreach ($dropdownValues as $newkey => $newvalue) {
                /*if ($newvalue['value'] == $value) {
                    array_push($labelArray, $newvalue['label']);
                }*/
                if ($newvalue['value'] == $value) {
                    array_push($labelArray, $newvalue['label']);
                }
            }
        }
        
        foreach ($labelArray as $key => $value) {
            foreach ($attrOptNew as $optkey => $optvalue) {
                /*if($optvalue['label'] == $value){
                    array_push($valueNewArray, $optvalue['value']);
                }*/
                if(strtolower($optvalue['label']) == strtolower($value))
                {
                    array_push($valueNewArray, $optvalue['value']);
                }
            }
        }
        $returnValues = implode(',', $valueNewArray);
        return $returnValues; 
    }

    public function generateAttrCode($attributeLabel)
    {
        $attributeLabelFormatUrlKey = $this->_objectManager->create(
            'Magento\Catalog\Model\Product\Url'
        )->formatUrlKey($attributeLabel);
        $attributeCode = substr(
            preg_replace('/[^a-z_0-9]/', '_', $attributeLabelFormatUrlKey),
            0,
            30
        );
        $validatorAttrCode = new \Zend_Validate_Regex(
            ['pattern' => '/^[a-z][a-z_0-9]{0,29}[a-z0-9]$/']
        );
        if (!$validatorAttrCode->isValid($attributeCode)) {
            $attributeCode = 'attr_' . ($attributeCode ?: substr(md5(time()), 0, 8));
        }
        return $attributeCode;
    }


}
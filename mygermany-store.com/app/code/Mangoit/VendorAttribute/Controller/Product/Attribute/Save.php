<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Mangoit\VendorAttribute\Controller\Product\Attribute;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Marketplace Product Attribute Save controller.
 */
class Save extends \Webkul\Marketplace\Controller\Product\Attribute\Save
{
    protected $_logger;

    public function execute()
    {
        $this->_logger = $this->_objectManager->create('Psr\Log\LoggerInterface');
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $isPartner = $helper->isSeller();
        $this->_logger->info('####### Seller Attribute Log #########');
        if ($isPartner == 1) {

            try {
                if ($this->getRequest()->isPost()) {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/new',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $wholedata = $this->getRequest()->getParams();
            
                    $this->isAttributeCodeAlreadyExist($wholedata);
                    if (isset($wholedata['attribute_id'])) {
                        $model = $this->_objectManager->create('Magento\Catalog\Model\ResourceModel\Eav\Attribute')->load($wholedata['attribute_id']);
                        $model->setFrontendLabel($wholedata['attribute_label']);
                        $model->save();
                        
                        $attributeModel = $this->_objectManager->create('Mangoit\VendorAttribute\Model\Attributemodel')->load($wholedata['attribute_id'],'attribute_id');
                        $attributeModel->setAttributeLabel($wholedata['attribute_label']);
                        $attributeModel->save();
                        if($wholedata['attribute_type'] == 'select' || $wholedata['attribute_type'] == 'multiselect'){
                            $eavConfig = $this->_objectManager->create('\Magento\Eav\Model\Config');
                            $attribute = $eavConfig->getAttribute('catalog_product', $wholedata['attribute_code']);
                            $options = $attribute->getSource()->getAllOptions();
                            $optionArray = [];
                            foreach ($wholedata['attroptions'] as $key => $value) {
                                $wholedata['attroptions'][$key] = ucwords(strtolower($value));
                            }
                            $optionArray['attribute_id'] = $wholedata['attribute_id'];
                            $existedArray = array_column($options, 'value');

                           
                            if (isset($wholedata['attroptions']) && (!empty($wholedata['attroptions']))) {
                                $attroptions = $wholedata['attroptions'];
                                $storeRepoObj = $this->_objectManager->create('\Magento\Store\Model\StoreRepository');
                                $stores = $storeRepoObj->getList();                        
                                foreach ($attroptions as $key => $value) {
                                    if (strlen(trim($value)) > 0) {
                                        foreach($stores as $store){
                                            if (in_array($key, $existedArray)) {
                                                $optionArray['value'][$key][$store->getId()] = $value;
                                            } else {
                                                $optionArray['value'][$value][$store->getId()] = $value;
                                            }
                                        }
                                    }

                                }
                            }
                            $eavSetup = $this->_objectManager->create('\Magento\Eav\Setup\EavSetupFactory')->create();
                            $eavSetup->addAttributeOption($optionArray);

                            $setup = $attributeModel = $this->_objectManager->create('Magento\Framework\Setup\ModuleDataSetupInterface');
                            $optionTable = $setup->getTable('eav_attribute_option');
                            if (isset($wholedata['deleted_options']) && (!empty($wholedata['deleted_options']))) {
                                $explodedOptToDel = explode(',', $wholedata['deleted_options']);
                                foreach ($explodedOptToDel as $deletedKey => $deletedValue) {
                                    $intOptionId = (int)$deletedValue;
                                    $condition = ['option_id =?' => $intOptionId];
                                    $setup->getConnection()->delete($optionTable, $condition);
                                }
                            }

                        }
                        $this->messageManager->addSuccess(
                                __('Attribute Edited Successfully')
                            );
                        exit(0);
                        // return $this->resultRedirectFactory->create()->setPath(
                        // '*/*/new',
                        // ['_secure' => $this->getRequest()->isSecure()]);
                    } else {
                        // $imgdata = $this->getRequest()->getFiles();
                        if (isset($wholedata['frontend_input'])) {
                            if ( ($wholedata['frontend_input'] == '0') || ($wholedata['frontend_input'] == '') ) {
                                $this->messageManager->addError(
                                    __('Please select catalog input type.')
                                );
                                return $this->resultRedirectFactory->create()->setPath(
                                    '*/*/new',
                                    ['_secure' => $this->getRequest()->isSecure()]
                                );
                            }                    
                        }
                        $attributes = $this->_objectManager->get(
                            'Magento\Catalog\Model\Product'
                        )->getAttributes();
                        $allattrcodes = [];

                        foreach ($attributes as $a) {
                            $allattrcodes[] = $a->getEntityType()->getAttributeCodes();
                        }

                        $this->_logger->info('####### Seller Attribute Log 2: allattrcodes: '.json_encode($allattrcodes));

                        if (!empty($allattrcodes)
                            && in_array($wholedata['attribute_code'], $allattrcodes)
                        ) {
                            $this->messageManager->addError(
                                __('Attribute Code already exists')
                            );

                            return $this->resultRedirectFactory->create()->setPath(
                                '*/*/new',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        } else {
                            if (array_key_exists('attroptions', $wholedata)) {
                                foreach ($wholedata['attroptions'] as $c) {
                                    $data1['.'.$c['admin'].'.'] = [$c['admin']];
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
                                        '*/*/new',
                                        ['_secure' => $this->getRequest()->isSecure()]
                                    );
                                }
                            }


                            $attributeData = [
                                'attribute_code' => $wholedata['attribute_code'],
                                'attribute_set' => 'Default',
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

                            // $data['option']['value'] = $data1; // Old code which is used to add options
                            $data['option']['value'] = $this->uppercaseAllOptions($data1); // new code for add options


                            // $attributeData['backend_type'] = 'varchar';
                            //$attributeData['backend_model'] = 'Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend';
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

                            $model->save();
                                $helperData = $this->_objectManager->create('Mangoit\VendorAttribute\Helper\Data');                        
                                $attributeCode = $wholedata['attribute_code'];
                                $attributeGroupCode = 'general';
                                $helperData->addAttributeToAllAttributeSets($attributeCode,$attributeGroupCode);
                                
                            $newdata = $this->_objectManager->create('Mangoit\VendorAttribute\Model\Attributemodel');
                            $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
                            $customerId = $customerSession->getCustomer()->getId();
                            $today = date("Y-m-d H:i:s");
                            $newdata->setVendorId($customerId);
                            $newdata->setAttributeId($model->getAttributeId());
                            $newdata->setAttributeCode($wholedata['attribute_code']);
                            $newdata->setAttributeType($wholedata['frontend_input']);
                            $newdata->setAttributeLabel($wholedata['attribute_label']);
                            $newdata->setCreatedAt($today);
                            $newdata->save();
                            $this->messageManager->addSuccess(
                                __('Attribute Created Successfully')
                            );

                            return $this->resultRedirectFactory->create()->setPath(
                                '*/*/new',
                                ['_secure' => $this->getRequest()->isSecure()]
                            );
                        }
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/new',
                        ['_secure' => $this->getRequest()->isSecure()]
                    );

                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) { 
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/new',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());

                return $this->resultRedirectFactory->create()->setPath(
                    '*/*/new',
                    ['_secure' => $this->getRequest()->isSecure()]
                );
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function uppercaseAllOptions($data)
    {
        $newOptionsData = array();
        foreach ($data as $key => $value) {
            $newOptionsData[$key][] = ucwords(strtolower($value[0]));
        }
        return $newOptionsData;
    }
    
    /**
     * Method for check attribute code is already exist or not
     * @return boolean
     */
    public function isAttributeCodeAlreadyExist($wholedata)
    {
        $attributes = $this->_objectManager->get('Magento\Catalog\Model\Product')->getAttributes();
        $allattrcodes = [];
        foreach ($attributes as $a) {
            $allattrcodes[] = $a->getEntityType()->getAttributeCodes();
        }

        $this->_logger->info('####### Seller Attribute Log : allattrcodes: '.json_encode($allattrcodes));

        if (!empty($allattrcodes)) {
            if (!empty($allattrcodes) && in_array($wholedata['attribute_code'], $allattrcodes)) {
                $this->messageManager->addError( __('Attribute Code already exists'));
                return $this->resultRedirectFactory->create()->setPath('*/*/new',
                                    ['_secure' => $this->getRequest()->isSecure()]
                                );
            }
        }
    }
}


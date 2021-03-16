<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 */


namespace Mangoit\Marketplace\Controller\Attribute;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;
use Magento\Framework\View\Result\PageFactory;

/**
 * Webkul Marketplace Product Attribute Save controller.
 */
class Save extends \Magento\Customer\Controller\AbstractAccount
{

    /**
     * @return \Magento\Framework\Controller\Result\RedirectFactory
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $validator = $this->_objectManager->create(
            'Magento\Framework\Data\Form\FormKey\Validator'
        );
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {
            try {
                if ($this->getRequest()->isPost()) {
                    if (!$validator->validate($this->getRequest())) {
                        die('line40');
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/new',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }

                    $wholedata = $this->getRequest()->getParams();
                    // $imgdata = $this->getRequest()->getFiles();
                    if ( ($wholedata['frontend_input'] == '0') || ($wholedata['frontend_input'] == '') ) {
                        $this->messageManager->addError(
                            __('Please select catalog input type.')
                        );
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/new',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }
                    $attributes = $this->_objectManager->get(
                        'Magento\Catalog\Model\Product'
                    )->getAttributes();
                    $allattrcodes = [];

                    foreach ($attributes as $a) {
                        $allattrcodes = $a->getEntityType()->getAttributeCodes();
                    }
                    if (!empty($allattrcodes) && in_array($wholedata['attribute_code'], $allattrcodes)
                    ) {
                        $this->messageManager->addError(
                            __('Attribute Code already exists')
                        );
                        echo 'exist';
                        exit;
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
                                echo false;
                                exit();
                                die('line98');
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
                        echo json_encode(['success'=> 'successfully save!']);
                        exit;
                    }
                }
               
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                echo json_encode(['error'=> $e->getMessage()]);
                exit;
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                echo json_encode(['error'=> $e->getMessage()]);
                exit;
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
    
    /**
     * Generate attribute code from attribute label
     *
     * @param string $attributeLabel
     * @return string
     */
    protected function generateAttrCode($attributeLabel)
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

<?php
/**
 * Mangoit Software.
 *
 * @category  Mangoit
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit Software Private Limited
 */

namespace Mangoit\Marketplace\Controller\Product;

use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Data\Form\FormKey\Validator as FormKeyValidator;

/**
 * Mangoit Marketplace Product Save Controller.
 */
class Save extends \Webkul\Marketplace\Controller\Product\Save
{
    /**
     * seller product save action.
     *
     * @return \Magento\Framework\Controller\Result\RedirectFactory $this->makeRestricted($categoies, $productId, $wholedata);
     */
    public function execute()
    {
        $helper = $this->_objectManager->create(
            'Webkul\Marketplace\Helper\Data'
        );
        $onlyAlphaNumeric = '/^[a-zA-Z0-9 ]+$/';
        $isPartner = $helper->isSeller();
        if ($isPartner == 1) {

            $productId = $this->getRequest()->getParam('id');
            $wholedata = $this->getRequest()->getParams();      
            try {
                $returnArr = [];
                if ($this->getRequest()->isPost()) {
                    if (!$this->_formKeyValidator->validate($this->getRequest())) {
                        return $this->resultRedirectFactory->create()->setPath(
                            '*/*/create',
                            ['_secure' => $this->getRequest()->isSecure()]
                        );
                    }


                    if (isset($wholedata['custom_field_value'])) {
                        if ($wholedata['hasValue'] == 1) {
                           
                            foreach ($wholedata['custom_field_value'] as $key => $value) {
                                if (strlen(trim($value['label_name'])) == 0 ) {
                                    $this->messageManager->addError(__('custom field label cannot be empty.'));
                                    if ($productId) {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/edit',
                                            [
                                                'id' => $productId,
                                                '_secure' => $this->getRequest()->isSecure(),
                                            ]
                                        );
                                    } else {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/add',
                                            [
                                                'set' => $wholedata['set'],
                                                'type' => $wholedata['type'],
                                                '_secure' => $this->getRequest()->isSecure()
                                            ]
                                        );
                                    }

                                } else if (!preg_match($onlyAlphaNumeric, trim($value['label_name']))) {
                                    $this->messageManager->addError(__('only alphanumeric characters allowed in label name.'));
                                    if ($productId) {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/edit',
                                            [
                                                'id' => $productId,
                                                '_secure' => $this->getRequest()->isSecure(),
                                            ]
                                        );
                                    } else {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/add',
                                            [
                                                'set' => $wholedata['set'],
                                                'type' => $wholedata['type'],
                                                '_secure' => $this->getRequest()->isSecure()
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    } else {
                        if ($productId) {
                            if ($wholedata['deletValue'] <= 0) {
                               $product = $wholedata['product_id'];
                               //$this->deleteCustomfields($product); 
                            }
                        }
                    }         
                    //FAQ save Start  
                    if (isset($wholedata['faq_fields'])) {
                        if ($wholedata['hasValue'] == 1) {
                           
                            foreach ($wholedata['faq_fields'] as $key => $value) {
                                if (strlen(trim($value['title'])) == 0 ) {
                                    $this->messageManager->addError(__('custom field label cannot be empty.'));
                                    if ($productId) {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/edit',
                                            [
                                                'id' => $productId,
                                                '_secure' => $this->getRequest()->isSecure(),
                                            ]
                                        );
                                    } else {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/add',
                                            [
                                                'set' => $wholedata['set'],
                                                'type' => $wholedata['type'],
                                                '_secure' => $this->getRequest()->isSecure()
                                            ]
                                        );
                                    }

                                } else if (!preg_match($onlyAlphaNumeric, trim($value['title']))) {
                                    $this->messageManager->addError(__('only alphanumeric characters allowed in label name.'));
                                    if ($productId) {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/edit',
                                            [
                                                'id' => $productId,
                                                '_secure' => $this->getRequest()->isSecure(),
                                            ]
                                        );
                                    } else {
                                        return $this->resultRedirectFactory->create()->setPath(
                                            '*/*/add',
                                            [
                                                'set' => $wholedata['set'],
                                                'type' => $wholedata['type'],
                                                '_secure' => $this->getRequest()->isSecure()
                                            ]
                                        );
                                    }
                                }
                            }
                        }
                    } else {
                        if ($productId) {
                            if ($wholedata['deletValue'] <= 0) {
                               $product = $wholedata['product_id'];
                               //$this->deleteCustomfields($product); 
                            }
                        }
                    }
                    //FAQ save End      

                    $skuType = $helper->getSkuType();
                    $skuPrefix = $helper->getSkuPrefix();
                    if ($skuType == 'dynamic') {
                        $sku = $skuPrefix.$wholedata['product']['name'];
                        $wholedata['product']['sku'] = $this->checkSkuExist($sku);
                    }
                    list($errors, $wholedata) = $this->validatePost($wholedata);
                    if (empty($errors)) {
                        $returnArr = $this->_saveProduct->saveProductData(
                            $this->_getSession()->getCustomerId(),
                            $wholedata
                        );
                        $productId = $returnArr['product_id'];
                        if (isset($wholedata['selectedCategories'])) {
                            if (!empty($wholedata['selectedCategories'])) {
                                $categoies = json_decode($wholedata['selectedCategories']);
                                $this->makeRestricted($categoies, $productId, $wholedata);
                            }
                        }
                    } else {
                        foreach ($errors as $message) {
                            $this->messageManager->addError($message);
                        }
                        $this->getDataPersistor()->set('seller_catalog_product', $wholedata);
                    }
                }
                if ($productId != '') {
                    if (empty($errors)) {
                        $customerData = $this->_getSession()->getCustomer();
                        $seller = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
                        $sellerData = $seller->load($customerData->getId(),'seller_id');
                        if (!$sellerData->getTrustworthy()) {
                            $this->disApproveChildProducts($productId);
                        }
                        if (!empty($categoies)) {
                            $this->makeRestricted($categoies, $productId, $wholedata);
                        } 
                        
                        if ( isset($wholedata['custom_field_value']) ) {
                            $this->setCustomFieldData($wholedata, $productId); // For saving custom fields
                            
                            if ( isset($wholedata['associated_product_ids']) && (!empty($wholedata['associated_product_ids'])) ) {
                                
                                foreach ($wholedata['associated_product_ids'] as $value) {
                                    $this->setVendorCustomFields($wholedata, $value);
                                }
                            }
                        }
                        
                        $sensitiveArray  = [];
                        foreach ($wholedata['product'] as $key => $value) {
                            if (strpos($key, 'mis_2_41_35_sensitive') !== false) {
                                $sensitiveArray[$key] = $value;
                            }
                        }
                        $sensitiveAttrModel = $this->_objectManager->create('\Mangoit\Marketplace\Model\Sensitiveattrs');
                        $loadedProduct = $sensitiveAttrModel->load($productId, 'mageproduct_id');
                        // $sensitiveAttrModel = $this->_objectManager->create('\Mangoit\Marketplace\Model\ResourceModel\Sensitiveattrs\Collection');
                        if ($loadedProduct->hasData()) {
                            $loadedProduct->setSensitiveAttributes(serialize($sensitiveArray));
                            $loadedProduct->save();
                        } else {
                            $sensitiveAttrModel->setMageproductId($productId);
                            $sensitiveAttrModel->setSensitiveAttributes(serialize($sensitiveArray));
                            $sensitiveAttrModel->save();
                        }

                        if ( isset($wholedata['faq_fields']) ) {
                            $this->setFaqData($wholedata, $productId); // For saving FAQ data
                        }

                        /*if (isset($wholedata['associated_product_ids']) && (!empty($wholedata['associated_product_ids']))) {
                           $this->disApproveChildProducts($wholedata); 
                        }*/
                        // $this->disApproveChildProducts($productId);
                        $this->sendProductSaveNotificationEmailToSeller($productId);
                        $this->messageManager->addSuccess(
                            __('Your product has been successfully saved. Now it needs to be released by admin, which can take up to 24h. You will be notified via email once your product is released! Only then it will be available in the marketplace.')
                        );
                        $this->getDataPersistor()->clear('seller_catalog_product');
                    }

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/edit',
                        [
                            'id' => $productId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                } else {
                    if (isset($returnArr['error']) && isset($returnArr['message'])) {
                        if ($returnArr['error'] && $returnArr['message'] != '') {
                            $this->messageManager->addError($returnArr['message']);
                        }
                    }
                    $this->getDataPersistor()->set('seller_catalog_product', $wholedata);

                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/add',
                        [
                            'set' => $wholedata['set'],
                            'type' => $wholedata['type'],
                            'product'=> $wholedata['product'],
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addError($e->getMessage());
                $this->getDataPersistor()->set('seller_catalog_product', $wholedata);
                if ($productId) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/edit',
                        [
                            'id' => $productId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/add',
                        [
                            'set' => $wholedata['set'],
                            'type' => $wholedata['type'],
                            'product'=> $wholedata['product'],
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }
            } catch (\Exception $e) {
                $this->messageManager->addError($e->getMessage());
                $this->getDataPersistor()->set('seller_catalog_product', $wholedata);
                if ($productId) {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/edit',
                        [
                            'id' => $productId,
                            '_secure' => $this->getRequest()->isSecure(),
                        ]
                    );
                } else {
                    return $this->resultRedirectFactory->create()->setPath(
                        '*/*/add',
                        [
                            'set' => $wholedata['set'],
                            'type' => $wholedata['type'],
                            'product'=> $wholedata['product'],
                            '_secure' => $this->getRequest()->isSecure()
                        ]
                    );
                }
            }
        } else {
            return $this->resultRedirectFactory->create()->setPath(
                'marketplace/account/becomeseller',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
    }

    public function makeRestricted($categoies, $productId, $wholedata)
    {
        $name = $wholedata['product']['name'];
        $vendor = 'vendor';
        $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        foreach ($categoies as $catId) {
            if ($this->isRestrictedOrNot($catId)) {
                $this->proceedForRestrict($catId, $productId, $name, $vendor);
            }     
        }
    }

    public function isRestrictedOrNot($categoies)
    {
        $model = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory');
        $model->load($categoies, 'category_id');
        if ($model->getData()) {
            if ($model->getRestrictedCountries()) {
                return true;              
            } else {
                return false;
            }
            $model->unsetData();
        } else {
            return false;
        }
    }

    public function proceedForRestrict($catId, $productId, $name, $vendor)
    {
        $categoryModel = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedcategory');
        $productModel = $this->_objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        $categoryModel->load($catId, 'category_id');
        $productCol = $productModel->getCollection()->addFieldToFilter('category_id', array('eq' => $catId))->addFieldToFilter('product_id', $productId);

        if (count($productCol->getData()) > 0) {
            foreach ($productCol->getData() as $product) {
                $productModel->load($product['id']);
                $productModel->setRestrictedCountries($categoryModel->getRestrictedCountries());
                $productModel->unsetData();
            }
            # code...
        } else {           
            $productModel->setCategoryId($categoryModel->getCategoryId());
            $productModel->setCategoryName($categoryModel->getCategoryName());
            $productModel->setRestrictedCountries($categoryModel->getRestrictedCountries());
            $productModel->setVendorName($this->isLoggedIn());
            $productModel->setProductId($productId);
            $productModel->setProductName($name);
            $productModel->setProductStatus('Disabled');
            $productModel->save();


        }

        $categoryModel->unsetData();
        $productModel->unsetData();

        
    }

    private function checkSkuExist($sku)
    {
        try {
            $id = $this->_productResourceModel->getIdBySku($sku);
            if ($id) {
                $avialability = 0;
            } else {
                $avialability = 1;
            }
        } catch (\Exception $e) {
            $avialability = 0;
        }
        if ($avialability == 0) {
            $sku = $sku.rand();
            $sku = $this->checkSkuExist($sku);
        }
        return $sku;
    }
    /**
     * @return array
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    private function validatePost(&$wholedata)
    {
        $errors = [];
        $data = [];
        if (!isset($wholedata['product']['category_ids'])) {
           $errors[] = __('Please select at least one category.');
           $wholedata['product']['category_ids'] = '';
        }

        foreach ($wholedata['product'] as $code => $value) {
            switch ($code) :
                case 'name':
                    $result = $this->nameValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Name has to be completed');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'description':
                    $result = $this->descriptionValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Description has to be completed');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'short_description':
                    $result = $this->descriptionValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'price':
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Price should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        // if ($wholedata['product'][$code] <  $wholedata['product']['shipping_price_to_mygmbh']) {
                        //     $errors[] = __('Price should be greated than shipping cost to myGermany');
                        //     $wholedata['product'][$code] = '';
                        // } else {
                        // }
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'shipping_price_to_mygmbh':
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Shipping cost to mygermany should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'mygmbh_shipping_product_length':
                    $result = $this->priceValidateFunction(trim($value), $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Length of product should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'mygmbh_shipping_product_width':
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Width of product should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'mygmbh_shipping_product_height':
                    $result = $this->priceValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Height of product should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'weight':
                    $result = $this->weightValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Weight should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'stock':
                    $result = $this->stockValidateFunction($value, $code, $errors, $data);
                    if ($result['error']) {
                        $errors[] = __('Product quantity should contain only decimal numbers');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'sku_type':
                    $result = $this->skuTypeValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Sku Type has to be selected');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'sku':
                    $result = $this->skuValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Sku has to be completed');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'price_type':
                    $result = $this->priceTypeValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Price Type has to be selected');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'weight_type':
                    $result = $this->weightTypeValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Weight Type has to be selected');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'bundle_options':
                    $result = $this->bundleOptionValidateFunction($value, $code, $data);
                    if ($result['error']) {
                        $errors[] = __('Default Title has to be completed');
                        $wholedata['product'][$code] = '';
                    } else {
                        $wholedata['product'][$code] = $result['data'][$code];
                    }
                    break;
                case 'meta_title':
                    $result = $this->metaTitleValidateFunction($value, $code, $data);
                    $wholedata['product'][$code] = $result['data'][$code];
                    break;
                case 'meta_keyword':
                    $result = $this->metaKeywordValidateFunction($value, $code, $data);
                    $wholedata['product'][$code] = $result['data'][$code];
                    break;
                case 'meta_description':
                    $result = $this->metaDiscValidateFunction($value, $code, $data);
                    $wholedata['product'][$code] = $result['data'][$code];
                    break;
                case 'mp_product_cart_limit':
                    if (!empty($value)) {
                        $result = $this->stockValidateFunction($value, $code, $errors, $data);
                        if ($result['error']) {
                            $errors[] = __('Allowed Product Cart Limit Qty should contain only decimal numbers');
                            $wholedata['product'][$code] = '';
                        } else {
                            $wholedata['product'][$code] = $result['data'][$code];
                        }
                    }
                    break;
            endswitch;
        }

        return [$errors, $wholedata];
    }

    


    private function nameValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = strip_tags($value);
        }
        return ['error' => $error, 'data' => $data];
    }

    private function descriptionValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
            $helper = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            );
            $value = $helper->validateXssString($value);
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function shortDescValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function priceValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match('/^([0-9])+?[0-9.,]*$/', $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function weightValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match('/^([0-9])+?[0-9.,]*$/', $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function stockValidateFunction($value, $code, $data)
    {
        $error = false;
        if (!preg_match('/^([0-9])+?[0-9.]*$/', $value)) {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function skuTypeValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function skuValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = strip_tags($value);
        }
        return ['error' => $error, 'data' => $data];
    }

    private function priceTypeValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function weightTypeValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function bundleOptionValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
        } else {
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function metaTitleValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
            $data[$code] = '';
        } else {
            $data[$code] = strip_tags($value);
        }
        return ['error' => $error, 'data' => $data];
    }

    private function metaKeywordValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
            $data[$code] = '';
        } else {
            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
            $helper = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            );
            $value = $helper->validateXssString($value);
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    private function metaDiscValidateFunction($value, $code, $data)
    {
        $error = false;
        if (trim($value) == '') {
            $error = true;
            $data[$code] = '';
        } else {
            $value = preg_replace("/<script.*?\/script>/s", "", $value) ? : $value;
            $helper = $this->_objectManager->create(
                'Webkul\Marketplace\Helper\Data'
            );
            $value = $helper->validateXssString($value);
            $data[$code] = $value;
        }
        return ['error' => $error, 'data' => $data];
    }

    /**
     * Retrieve data persistor
     *
     * @return \Magento\Framework\App\Request\DataPersistorInterface|mixed
     */
    protected function getDataPersistor()
    {
        if (null === $this->dataPersistor) {
            $this->dataPersistor = $this->_objectManager->get(
                \Magento\Framework\App\Request\DataPersistorInterface::class
            );
        }

        return $this->dataPersistor;
    }
    

    public function setVendorCustomFields($wholedata, $productId)
    {
        $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
        $filteredData = $fieldModel->getCollection()->addFieldToFilter('product_id', ['eq'=> $productId]);
        if (count($filteredData->getData()) > 0) {
            foreach ($filteredData->getData() as $item) {
                $fieldModel->load($item['id']);
                foreach ($wholedata['custom_field_value'] as $customKey => $customValue) {
                    $fieldModel->setProductId($productId);
                    $fieldModel->setCustomFields($customValue['field_name']);
                    $fieldModel->setVendorInput($customValue['input']);
                    $fieldModel->setCustomFieldValue($customValue['input']);
                    $fieldModel->setVendorComments((isset($customValue['comment'])) ? $customValue['comment'] : NULL );
                    $fieldModel->save();
                }
            }
        } else {
            if (isset($wholedata['custom_field_value'])) {
                foreach ($wholedata['custom_field_value'] as $customKey => $customValue) {
                    $fieldModel->setProductId($productId);
                    $fieldModel->setCustomFields($customValue['field_name']);
                    $fieldModel->setVendorInput($customValue['input']);
                    $fieldModel->setCustomFieldValue($customValue['input']);
                    $fieldModel->setVendorComments((isset($customValue['comment'])) ? $customValue['comment'] : NULL );
                    $fieldModel->save();
                }
            }            
        }
    }
    
    public function setCustomFieldData($wholedata, $productId)
    {
        if (isset($wholedata['custom_field_value'])) {
            foreach ($wholedata['custom_field_value'] as $customKey => $customValue) {
                $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
                $fieldModel->load($customKey);
                $fieldModel->setVendorInput($customValue['input']);
                $fieldModel->setCustomFieldValue($customValue['input']);
                $fieldModel->setVendorComments((isset($customValue['comment'])) ? $customValue['comment'] : NULL );
                $fieldModel->save();
            }
        }

        // Old Code Commented on 13th Sept, 2018
        // $fieldArray = [];
        // $postFieldsArray = [];
        // $date = date("d-m-y h:i:s");
        // foreach ($wholedata['custom_field_value'] as $key => $value) {
        //     if ($wholedata['hasValue'] == 1) {
        //         array_push($postFieldsArray, $value['label_name']);
        //     }
        // }
       
        // $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
        // $idsNtoDelete = [];
        // if (isset($wholedata['custom_field_value'])) {
        //     foreach ($wholedata['custom_field_value'] as $customValue) {
        //         if (isset($customValue['current_id'])) {
        //             $fieldModel->load($customValue['current_id']);
        //             $fieldModel->setCustomFields($customValue['label_name']);
        //             $fieldModel->setCustomFieldValue($customValue['label_value']);
        //             $fieldModel->save();
        //             $idsNtoDelete[] = $customValue['current_id'];
                    
        //         } else {
        //             if (!empty($customValue['label_name'])) {
        //                 $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
        //                 $fieldModel->setProductId($productId);
        //                 $fieldModel->setProductName($wholedata['product']['name']);
        //                 $fieldModel->setCustomFields($customValue['label_name']);
        //                 $fieldModel->setCustomFieldValue($customValue['label_value']);
        //                 $fieldModel->save();
        //                 $idsNtoDelete[] = $fieldModel->getId();
        //             }
        //         }
        //     }
        //     $filteredData = $fieldModel->getCollection()
        //     ->addFieldToFilter('product_id', array('eq'=> $productId))
        //     ->addFieldToFilter('id', array('nin'=> $idsNtoDelete));
        //     if (!empty($filteredData)) {
        //         foreach ($filteredData as $delVal) {
        //             $delVal->delete();
        //         }
        //     } 
        // }
        
    }

    public function setFaqData($wholedata, $productId)
    {
        $customerData = $this->_getSession()->getCustomer();
       
        $storeManager = $this->_objectManager->create('Magento\Store\Model\StoreManagerInterface');
        $dateObj = $this->_objectManager->create('Magento\Framework\Stdlib\DateTime\DateTime');
        $seller = $this->_objectManager->create('Webkul\Marketplace\Model\Seller');
        $storeRepository = $this->_objectManager->create('Magento\Store\Model\StoreRepository');
        $sellerData = $seller->load($customerData->getId(),'seller_id');

        $shoptitle = $sellerData->getShopTitle();
        $date = $dateObj->gmtDate();
        $stores = $storeRepository->getList();
        $storeObj = $storeManager->getDefaultStoreView();
        $storeId = $storeObj->getStoreId();
        $defaultStoreCode = $storeObj->getCode();
        $allStoreIds = [];
        $isActive = 0;
        if ($sellerData->getTrustworthy()) {
            $isActive = 1;
        }
        foreach ($wholedata['faq_fields'] as $faqValues) {
            $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
            $model->setProductId($productId);
            $model->setTitle($faqValues['title']);
            $model->setDescription($faqValues['description']);
            $model->setPublishDate($date);
            $model->setIsActive($isActive);
            $model->setPostedBy($shoptitle);
            $model->setEmailId($customerData->getEmail());
            $model->setVendorId($customerData->getId());
            $model->setPublishDate($date);
            $model->setUpdatedAt($date);
            $model->setAdminNotification(1);
            $model->setStoreId($storeId);
            $model->setParentFaqId(0);
            $model->setIsTranslated(1);
            $model->save();
            $parentFaqId = $model->getId();

            $allStoreIds[$storeId] = $model->getId();
            
            foreach ($stores as $store) {
                $localStoreId = $store->getStoreId();
                $storeCode = $store->getCode();

                if ($storeCode == $defaultStoreCode) {
                    continue;
                }
                $model = $this->_objectManager->create('Ced\Productfaq\Model\Productfaq');
                $model->setProductId($productId);
                $model->setTitle($faqValues['title']);
                $model->setDescription($faqValues['description']);
                $model->setPublishDate($date);
                $model->setIsActive($isActive);
                $model->setPostedBy($shoptitle);
                $model->setEmailId($customerData->getEmail());
                $model->setVendorId($customerData->getId());
                $model->setPublishDate($date);
                $model->setUpdatedAt($date);
                $model->setAdminNotification(1);
                $model->setStoreId($localStoreId);
                $model->setParentFaqId($parentFaqId);
                if ($customerData->getIsTranslated()) {
                    $model->setIsTranslated(1);
                }
                try {
                    $model->save();
                    $allStoreIds[$localStoreId] = $model->getId();
                } catch (\Magento\Framework\Exception\LocalizedException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\RuntimeException $e) {
                    $this->messageManager->addError($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addException($e, __('Something went wrong while saving the faq.'));
                }
            }

            try {
                $misFaqModel = $this->_objectManager->create('Mangoit\Productfaq\Model\Misproductfaq');
                $misFaqModel->setDefaultFaqId($parentFaqId);
                $misFaqModel->setStorewiseFaqIds(serialize($allStoreIds));
                $misFaqModel->save();
            } catch (Exception $e) {
                echo $e->getMessage();
            }
        }
        
    }

    public function isLoggedIn()
    {
        $model = $this->_objectManager->create('Magento\Customer\Model\Session');
        return $model->getCustomer()->getName();
    }

    public function deleteCustomfields($productId)
    {
        $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel');
        $filteredData = $fieldModel->getCollection()->addFieldToFilter('product_id', array('eq'=> $productId));
        // print_r($filteredData->getData());
        if (count($filteredData->getData()) >= 1) {
            foreach ($filteredData->getData() as $item) {
                $fieldModel->load($item['id']);
                $fieldModel->delete();
                $fieldModel->save();
                $fieldModel->unsetData();
            }
            
        }
    }

    public function disApproveChildProducts($productId)
    {
        // die('disApproveChildProducts');
        // echo "<pre>";
        $model = $this->_objectManager->create('Magento\Catalog\Model\Product');
        $loadedModel = $model->load($productId);
        // print_r($loadedModel->debug());
        // die();
        if ($loadedModel->getTypeId() == 'configurable') {
            $idarray = $loadedModel->getTypeInstance()->getUsedProducts($loadedModel);  
            
            if (!empty($idarray)) {
                foreach ($idarray as $child) {
                    $child->setStatus(2);
                    $child->save();
                }
            }
        }
    }

    public function sendProductSaveNotificationEmailToSeller($productId)
    {
        try {

            $model = $this->_objectManager->create('Magento\Catalog\Model\Product');
            $customerData = $this->_getSession()->getCustomer();
            $product = $model->load($productId);

            $data['name'] = $customerData->getFirstname(); 
            $data['product_name'] = $product->getName();
            $data['product_qty'] = $product->getQuantityAndStockStatus()['qty'];
            $data['product_type'] = $product->getTypeId();
            $receiverInfo = ['name'=> $customerData->getFirstName(), 'email'=> $customerData->getEmail()];

            $productNotificationHelper = $this->_objectManager->create('Mangoit\Marketplace\Helper\ProductSavedNotification');
            $imageUrl = $productNotificationHelper->getImageUrl($product);
            $data['product_image'] = $imageUrl;

            $productNotificationHelper->sendProductSavedNotification($data, $receiverInfo, $customerData->getStoreId());

        } catch (Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
        }

    }
}

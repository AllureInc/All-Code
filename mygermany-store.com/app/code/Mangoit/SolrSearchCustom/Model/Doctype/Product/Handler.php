<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\SolrSearchCustom\Model\Doctype\Product;

use \Solrbridge\Search\Model\Doctype\Product\Handler as SolrHandler;

/**
* This class will load, polutate magento products
* and push data to Solr for indexing
*/
class Handler extends SolrHandler
{
    protected function _injectProductAttributeData($productId)
    {
        $attributeCodesUsedForAutocomplete = $this->getProductAttrCodesUseForTermGeneration();
        $attributeUsedForBoost = array();
        $this->_documentData['document_search_weight_int'] = 0;
        //Load product attribute data for specific product
        if (isset($this->_dataProductAttributeValues[$productId])) {
            $attributeValues = $this->_dataProductAttributeValues[$productId];
            //Normal attributes
            foreach ($attributeValues as $attributeCode => $value) {
                $attribute = $this->_dataProductAttributeData[$attributeCode];
                $key = $attributeCode.'_'.$attribute->getBackendType();
                $this->_documentData[$key] = $value;
                
                if ('name' == $attributeCode) {
                    $attributeUsedForBoost[$attributeCode] = $value;
                } else {
                    if ($attribute->getData('solrbridge_search_boost_weight') > 0) {
                        $attributeUsedForBoost[$attributeCode] = $value;
                        if (isset($this->_dataProductAttributeValuesOptionsLabels[$productId][$attributeCode])) {
                            $labels = $this->_dataProductAttributeValuesOptionsLabels[$productId][$attributeCode];
                            if (!empty($labels)) {
                                $attributeUsedForBoost[$attributeCode] = $labels;
                            }
                        }
                    }
                }
                
                $this->_documentTextSearch[] = $value;
                
                //put attribute values for autocomplete search
                if (is_array($attributeCodesUsedForAutocomplete) && in_array($attributeCode, $attributeCodesUsedForAutocomplete)) {
                    $this->_documentAutocompleteSearch[] = $value;
                }
                
                //Inject product search weight value
                if ($attributeCode == $this->getProductAttrCodeUseForSearchWeight()) {
                    $this->_documentData['document_search_weight_int'] = $value;
                }
            }
            //Attribute has options select/multiple select
            if (isset($this->_dataProductAttributeValuesOptions[$productId])) {
                $attributeRealValues = $this->_dataProductAttributeValuesOptions[$productId];
                foreach ($attributeRealValues as $attributeCode => $value) {
                    $attribute = $this->_dataProductAttributeData[$attributeCode];
                    $key = $attributeCode.'_facet';
                    $this->_documentData[$key] = $value;
                    //$this->_documentTextSearch[] = $value;
                }
            }
            //Inject data for autocomplete brand attribute
            if ($attrCode = $this->_getAutocompleteBrandAttributeCode()) {
                if (isset($this->_dataProductAttributeValuesOptionsLabels[$productId][$attrCode])) {
                    $_autocompleteBrands = $this->_dataProductAttributeValuesOptionsLabels[$productId][$attrCode];
                    if (!is_array($_autocompleteBrands)) {
                        $autocompleteBrands = array($_autocompleteBrands);
                    } else {
                        $autocompleteBrands = array();
                        foreach ($_autocompleteBrands as $k => $v) {
                            $autocompleteBrands[] = $v.'/'.$k;
                        }
                    }
                    $key = $attrCode.'_autocomplete_facet';
                    $this->_documentData[$key] = $autocompleteBrands;
                }
            }
        }
        /**********Code added for country restriction*************/
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $productModel = $objectManager->create('Mangoit\FskRestricted\Model\Restrictedproduct');
        $productModelCollection = $productModel->getCollection()->addFieldToSelect('restricted_countries')->addFieldToFilter('product_id',array('eq' => $productId));
        $countries = array_column($productModelCollection->getData(), 'restricted_countries');

        $resCountries = [];
        foreach ($countries as $perCountry) {
            $resCountries = array_merge($resCountries, explode(',', $perCountry));
        }

        $this->_documentData['restricted_countries_facet'] = $resCountries;
        /**********Code added for country restriction*************/
        //Inject attributes which used for solr boost
        if (is_array($attributeUsedForBoost) && count($attributeUsedForBoost) > 0) {
            foreach ($attributeUsedForBoost as $attributeCode => $value) {
                if (is_array($value)) {
                    $value = array_values($value);
                }
                $this->_documentData[$attributeCode.'_boost'] = $value;
                $this->_documentData[$attributeCode.'_boost_exact'] = $value;
                $this->_documentData[$attributeCode.'_relative_boost'] = $value;
            }
        }
    }
}

<?php
/**
 * Copyright © 2017 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Helper;


class SolrProductPrice extends \Magento\Framework\App\Helper\AbstractHelper
{
    /*
    * Magento\Catalog\Helper\Data $taxHelper
    *
    */
    protected $_taxHelper;

    /*
    * Magento\Catalog\Helper\Data $taxHelper
    *
    */
    protected $_taxCalculation;
    protected $_productRepository;

    public function __construct (
        \Magento\Catalog\Helper\Data $taxHelper,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Tax\Api\TaxCalculationInterface $taxCalculation,
        \Magento\Framework\App\Helper\Context $context
    ) {   
        $this->_taxHelper = $taxHelper;
        $this->_taxCalculation = $taxCalculation;
        $this->_productRepository = $productRepository;
        parent::__construct($context);
    }

    public function getProductPriceWithTax($product_id)
    {
        $product = $this->_productRepository->getById($product_id);
        $taxAttribute = $product->getCustomAttribute('tax_class_id');
        if($taxAttribute) {
            $productRateId = $taxAttribute->getValue();
            if($productRateId) {
                $rate = $this->_taxCalculation->getCalculatedRate($productRateId);
            }
        } else {
            $rate = 19;
        }

        return $rate;
    }
}
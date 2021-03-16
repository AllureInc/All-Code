<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config\Source\Tax\Product;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Tax\Model\TaxClass\Source\Product as ProductTaxClassSource;

/**
 * Class AdaptorType
 * @package Plenty\Core\Model\Profile\Source
 */
class Classes implements OptionSourceInterface
{
    /**
     * @var ProductTaxClassSource
     */
    protected $_productTaxClassSource;

    /**
     * Classes constructor.
     * @param ProductTaxClassSource $productTaxClassSource
     */
    public function __construct(ProductTaxClassSource $productTaxClassSource)
    {
        $this->_productTaxClassSource = $productTaxClassSource;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return $this->_productTaxClassSource->getAllOptions();
    }
}
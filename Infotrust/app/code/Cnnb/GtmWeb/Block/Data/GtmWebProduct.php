<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block Class
 * For preparing product's data
 */
namespace Cnnb\GtmWeb\Block\Data;

use Cnnb\Gtm\Block\Data\Product;
use Magento\Framework\View\Element\Template\Context;
use Magento\Framework\View\Element\Template;

class GtmWebProduct extends Template
{
    /**
     * AbstractProduct
     **/
    protected $_product;
    
    /**
     * @param  Context  $context
     * @param  AbstractProduct  $abstractProduct
     */
    public function __construct(
        Context $context,
        Product $product
    ) {
        $this->_product = $product;
    }

    public function getProductName()
    {
        $product_name = '';
        $productData = $this->_product->getProduct();
        if ($productData) {
            $product_name = $productData->getName();
        }
        return $product_name;
    }
}

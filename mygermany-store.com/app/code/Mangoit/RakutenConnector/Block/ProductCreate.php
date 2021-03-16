<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

namespace Mangoit\RakutenConnector\Block;

use Magento\Catalog\Model\Product;

class ProductCreate extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $_product;

    /**
     * @param \Magento\Catalog\Block\Product\Context $context
     * @param Product                                $product
     * @param array                                  $data
     */
    
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        Product $product,
        \Magento\Eav\Model\Config $eavConfig,
        array $data = []
    ) {
        $this->_product = $product;
        $this->eavConfig = $eavConfig;
        parent::__construct($context, $data);
    }

    /**
     * use to get current url.
     */
    public function getCurrentUrl()
    {
        // Give the current url of recently viewed page
        return $this->_urlBuilder->getCurrentUrl();
    }
    public function getProduct()
    {
        return $this->_product;
    }
    /**
     * getIsSecure check is secure or not
     * @return boolean
     */
    public function getIsSecure()
    {
        return $this->getRequest()->isSecure();
    }

    public function getParameters()
    {
        return $this->getRequest()->getParams();
    }


    public function getProductIdentifierCodes()
    {
        $attribute = $this->eavConfig->getAttribute('catalog_product', 'identification_label');
        $options = $attribute->getSource()->getAllOptions();
        return $options;
    }
}

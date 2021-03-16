<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Plugin;

use Magento\Catalog\Model\Product as CatalogProduct;

class Product
{
    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    private $_preorderHelper;

    /**
     * Initialize dependencies.
     *
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper
    ) {
        $this->_preorderHelper = $preorderHelper;
    }

    public function afterIsSalable(CatalogProduct $subject, $result)
    {
        $productId = $subject->getId();
        $helper = $this->_preorderHelper;
        if ($helper->isPreorder($productId) && !$helper->isChildProduct()) {
            return true;
        } elseif ($helper->isConfigPreorder($productId)) {
            return true;
        }
        return $result;
    }
}

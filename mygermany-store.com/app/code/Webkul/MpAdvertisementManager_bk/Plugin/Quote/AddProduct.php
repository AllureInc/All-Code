<?php

/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Plugin\Quote;

class AddProduct
{
    /**
     * object manager for injecting objects.
     *
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Framework\UrlInterface           $urlinterface
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
    
        $this->_objectManager = $objectManager;
    }

    /**
     * plugin to stop add to cart of ads plan product
     *
     * @param \Magento\Quote\Model\Quote
     * @param Closure                       $proceed
     * @param Magento\Catalog\Model\Product $product
     *
     * @return html
     */
    public function beforeAddProduct(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Catalog\Model\Product $product,
        $request = null,
        $processMode = \Magento\Catalog\Model\Product\Type\AbstractType::PROCESS_MODE_FULL
    ) {
        if ($product->getSku() == 'wk_mp_ads_plan' && $request != 1) {
            throw new \Magento\Framework\Exception\LocalizedException(
                __('This product cannot be added from here')
            );
        }
    }
}

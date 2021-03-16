<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerVacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Block\Plugin\Compare;

class ListCompare
{
    /**
     * object manager for injecting objects.
     *
     * @var Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {

        $this->_objectManager = $objectManager;
    }

    /**
     * function to run to change the retun data of GetProductPrice.
     *
     * @param \Magento\CatalogSearch\Block\Result $list
     * @param Closure                             $proceed
     * @param Magento\Catalog\Model\Product       $product
     *
     * @return html
     */
    public function aroundGetProductPrice(
        \Magento\Catalog\Block\Product\Compare\ListCompare $list,
        $proceed,
        $product,
        $idSuffix
    ) {
        $vacationStatus = $this->_objectManager
          ->create('Webkul\MpSellerVacation\Helper\Data')
          ->getProductvacationStatus($product->getId());
        $status = '';
        if ($vacationStatus == 'add_to_cart_disable') {
            $status = '<input type="hidden"
           value="'.$product->getId().'" name="remove-add-to-cart" class="remove-add-to-cart">';
        }

        return $proceed($product).$status;
    }
}

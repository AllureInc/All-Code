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

namespace Webkul\MpAdvertisementManager\Plugin\Customer;

class LastOrderedItems
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper;
    
    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_orderItems;

    /**
     * __construct
     *
     * @param \Webkul\MpAdvertisementManager\Helper\Data $helper
     * @param \Magento\Sales\Model\Order\ItemFactory     $orderItems
     */
    public function __construct(
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Sales\Model\Order\ItemFactory $orderItems
    ) {
        $this->_helper = $helper;
        $this->_orderItems = $orderItems;
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
    public function afterGetSectionData(\Magento\Sales\CustomerData\LastOrderedItems $subject, $result)
    {
        $lastOrderItems = $result['items'];
        $temp = [] ;
        foreach ($lastOrderItems as $key => $items) {
            $orderItemModel = $this->_orderItems->create()->load($items['id']);
            $sku = $orderItemModel->getSku();
            if ($sku == "wk_mp_ads_plan") {
                // $temp[] = $key;
            } else {
                $temp[] = $items;
            }
        }
        return ['items' => $temp];
    }
}

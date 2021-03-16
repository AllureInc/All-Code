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

namespace Webkul\MpAdvertisementManager\Plugin\Checkout\Block\Cart;

class GetCartItemHtml
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper;

    /**
     * Undocumented variable
     *
     * @var \Magento\Checkout\Model\Cart
     */
    protected $_item;

    /**
     * @param \Webkul\MpAdvertisementManager\Helper\Data $helper
     * @param \Magento\Checkout\Model\Cart               $item
     */
    public function __construct(
        \Webkul\MpAdvertisementManager\Helper\Data $helper,
        \Magento\Checkout\Model\Cart $item
    ) {
        $this->_helper = $helper;
        $this->_item = $item;
    }

    /**
     * plugin to disable the quantity input element so that it's quantity cannot be chabge and remain always 1
     * of caret item.
     *
     * @param \Magento\Checkout\Block\Cart
     * @param Closure              $result
     * @param \Magento\Quote\Model\Quote\Item
     * @return html
     */
    public function aroundGetItemHtml(\Magento\Checkout\Block\Cart $subject, $proceed, \Magento\Quote\Model\Quote\Item $item)
    {
        if ($item->getSku() == "wk_mp_ads_plan") {
            /* if ads product increses at the time of check out then no of ad's display day increases not the qty that's why removing the check*/
            return $proceed($item);
            // $match = explode('<input', $result);
            // $match2 = explode('/>', $match[1]);
            // return str_replace("<input".$match2[0]."/>", '<span>'.$item->getQty().'<span>', $result);
        } else {
            return $proceed($item);
        }
    }
}

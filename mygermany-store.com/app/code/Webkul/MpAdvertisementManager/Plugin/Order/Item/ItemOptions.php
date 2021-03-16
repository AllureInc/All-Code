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

namespace Webkul\MpAdvertisementManager\Plugin\Order\Item;

class ItemOptions
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Data
     */
    protected $_helper;

    /**
     * __construct
     *
     * @param \Webkul\MpAdvertisementManager\Helper\Data $helper
     */
    public function __construct(
        \Webkul\MpAdvertisementManager\Helper\Data $helper
    ) {
        $this->_helper = $helper;
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
    public function afterGetProductOptions(\Magento\Sales\Model\Order\Item $subject, $result)
    {
        if ($subject->getData('sku')=='wk_mp_ads_plan') {
            $data = $result;
            if (is_string($result)) {
                $data = unserialize($result);
            }
            $adsProductOptions = $subject->getData('product_options');
            $adsBlockPosition = key($adsProductOptions['info_buyRequest']);
            $adsBlockName = $this->getBlockNameById($adsProductOptions['info_buyRequest'][$adsBlockPosition]['block_position']);
            $data['options'][] = ['label'=>'Block Name','value'=>$adsBlockName];
            $data['options'][] = ['label'=>'Block Position','value'=>$adsProductOptions['info_buyRequest'][$adsBlockPosition]['block_position']];
            $data['options'][] = ['label'=>'Days','value'=>$adsProductOptions['info_buyRequest'][$adsBlockPosition]['days']];
            return $data;
        } else {
            return $result;
        }
    }
    /**
     * getBlockNameById get Block position label
     *
     * @param int $blockId
     * @return String
     */
    public function getBlockNameById($blockId)
    {
        if (isset($blockId)) {
            $allBlockDetails = $this->_helper->getAdsPositions();
            return $allBlockDetails[$blockId]['label'];
        } else {
            return "--";
        }
    }
}

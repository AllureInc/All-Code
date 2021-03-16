<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Api\Item;;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Filters
 * @package Plenty\Item\Profile\Config\Source\Api\Item
 */
class Filters implements OptionSourceInterface
{
    /**
     * @see https://developers.plentymarkets.com/rest-doc/item/details#search-item
     */
    const PROPERTIES                        = 'itemProperties';
    const VARIATION_PROPERTIES              = 'itemCrossSelling';
    const VARIATION_BARCODE                 = 'variations';
    const VARIATION_BUNDLE_COMPONENTS       = 'itemImages';
    const VARIATION_COMPONENT_BUNDLES       = 'itemShippingProfiles';
    const VARIATION_SALES_PRICES            = 'ebayTitles';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::PROPERTIES, 'label'                       => __('Properties')],
            ['value' => self::VARIATION_PROPERTIES, 'label'             => __('Item cross-sells')],
            ['value' => self::VARIATION_BARCODE, 'label'                => __('Variations')],
            ['value' => self::VARIATION_BUNDLE_COMPONENTS, 'label'      => __('Item images')],
            ['value' => self::VARIATION_COMPONENT_BUNDLES, 'label'      => __('Item shipping profiles')],
            ['value' => self::VARIATION_SALES_PRICES, 'label'           => __('eBay titles')],
        ];
    }
}
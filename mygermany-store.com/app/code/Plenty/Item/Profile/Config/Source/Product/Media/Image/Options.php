<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Media\Image;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 * @package Plenty\Item\Profile\Config\Source\Product\Media\Image
 */
class Options implements OptionSourceInterface
{
    /**
     * @see https://developers.plentymarkets.com/rest-doc/item_image_availability/details#list-availabilities
     */
    const ALL = 'all';
    const MANDANT = 'mandant';
    const MARKETPLACE = 'marketplace';
    const LISTING = 'listing';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ALL, 'label' => __('ALL')],
            ['value' => self::MANDANT,  'label' => __('Mandant [The image can be made available for clients (stores)]')],
            ['value' => self::MARKETPLACE,  'label' => __('Marketplace [The image can be made available for markets]')],
            ['value' => self::LISTING,  'label' => __('Listing [The image can be made available for listings]')]
        ];
    }
}
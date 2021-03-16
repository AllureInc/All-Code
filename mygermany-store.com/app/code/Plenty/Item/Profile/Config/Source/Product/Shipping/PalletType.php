<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\Shipping;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class PalletType
 * @package Plenty\Item\Profile\Config\Source\Product\Shipping
 */
class PalletType implements OptionSourceInterface
{
    /**
     * 0: unpacked
    1: Bale
    2: Dispenser
    3: Coil
    4: Roll pallet
    5: Colli
    6: Container
    7: Bucket
    8: Cask
    9: Bottles
    10: European flat pallet
    11: Structural frame
    12: Gas cylinder
    13: Pallet cage
    14: Hobbock
    15: Half pallet
    16: Pallet of food items
    17: Wooden coaster
    18: IBC container
    19: Pitcher
    20: Wicker bottle
    21: Case
    22: Canister
    23: Customer pallet
    24: Cardboard box
    25: Composite packaging
    26: Package
    27: Ring
    28: Role
    29: Sack
    30: units
    31: Tank
    32: Drum
    34: Crate
    35: Quarter pallet
    36: Other pallets
    37: Bin
    38: One-way pallet
    39: Foil bag
     */

    const ROL_PALLET                = 4;
    const EUROPEAN_FLAT_PALLET      = 10;
    const PALLET_CAGE               = 13;
    const HALF_PALLET               = 15;
    const FOOD_ITEMS_PALLET         = 16;
    const CASE_PALLET               = 21;
    const CARDBOARD_BOX_PALLET      = 24;
    const PACKAGE_PALLET            = 26;
    const QUARTER_PALLET            = 35;
    const ONE_WAY_PALLET            = 38;

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ROL_PALLET, 'label' => __('Roll pallet')],
            ['value' => self::EUROPEAN_FLAT_PALLET, 'label' => __('European flat pallet')],
            ['value' => self::PALLET_CAGE, 'label' => __('Pallet cage')],
            ['value' => self::HALF_PALLET, 'label' => __('Half pallet')],
            ['value' => self::FOOD_ITEMS_PALLET, 'label' => __('Pallet of food items')],
            ['value' => self::CASE_PALLET, 'label' => __('Case pallet')],
            ['value' => self::CARDBOARD_BOX_PALLET, 'label' => __('Cardboard box')],
            ['value' => self::PACKAGE_PALLET, 'label' => __('Package')],
            ['value' => self::QUARTER_PALLET, 'label' => __('Quarter pallet')],
            ['value' => self::ONE_WAY_PALLET, 'label' => __('One-way pallet')]
        ];
    }
}
<?php

/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Model\Vacation;

class Mode
{
    public function toOptionArray()
    {
        $data = [
                    [
                        'value' => 'product_disable',
                        'label' => __('Product Disable'),
                    ],
                    [
                        'value' => 'add_to_cart_disable',
                        'label' => __('Add To Cart Disabled'),
                    ],
            ];

        return  $data;
    }
}

<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpSellerCoupons\Model;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{
    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
                        [
                            'label' => 'Used',
                            'value' => 'used'
                        ],
                        [
                            'label' => 'Active',
                            'value' => 'active'
                        ],
                        [
                            'label' => 'Expired',
                            'value' => 'expired'
                        ]
                    ];
        return $options;
    }
}

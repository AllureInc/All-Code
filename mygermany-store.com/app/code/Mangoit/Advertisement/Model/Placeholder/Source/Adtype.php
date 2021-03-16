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

namespace Mangoit\Advertisement\Model\Placeholder\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Positions.
 */
class Adtype implements OptionSourceInterface
{

    /**
     * Get positions.
     *
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'label' => __("Not Specified"),
                'value' => null
            ],[
                'label' => __("External"),
                'value' => 0
            ],
            [
                'label' => __("Internal"),
                'value' => 1,
            ]
        ];
        return $options;
    }
}

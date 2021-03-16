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
class Contentname implements OptionSourceInterface
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
                'label' => __("Image"),
                'value' => 1
            ],
            [
                'label' => __("Product"),
                'value' => 2,
            ],
            [
                'label' => __("Category"),
                'value' => 3,
            ],
            [
                'label' => __("HTML Editor"),
                'value' => 4,
            ]
        ];
        return $options;
    }
}

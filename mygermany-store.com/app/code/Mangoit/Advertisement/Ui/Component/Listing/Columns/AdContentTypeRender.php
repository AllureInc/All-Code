<?php
namespace Mangoit\Advertisement\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class AdContentTypeRender implements OptionSourceInterface
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
                'label'=> __('-- select --'),
                'value'=> ''
            ],
            [
                'label' => __('Image/Banner'),
                'value' => 1
            ],
            [
                'label' => __('Product'),
                'value' => 2
            ],
            [
                'label' => __('Category'),
                'value' => 3
            ],
            [
                'label' => __('HTML'),
                'value' => 4
            ]
        ];
        return $options;
    }
}

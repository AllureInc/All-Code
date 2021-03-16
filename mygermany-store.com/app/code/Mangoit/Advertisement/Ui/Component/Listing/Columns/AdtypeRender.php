<?php
namespace Mangoit\Advertisement\Ui\Component\Listing\Columns;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Options
 */
class AdtypeRender implements OptionSourceInterface
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
                'label' => __('External'),
                'value' => 0
            ],
            [
                'label' => __('Internal'),
                'value' => 1
            ]
        ];
        return $options;
    }
}

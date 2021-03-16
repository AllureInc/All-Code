<?php 

namespace Mangoit\Advertisement\Model\Source;

/**
* 
*/
class Contentsource implements \Magento\Framework\Option\ArrayInterface
{
	
	public function toOptionArray()
    {
        return [
            ['value' => 0, 'label' => __('Select')],
            ['value' => 1, 'label' => __('Image/banner')],
            ['value' => 2, 'label' => __('Product')],
            ['value' => 3, 'label' => __('Category')],
            ['value' => 4, 'label' => __('HTML Editor')],
        ];
    }
}
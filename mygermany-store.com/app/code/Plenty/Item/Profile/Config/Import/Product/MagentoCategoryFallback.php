<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Import\Product;;

use Magento\Catalog\Api\Data\CategoryInterface;
use Plenty\Item\Profile\Config\Source\Category\Magento;

/**
 * Class MagentoCategoryFallback
 * @package Plenty\Item\Profile\Config\Import\Product
 */
class MagentoCategoryFallback extends Magento
{
    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = [
            [
                'value' => 0,
                'label' => 'Use Default Store Category'
            ]
        ];

        if (null === $this->_options) {
            $this->_options = $options;
            /** @var CategoryInterface $category */
            foreach ($this->getRootCategories() as $category) {
                $this->_options[] = [
                    'value' => $category->getId(),
                    'label' => "{$category->getName()} [ID: {$category->getId()}]",
                ];
            }
        } else {
            array_unshift($this->_options, $options);
        }

        return $this->_options;
    }
}
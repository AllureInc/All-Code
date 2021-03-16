<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import\Config\Source;

/**
 * Class Behaviour
 * @package Plenty\Core\Plugin\ImportExport\Model\Import\Config\Source
 */
class Behaviour implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
     * Return options array
     *
     * @return array
     */
    public function toOptionArray()
    {
        if (!$this->_options) {
            $this->_options = [
                ['value' => \Magento\ImportExport\Model\Import::BEHAVIOR_APPEND, 'label' => __('Add/Update')], 
                ['value' => \Magento\ImportExport\Model\Import::BEHAVIOR_REPLACE, 'label' => __('Replace')], 
                ['value' => \Magento\ImportExport\Model\Import::BEHAVIOR_DELETE, 'label' => __('Delete')], 
            ];
        }

        return $this->_options;
    }
}

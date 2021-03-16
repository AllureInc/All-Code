<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Behaviour
 * @package Plenty\Core\Model\Profile\Config\Source
 */
class Behaviour implements OptionSourceInterface
{
    /**#@+
     * Import behaviors
     */
    const APPEND        = 'append';
    const ADD_UPDATE    = 'add_update';
    const REPLACE       = 'replace';
    const DELETE        = 'delete';
    const CUSTOM        = 'custom';

    /**
     * Options array
     *
     * @var array
     */
    protected $_options;

    /**
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
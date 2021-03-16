<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\Import\Config\Source;

use \Magento\ImportExport\Model\Import\ErrorProcessing\ProcessingErrorAggregatorInterface;

/**
 * Class ValidationStrategy
 * @package Plenty\Core\Plugin\ImportExport\Model\Import\Config\Source
 */
class ValidationStrategy implements \Magento\Framework\Option\ArrayInterface
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
                ['value' => ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_STOP_ON_ERROR, 'label' => __('Stop on Error')], 
                ['value' => ProcessingErrorAggregatorInterface::VALIDATION_STRATEGY_SKIP_ERRORS, 'label' => __('Skip error entries')], 
            ];
        }

        return $this->_options;
    }
}

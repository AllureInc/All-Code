<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config\Source;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class DebugLevel
 * @package Plenty\Core\Model\Profile\Config\Source
 */
class DebugLevel implements OptionSourceInterface
{
    const API_ERROR                 = 1;
    const API_RESPONSE_HEADER       = 2;
    const API_RESPONSE_BODY_SHORT   = 3;
    const API_RESPONSE_BODY_FULL    = 4;
    const API_REQUEST_BODY          = 5;

    public function toOptionArray()
    {
        return [
            ['value' => self::API_ERROR, 'label' => __('API Error')],
            ['value' => self::API_RESPONSE_HEADER, 'label' => __('API Response Headers')],
            ['value' => self::API_RESPONSE_BODY_SHORT, 'label' => __('API Response Body (short)')],
            ['value' => self::API_RESPONSE_BODY_FULL, 'label' => __('API Response Body (full)')],
            ['value' => self::API_REQUEST_BODY, 'label' => __('API Request Body')]
        ];
    }
}
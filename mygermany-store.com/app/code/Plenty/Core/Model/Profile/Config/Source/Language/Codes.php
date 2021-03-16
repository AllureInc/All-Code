<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config\Source\Language;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Codes
 * @package Plenty\Core\Model\Profile\Config\Source\Language
 */
class Codes implements OptionSourceInterface
{
    const BG =	'bg';
    const CN =	'cn';
    const CZ =	'cz';
    const DA =	'da';
    const DE =	'de';
    const EN =	'en';
    const ES =	'es';
    const FR =	'fr';
    const IT =	'it';
    const NL =	'nl';
    const NN =	'nn';
    const PL =	'pl';
    const PT =	'pt';
    const RO =	'ro';
    const RU =	'ru';
    const SE =	'se';
    const SK =	'sk';
    const TR =	'tr';
    const VN =	'vn';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::BG,  'label' => __('(Bulgarian)')],
            ['value' => self::CN,  'label' => __('(Chinese)')],
            ['value' => self::CZ,  'label' => __('(Czech)')],
            ['value' => self::DA,  'label' => __('(Danish)')],
            ['value' => self::DE,  'label' => __('(German)')],
            ['value' => self::EN,  'label' => __('(English)')],
            ['value' => self::ES,  'label' => __('(Spanish)')],
            ['value' => self::FR,  'label' => __('(French)')],
            ['value' => self::IT,  'label' => __('(Italian)')],
            ['value' => self::NL,  'label' => __('(Dutch)')],
            ['value' => self::NN,  'label' => __('(Norwegian)')],
            ['value' => self::PL,  'label' => __('(Polish)')],
            ['value' => self::PT,  'label' => __('(Portuguese)')],
            ['value' => self::RO,  'label' => __('(Romanian)')],
            ['value' => self::RU,  'label' => __('(Russian)')],
            ['value' => self::SE,  'label' => __('(Swedish)')],
            ['value' => self::SK,  'label' => __('(Slovak)')],
            ['value' => self::TR,  'label' => __('(Turkish)')],
            ['value' => self::VN,  'label' => __('(Vietnamese)')]
        ];
    }
}
<?php
namespace Kerastase\Core\Plugin\Directory\Currency;


class PriceFormatPrecision
{
    public function aroundFormatPrecision (
        $subject,
        $proceed,
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    )
    {
        $result = $proceed(
            $price,
            0,
            $options,
            $includeContainer,
            $addBrackets
        );
        
        return $result;
    }
}
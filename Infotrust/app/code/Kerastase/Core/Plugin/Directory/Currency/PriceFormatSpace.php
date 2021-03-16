<?php
namespace Kerastase\Core\Plugin\Directory\Currency;

class PriceFormatSpace
{
    public function afterGetOutputFormat (
        $subject,
        $result
    ) {
        return str_replace('%s', '%s ', $result);
    }
}
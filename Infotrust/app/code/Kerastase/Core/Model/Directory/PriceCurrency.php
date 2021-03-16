<?php
namespace  Kerastase\Core\Model\Directory;

class PriceCurrency extends \Magento\Directory\Model\PriceCurrency
{
    const DEFAULT_PRECISION = 0;

    /**
     * {@inheritdoc}
     */
    public function format(
        $amount,
        $includeContainer = true,
        $precision = self::DEFAULT_PRECISION,
        $scope = null,
        $currency = null
    ) {
        return $this->getCurrency($scope, $currency)
            ->formatPrecision($amount, 0, [], $includeContainer);
    }

    /**
     * Round price
     *
     * @param float $price
     * @return float
     */
    public function round($price)
    {
        return round($price, 0);
    }
}
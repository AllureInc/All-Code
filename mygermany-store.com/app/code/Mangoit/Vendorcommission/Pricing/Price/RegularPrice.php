<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\Vendorcommission\Pricing\Price;

use Magento\Framework\Pricing\Price\AbstractPrice;
use Magento\Framework\Pricing\Price\BasePriceProviderInterface;

/**
 * Class RegularPrice
 */
class RegularPrice extends \Magento\Catalog\Pricing\Price\RegularPrice
{
    /**
     * Price type
     */
    const PRICE_CODE = 'regular_price';

    /**
     * Get price value
     *
     * @return float|bool
     */
    public function getValue()
    {
        // $newObjectManager = \Magento\Framework\App\ObjectManager::getInstance();
        // $objectAction = $newObjectManager->get('Mangoit\Vendorcommission\Plugin\Action');
        // $tax = $objectAction->getCountryCode();        
        if ($this->value === null) {
            $price = $this->product->getPrice();
            $priceInCurrentCurrency = $this->priceCurrency->convertAndRound($price);
            $this->value = $priceInCurrentCurrency ? floatval($priceInCurrentCurrency) : false;  
        }

        /*$priceWithTax = ($this->value * $tax)/100 ;
        $priceWithTax = ($this->value + $priceWithTax);*/
        return $this->value;
    }
}

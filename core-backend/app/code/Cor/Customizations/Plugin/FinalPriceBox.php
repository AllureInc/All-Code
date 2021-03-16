<?php
/**
* Module name: Cor_Customizations
* Plugin class for removing display label before product price
*/
namespace Cor\Customizations\Plugin;

/*
* Main Class
*/
class FinalPriceBox
{
    /*
    * Function for overrid phtml file
    */
    public function beforeSetTemplate(\Magento\ConfigurableProduct\Pricing\Render\FinalPriceBox $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($template == 'Magento_ConfigurableProduct::product/price/final_price.phtml') {
            return ['Cor_Customizations::product/price/final_price.phtml'];
        } 
        else
        {
            return [$template];
        }
    }
}

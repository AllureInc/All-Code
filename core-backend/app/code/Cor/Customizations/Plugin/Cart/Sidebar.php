<?php
/**
* Module name: Cor_Customizations
* Plugin class for changing positions of divisions
*/
namespace Cor\Customizations\Plugin\Cart;

/*
* Main Class
*/
class Sidebar
{
    /*
    * Function for overrid phtml file
    */
    public function beforeSetTemplate(\Magento\Checkout\Block\Cart\Sidebar $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($template == 'Magento_Checkout::cart/minicart.phtml') {
            return ['Cor_Customizations::cart/minicart.phtml'];
        } 
        else
        {
            return [$template];
        }
    }
}
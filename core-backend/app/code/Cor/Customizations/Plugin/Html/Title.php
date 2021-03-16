<?php
/**
* Module name: Cor_Customizations
* Plugin class for adding back arrow.
*/
namespace Cor\Customizations\Plugin\Html;

/*
* Main Class
*/
class Title
{
    /*
    * Function for overrid phtml file
    */
    public function beforeSetTemplate(\Magento\Theme\Block\Html\Title $subject, $template)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        if ($template == 'Magento_Theme::html/title.phtml') {
            return ['Cor_Customizations::html/title.phtml'];
        } 
        else
        {
            return [$template];
        }
    }
}
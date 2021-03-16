<?php
namespace Mangoit\Marketplace\Model\Config\Source;

use Magento\Framework\Data\Form\Element\AbstractElement;

class Ranges extends \Magento\Config\Block\System\Config\Form\Field
{    
    protected function _getElementHtml(AbstractElement $element)
    {
        $element->setDisabled('disabled');
        return $element->getElementHtml();

    }
}

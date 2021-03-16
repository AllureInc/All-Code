<?php

namespace Mangoit\Marketplace\Block\Price;

/**
 * Mangoit Marketplace Sellerlist Block.
 */
class PriceCalculation extends \Magento\Framework\View\Element\Template
{

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Mangoit\Marketplace\Helper\Data $helper,
        \Magento\Framework\View\Result\PageFactory $pageFactory
    ) 
    {
        return parent::__construct($context);
    }
}

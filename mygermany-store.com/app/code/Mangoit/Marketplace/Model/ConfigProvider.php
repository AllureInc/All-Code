<?php

namespace Mangoit\Marketplace\Model;


use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class ConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;

    public function __construct(LayoutInterface $layout)
    {
        $this->_layout = $layout;
    }

    public function getConfig()
    {

        return [
            'dropship' => $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId('checkout_page_dropshipment')->toHtml(),
            'warehouse' => $this->_layout->createBlock('Magento\Cms\Block\Block')->setBlockId('checkout_page_warehouse')->toHtml()
        ];
    }
}
<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Observer;

use Magento\Framework\Event\ObserverInterface;

class CartAddRouter implements ObserverInterface
{
    /**
     * @var \Webkul\Preorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        \Webkul\MarketplacePreorder\Helper\CompleteProduct $preorderHelper
    ) {
        $this->_preorderHelper = $preorderHelper;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->_preorderHelper->createPreOrderProduct();
    }
}

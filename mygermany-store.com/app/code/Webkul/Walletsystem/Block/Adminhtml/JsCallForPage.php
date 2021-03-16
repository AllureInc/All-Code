<?php
/**
 * Wallet system
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Block\Adminhtml;

class JsCallForPage extends \Magento\Backend\Block\Template
{
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        array $data = []
    ) {
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * @var string
     */
    protected $_template = 'js.phtml';

    public function getUserHasWallet()
    {
      $orderId = $this->getRequest()->getParam('order_id');
      $order = $this->order->load($orderId);
      return $order->getCustomerId();
    }
}

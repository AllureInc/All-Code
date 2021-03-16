<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Block\Sales\Order;

use Magento\Sales\Model\Order;

class Walletsystem extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Order
     */
    protected $_order;

    /**
     * @var \Magento\Framework\DataObject
     */
    protected $_source;

    public function getSource()
    {
        return $this->_source;
    }

    public function displayFullSummary()
    {
        return true;
    }
    //add wallet amount in totals
    public function initTotals()
    {
        $parent = $this->getParentBlock();
        $this->_order = $parent->getOrder();
        $this->_source = $parent->getSource();
        $title = 'Wallet Amount';
        $store = $this->getStore();
        if ($this->_order->getWalletAmount() != 0) {
            $walletAmount = new \Magento\Framework\DataObject(
                [
                    'code' => 'walletamount',
                    'strong' => false,
                    'value' => $this->_order->getWalletAmount(),
                    'base_value' => $this->_order->getBaseWalletAmount(),
                    'label' => __($title),
                ]
            );
            $parent->addTotal($walletAmount, 'walletamount');
        }
        return $this;
    }

    /**
     * Get order store object.
     *
     * @return \Magento\Store\Model\Store
     */
    public function getStore()
    {
        return $this->_order->getStore();
    }
    /**
     * @return Order
     */
    public function getOrder()
    {
        return $this->_order;
    }
    /**
     * @return array
     */
    public function getLabelProperties()
    {
        return $this->getParentBlock()->getLabelProperties();
    }
    /**
     * @return array
     */
    public function getValueProperties()
    {
        return $this->getParentBlock()->getValueProperties();
    }
}

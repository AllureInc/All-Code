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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;

class CartUpdateItemsAfter implements ObserverInterface
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_messageManager;

    /**
     * @param \Magento\Store\Model\StoreManagerInterface  $storeManager
     * @param \Webkul\Walletsystem\Helper\Data            $helper
     * @param \Magento\Checkout\Model\Session             $checkoutSession
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Message\ManagerInterface $messageManager
    ) {
        $this->_storeManager = $storeManager;
        $this->_helper = $helper;
        $this->_messageManager = $messageManager;
        $this->checkoutSession = $checkoutSession;
    }

    /**
     * customer register event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $walletProductId = $this->_helper->getWalletProductId();
        $cart = $observer->getCart()->getQuote()->getAllItems();
        foreach ($cart as $item) {
            $productId = $item->getProductId();
            if ($productId == $walletProductId) {
                $this->updateItemQty($item);
                $this->_messageManager->addNotice(
                    __("You can not update wallet product's quantity.")
                );
            }
        }
    }

    public function updateItemQty($item)
    {
        $item->setQty(1);
        $item->save();
    }
}

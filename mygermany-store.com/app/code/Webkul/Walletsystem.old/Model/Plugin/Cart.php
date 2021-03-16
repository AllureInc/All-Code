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

namespace Webkul\Walletsystem\Model\Plugin;

use Magento\Framework\Exception\LocalizedException;

class Cart
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;
    /**
     * @var \Magento\Quote\Model\Quote
     */
    protected $quote;
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    protected $orderRegistry;
    /**
     * @param WebkulWalletsystemHelperData   $walletHelper
     * @param MagentoCheckoutModelSession    $checkoutSession
     * @param MagentoFrameworkAppRequestHttp $request
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Registry $registry
    ) {
        $this->_walletHelper = $walletHelper;
        $this->quote = $checkoutSession->getQuote();
        $this->_request = $request;
        $this->orderRegistry = $registry;
    }

    public function beforeAddProduct(
        \Magento\Checkout\Model\Cart $subject,
        $productInfo,
        $requestInfo = null
    ) {
        $params = $this->_request->getParams();
        $flag = 0;
        $productId = 0;
        $items = [];
        $walletProductId = $this->_walletHelper->getWalletProductId();
        if (array_key_exists('product', $params)) {
            $productId = $params['product'];
        } elseif (array_key_exists('order_id', $params)) {
            $order = $this->orderRegistry->registry('current_order');
            $items = $order->getItemsCollection();
        }
        $quote = $this->quote;
        $cartData = $quote->getAllItems();
        if ($productId) {
            if ($walletProductId == $productId) {
                $flag = 1;
            }
            if (count($cartData)) {
                foreach ($cartData as $item) {
                    $itemProductId = $item->getProductId();
                    if ($walletProductId == $itemProductId) {
                        if ($flag != 1) {
                            throw new LocalizedException(__('You can not add other product with wallet product'));
                        }
                    } else {
                        if ($flag == 1) {
                            throw new LocalizedException(__('You can not add wallet product with other product'));
                        }
                    }
                }
            }
        } elseif (count($items)) {
            $walletInOrder = $this->checkIfOrderHaveWalletProduct($items, $walletProductId);
            if (count($cartData)) {
                foreach ($cartData as $item) {
                    $itemProductId = $item->getProductId();
                    if ($walletProductId == $itemProductId) {
                        if (!$walletInOrder) {
                            throw new LocalizedException(__('You can not add other product with wallet product'));
                        }
                    } else {
                        if ($walletInOrder) {
                            throw new LocalizedException(__('You can not add wallet product with other product'));
                        }
                    }
                }
            }
        }
        return [$productInfo, $requestInfo];
    }
    public function checkIfOrderHaveWalletProduct($items, $walletProductId)
    {
        foreach ($items as $item) {
            $productId = $item->getproduct()->getId();
            if ($productId == $walletProductId) {
                return true;
            }
        }
        return false;
    }
}

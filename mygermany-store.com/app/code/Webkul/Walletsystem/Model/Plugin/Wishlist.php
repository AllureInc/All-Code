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
use Magento\Framework\Controller\ResultFactory;

class Wishlist
{
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Controller\ResultFactory $resultFactory,
        \Magento\Framework\Registry $registry
    ) {
        $this->_walletHelper = $walletHelper;
        $this->_messageManager = $messageManager;
        $this->quote = $checkoutSession->getQuote();
        $this->_request = $request;
        $this->_resultFactory = $resultFactory;
        $this->orderRegistry = $registry;
    }

    public function beforeExecute(
        \Magento\Wishlist\Controller\Index\Cart $subject
    ) {
    
        $params = $this->_request->getParams();
        $flag = 0;
        $productId = 0;
        $items = [];
        $walletProductId = $this->_walletHelper->getWalletProductId();

        $quote = $this->quote;
        $cartData = $quote->getAllItems();
        if (count($cartData)) {
            foreach ($cartData as $item) {
                if ($item->getProductId() == $walletProductId) {
                    $flag = true;
                }
            }
        }
        if ($flag) {
            $this->_messageManager->addError(__('You can not add other product with wallet product'));
            unset($params['item']);
            return $this->_request->setPostValue($params);
        }
    }
}

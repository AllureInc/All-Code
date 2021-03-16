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

class Product
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;
    /**
     * @var Magento\Framework\App\State
     */
    protected $_appState;

    /**
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\App\State     $appState
     */
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\App\State $appState
    ) {
        $this->_walletHelper = $walletHelper;
        $this->_appState = $appState;
    }

    public function aroundAddAttributeToSelect(
        \Magento\Catalog\Model\ResourceModel\Product\Collection $subject,
        \Closure $proceed,
        $attribute,
        $joinType = false
    ) {
        $appState = $this->_appState;
        $areCode = $appState->getAreaCode();
        $walletSku = $this->_walletHelper::WALLET_PRODUCT_SKU;
        $result = $proceed($attribute, $joinType = false);
        $code = \Magento\Backend\App\Area\FrontNameResolver::AREA_CODE;
        if ($appState->getAreaCode() == $code) {
            $result->addFieldToFilter('sku', ['neq' => $walletSku]);
        }

        return $result;
    }
}

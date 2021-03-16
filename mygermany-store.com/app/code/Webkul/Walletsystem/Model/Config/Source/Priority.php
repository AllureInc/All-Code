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

namespace Webkul\Walletsystem\Model\Config\Source;

use \Magento\Framework\App\Config\ScopeConfigInterface;
use Webkul\Walletsystem\Model\Walletcreditrules;

class Priority extends \Magento\Framework\DataObject implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var ScopeConfigInterface
     */
    protected $_appConfigScopeConfigInterface;

    /**
     * @param ScopeConfigInterface $appConfigScopeConfigInterface
     */
    public function __construct(
        ScopeConfigInterface $appConfigScopeConfigInterface
    ) {
        $this->_appConfigScopeConfigInterface = $appConfigScopeConfigInterface;
    }

    public function toOptionArray()
    {
        $retrunArray = [
            Walletcreditrules::WALLET_CREDIT_CONFIG_PRIORITY_PRODUCT_BASED => __('Product based'),
            Walletcreditrules::WALLET_CREDIT_CONFIG_PRIORITY_CART_BASED => __('Cart Based')
        ];
        return $retrunArray;
    }
}

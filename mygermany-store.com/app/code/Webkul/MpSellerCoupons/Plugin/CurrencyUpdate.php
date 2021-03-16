<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Plugin;

class CurrencyUpdate extends \Magento\Directory\Controller\Currency\SwitchAction
{
    public function beforeExecute()
    {
        $currency = (string)$this->getRequest()->getParam('currency');
        if ($currency) {
            $helper = $this->_objectManager->create('\Webkul\MpSellerCoupons\Helper\Data');
            $helper->updateCouponValue($currency);
        }
    }
}

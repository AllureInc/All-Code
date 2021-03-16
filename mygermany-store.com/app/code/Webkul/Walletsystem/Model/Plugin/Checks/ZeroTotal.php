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

namespace Webkul\Walletsystem\Model\Plugin\Checks;

class ZeroTotal
{
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;

    /**
     * @param \Webkul\Walletsystem\Helper\Data $walletHelper
     * @param \Magento\Framework\App\State     $appState
     */
    
    public function __construct(
        \Webkul\Walletsystem\Helper\Data $walletHelper
    ) {
        $this->_walletHelper = $walletHelper;
    }

    public function afterIsApplicable(
        \Magento\Payment\Model\Checks\ZeroTotal $subject,
        $result
    ) {
        if (!$result) {
            return $this->_walletHelper->getPaymentisEnabled();
        }
        return $result;
    }
}

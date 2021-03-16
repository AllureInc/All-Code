<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderRestore
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper CLass
 * For rendering data
 */
namespace Cnnb\OrderRestore\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    const MODULE_ENABLED = 'cnnb_order/order_restore/enable';

    const ORDER_COMMENT = 'cnnb_order/order_restore/order_comment';

    const ORDER_STATUS = 'cnnb_order/order_restore/order_status';

    const ORDER_RESTORE_BTN = 'cnnb_order/order_restore/order_restore_btn';

    public function __construct(
        Context $context
    ) {
        parent::__construct($context);
    }

    /**
     * @return mixed
     */
    public function isModuleEnable()
    {
        return $this->scopeConfig->getValue(self::MODULE_ENABLED, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderCommentText()
    {
        return $this->scopeConfig->getValue(self::ORDER_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderStatus()
    {
        return $this->scopeConfig->getValue(self::ORDER_STATUS, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderRestoreButtonTitle()
    {
        return $this->scopeConfig->getValue(self::ORDER_RESTORE_BTN, ScopeInterface::SCOPE_STORE);
    }
}

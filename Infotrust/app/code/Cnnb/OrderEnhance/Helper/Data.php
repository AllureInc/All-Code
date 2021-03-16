<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderEnhance
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Helper CLass
 * For rendering data
 */
namespace Cnnb\OrderEnhance\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;
use Magento\Framework\App\Helper\Context;
use Magento\Backend\Model\Auth\Session;
use Psr\Log\LoggerInterface;
use Magento\User\Model\User;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Data extends AbstractHelper
{
    const MODULE_ENABLED = 'cnnb_order/order_comment/enable';

    const UPDATED_BY_TEXT = 'cnnb_order/order_comment/order_history';

    const ORDER_INVOICE_COMMENT = 'cnnb_order/order_comment/order_invoice';

    const ORDER_INVOICE_SHIPMENT_COMMENT = 'cnnb_order/order_comment/order_invoice_shipment';

    const ORDER_SHIPMENT_COMMENT = 'cnnb_order/order_comment/order_shipment';

    const ORDER_CREDIT_MEMO_COMMENT = 'cnnb_order/order_comment/order_credit_memo';

    const ORDER_RETURN_COMMENT = 'cnnb_order/order_comment/order_return';

    const ORDER_RMA_COMMENT = 'cnnb_order/order_comment/order_rma';

    const ORDER_CANCEL_COMMENT = 'cnnb_order/order_comment/order_cancel';

    const ORDER_SEND_EMAIL_COMMENT = 'cnnb_order/order_comment/order_send_email';

    const ORDER_REORDER_COMMENT = 'cnnb_order/order_comment/order_reorder';

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * @var $_authSession
     */
    protected $_authSession;

    /**
     * @var $_adminUser
     */
    protected $_adminUser;

    /**
     * @var $_orderFactory
     */
    protected $_orderFactory;

    /**
     * @var $_orderCollection
     */
    protected $_orderCollection;

    public function __construct(
        Session $authSession,
        LoggerInterface $logger,
        User $adminUser,
        OrderFactory $orderFactory,
        CollectionFactory $orderCollection,
        Context $context
    ) {
        $this->_authSession = $authSession;
        $this->_logger = $logger;
        $this->_adminUser = $adminUser;
        $this->_orderFactory = $orderFactory;
        $this->_orderCollection = $orderCollection;
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
    public function getUpdatedByText()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::UPDATED_BY_TEXT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderInvoiceComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_INVOICE_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderInvoiceShipmentComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_INVOICE_SHIPMENT_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderShipmentComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_SHIPMENT_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderCreditMemoComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_CREDIT_MEMO_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderReturnComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_RETURN_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderRmaComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_RMA_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderCancelComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_CANCEL_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderSendEmailComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_SEND_EMAIL_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getOrderReorderComment()
    {
        $this->_logger->info("Class: ". __CLASS__.", Function: " . __FUNCTION__);
        $this->_logger->info("ScopeInterface:: SCOPE_STORE = ".ScopeInterface::SCOPE_STORE);
        return $this->scopeConfig->getValue(self::ORDER_REORDER_COMMENT, ScopeInterface::SCOPE_STORE);
    }

    public function getAdminUserDetails()
    {
        /* 
         * Admin User
         * getName, getFirstName, getLastName, getUserName,
         */
        $user = $this->_authSession->getUser();
        return $user;
    }

    public function getLoggedInAdminName()
    {
        return $this->getAdminUserDetails()->getName();
    }

    public function getLoggedInAdminId()
    {
        return $this->getAdminUserDetails()->getId();
    }

    public function getAdminUserData($id)
    {
        $user = $this->_adminUser->load($id);
        if (!empty($user->toArray())) {
            return $user->getName();
        }

        return false;
    }

    public function addCommentInOrder($orderId, $comment)
    {
        $order = $this->_orderFactory->create()->load($orderId);
        $history = $order->addStatusHistoryComment($comment);
        $history->setCreatedBy($this->getLoggedInAdminId());
        $order->setCreatedBy($this->getLoggedInAdminId());
        $history->save();
        $order->save();
    }
}

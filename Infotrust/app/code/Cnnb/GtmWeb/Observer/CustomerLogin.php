<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Observer for setting session variables
 */

namespace Cnnb\GtmWeb\Observer;

use Magento\Framework\Event\ObserverInterface;

class CustomerLogin implements ObserverInterface
{
    /**
     * @var _coreSession
     */
    protected $_coreSession;

    public function __construct(
        \Magento\Framework\Session\SessionManagerInterface $coreSession
    ) {
        $this->_coreSession = $coreSession;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $customer = $observer->getEvent()->getCustomer();
        $this->_coreSession->setCnnbCustomerId($customer->getId());
        $this->_coreSession->setIsLogin(1);
        $this->_coreSession->setIsRegister(0);
    }
}

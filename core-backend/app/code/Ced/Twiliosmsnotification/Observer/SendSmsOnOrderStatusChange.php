<?php
/**
 * CedCommerce
  *
  * NOTICE OF LICENSE
  *
  * This source file is subject to the Academic Free License (AFL 3.0)
  * You can check the licence at this URL: http://cedcommerce.com/license-agreement.txt
  * It is also available through the world-wide-web at this URL:
  * http://opensource.org/licenses/afl-3.0.php
  *
  * @category    Ced
  * @package     Ced_Twiliosmsnotification
  * @author      CedCommerce Core Team <connect@cedcommerce.com >
  * @copyright   Copyright CEDCOMMERCE (http://cedcommerce.com/)
  * @license     http://opensource.org/licenses/osl-3.0.php  Open Software License (OSL 3.0)
  */

namespace Ced\Twiliosmsnotification\Observer;

use Magento\Framework\Event\ObserverInterface;

class SendSmsOnOrderStatusChange implements ObserverInterface
{
    protected $_httpRequest;

    /**
     * @var \Magento\Framework\ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $_logger;
    
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Psr\Log\LoggerInterface $logger
    )
    {
        $this->_logger = $logger;
        $this->_objectManager = $objectManager;
        $this->_httpRequest = $this->_objectManager->create('Magento\Framework\App\Request\Http');
    }

    public function getHelper()
    {
        return $this->_objectManager->create('\Ced\Twiliosmsnotification\Helper\Data');
    }

    /**
     *
     * @param \Magento\Framework\Event\Observer $observer
     * @return $this
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if($this->getHelper()->isSectionEnabled('order_status/enable')) {
            $order = $observer->getOrder();
            if ($order instanceof \Magento\Sales\Model\Order) {
                if ($order->getStatus() == 'readyforpickup') {
                    try{
                        $smsto = $this->getHelper()->getTelephoneFromOrder($order);
                        $smsmsg = $this->getHelper()->getOrderStatusChangeMsg($order);
                        $this->getHelper()->sendSms($smsto, $smsmsg);

                        if($this->getHelper()->isOrderStatusNotificationEnabled() and $this->getHelper()->getAdminOrderStatusTelephone()) {
                            $state = $this->getHelper()->getStatusName($order->getState());
                            $smsto = $this->getHelper()->getAdminOrderStatusTelephone();
                            $smsmsg = __('Order status of %s has been changed to '.$state,$order->getIncrementId());
                                $this->getHelper()->sendSms($smsto, $smsmsg);
                        }
                    } catch (\Exception $e) {
                        $this->_logger->critical($e);
                    }
                }
            }
        }
    }
}

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

class SendSmsOnOrderCreated implements ObserverInterface
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
         if($this->getHelper()->isSectionEnabled('orders/enable')) {
             try {
                 $orders = $observer->getEvent()->getOrderIds();
                 $order = $this->_objectManager->get('\Magento\Sales\Model\Order')->load($orders['0']);
                 if ($order instanceof \Magento\Sales\Model\Order) {

                     $smsto = $this->getHelper()->getTelephoneFromOrder($order);
                     $smsmsg = $this->getHelper()->getMessage($order);
                     $this->getHelper()->sendSms($smsto, $smsmsg);

                     if($this->getHelper()->isSectionEnabled('orders/notify') and $this->getHelper()->getAdminTelephone()) {
                         $smsto = $this->getHelper()->getAdminTelephone();
                         $smsmsg = __('A new order has been placed: %s',$order->getIncrementId());
                         try {
                             $this->getHelper()->sendSms($smsto, $smsmsg);
                         } catch (\Magento\Framework\Exception $e) {
                             $this->_logger->critical($e);
                         }
                     }
                 }
             } catch (\Exception $e) {
                 $this->_logger->critical($e);
             }
         }
    }
}

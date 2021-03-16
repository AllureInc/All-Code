<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Controller For Mass action
 * For sending whatsapp notification
 */

namespace Cnnb\WhatsappApi\Controller\Adminhtml\Order;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Magento\Backend\App\Action\Context;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use Magento\Sales\Api\OrderManagementInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Customer\Model\CustomerFactory;
use Cnnb\WhatsappApi\Helper\Data as CnnbHelper;
use Cnnb\WhatsappApi\Model\Notification as NotificationModel;
use Cnnb\WhatsappApi\Logger\Logger as CnnbLogger;
use Cnnb\WhatsappApi\Helper\OrderHistoryHelper as OrderHistory;
use Magento\Store\Model\ScopeInterface;

/**
 * Class MassDelete
 */
class Notification extends \Magento\Sales\Controller\Adminhtml\Order\AbstractMassAction
{
    /**
     * @var $messageManager
     */
    protected $_messageManager;

    /**
     * @var $storeManager
     */
    private $_storeManager;

    /**
     * @var $customerFactory
     */
    private $_customerFactory;

    /**
     * @var $cnnbHelper
     */
    protected $_cnnbHelper;

    /**
     * @var $logger
     */
    protected $_logger;

    /**
     * @var $_notificationModel
     */
    protected $_notificationModel;

    /**
     * @var $notificationSentOrderIds
     */
    protected $notificationSentOrderIds = [];

    /**
     * @var $isNotificationSent
     */
    protected $isNotificationSent = false;

    /**
     * @var $_orderHistory
     */
    protected $_orderHistory;

    /**
     * @var $_currency
     */
    protected $_currency;

    /**
     * Active cron
     */
    const ORDER_STATUS = 'cnnb_whatsappapi/orderplace/order_status';


    /**
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param messageManager $messageManager
     * @param customerFactory $customerFactory
     * @param cnnbHelper $cnnbHelper
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        StoreManagerInterface $storeManager,
        CustomerFactory $customerFactory,
        NotificationModel $notificationModel,
        CnnbHelper $cnnbHelper,
        CnnbLogger $customLogger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Directory\Model\Currency $currency,
        OrderHistory $orderHistory
    ) {
        parent::__construct($context, $filter);
        $this->collectionFactory = $collectionFactory;
        $this->_messageManager = $messageManager;
        $this->_storeManager = $storeManager;
        $this->_customerFactory = $customerFactory;
        $this->_cnnbHelper = $cnnbHelper;
        $this->_notificationModel = $notificationModel;
        $this->_scopeConfig = $scopeConfig;
        $this->_orderHistory = $orderHistory;
        $this->_currency = $currency;
        $this->_logger = $customLogger;
    }

    /**
     * Hold selected orders
     *
     * @param AbstractCollection $collection
     * @return \Magento\Backend\Model\View\Result\Redirect
     */
    protected function massAction(AbstractCollection $collection)
    {
        $this->_logger->info(' Class | Cnnb\WhatsappApi\Controller\Adminhtml\Order\Notification');
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        $resultRedirect->setPath('sales/order/index/');
        $warningData = [];
        $orderStatusData = [];
        $isWarning = false;
        $orderStatusFlag = false;
        $counter = 0;
        $notificationAlreadySent = false;
        $notificationAlreadySentArray = [];
        $alreadyNotifiedOrders = $this->getAlreadySentNotificationsOrderIds();
        $m1 = 'Authentication has been failed. Please check the whatsapp configuration.';
        $m2 = 'Notification has been send to all eligible orders.';
        $m3 = 'Mobile number not found in these order ids ';
        $m4 = 'Following orders status are not in pending ';
        $m5 = 'Notifications for following orders has been sent already. ';
        $authCode = $this->_cnnbHelper->getAuthToken();
        if (!$authCode) {
            $this->_messageManager->addErrorMessage($m1);
            return $resultRedirect;
        }
        $orderStatus = explode(',', $this->getAllOrderStatus());

        foreach ($collection->getItems() as $order) {
            $this->_logger->info(' Processing Order ID: '.$order->getIncrementId());
            if (!in_array($order->getIncrementId(), $alreadyNotifiedOrders)) {
                if (in_array($order->getStatus(), $orderStatus)) {
                    $storeId = $order->getStoreId();
                    $addressData = $this->getCustomerAddress($order);
                    if ($addressData['phone_number'] != "") {
                        $sendMessage = $this->_cnnbHelper->sendWhatsAppNotification($storeId, $addressData, $authCode);
                        $counter++;
                        $messageResponse = $this->checkResponse($sendMessage, $order, $resultRedirect);
                        $this->_orderHistory->addCommentToOrder($order->getId(), $addressData['phone_number']);
                        $this->_logger->info('--- Order Comment has been added: '.$order->getId().' ---');
                        $this->_logger->info('--------------------------------');
                    } else {
                        $warningData[] = $order->getIncrementId();
                        $isWarning = true;
                    }
                } else {
                    $orderStatusData[] = $order->getIncrementId();
                    $orderStatusFlag = true;
                }
            } else {
                $notificationAlreadySent = true;
                $notificationAlreadySentArray[] = $order->getIncrementId();
            }
        }

        if ($this->isNotificationSent == true) {
            $this->_messageManager->addSuccessMessage($m2 .implode(', ', $this->notificationSentOrderIds));
        }
        if ($isWarning == true) {
            $this->_messageManager->addWarningMessage($m3 .implode(', ', $warningData));
        }
        if ($orderStatusFlag == true) {
            $this->_messageManager->addWarningMessage($m4 .implode(', ', $orderStatusData));
        }
        if ($notificationAlreadySent == true) {
            $this->_messageManager->addWarningMessage($m5 .implode(', ', $notificationAlreadySentArray));
        }

        $this->_logger->info('Total: '.$counter);
        $this->_logger->info('');

        return $resultRedirect;
    }

     /**
      * Function for checking response of whatsapp API
      */
    public function checkResponse($sendMessage, $order, $resultRedirect)
    {
        if (!$sendMessage['result']) {
            if ($sendMessage['code'] == 'curl_err') {
                $this->_messageManager->info('Something went wrong while sending whatsapp notification.');
                return $resultRedirect;
            }
        } else {
            $this->notificationSentOrderIds[] = $order->getIncrementId();
            $this->isNotificationSent = true;
        }
    }

    /**
     * Function for getting address data of customers
     *
     * @return array
     */
    public function getCustomerAddress($order)
    {
        $customerAddress = [];
        $shippingAddress[] = $order->getShippingAddress();
        $currency_code = $this->_storeManager->getStore()->getCurrentCurrencyCode();
        $currency_symbol = $this->_currency->load($currency_code)->getCurrencySymbol();
        if ($shippingAddress != null) {
            foreach ($shippingAddress as $address) {
                $streetData = $address->getStreet();
                $street = implode(',', $streetData);
                $customerAddress = [
                    "phone_number" => $address->getTelephone(),
                    "$1" => $address->getName(),
                    "$2" => $order->getIncrementId(),
                    "name" => $address->getName(),
                    "unassing_and_archive" => true,
                    "$3" => $currency_symbol.number_format((float)$order->getBaseGrandTotal(), 2, '.', ','),
                    "$4" => $address->getRegion(),
                    "$5" => $address->getCity(),
                    "$6" => $street,
                    "$7" => $address->getTelephone(),
                    "$8" => $address->getTelephone()
                ];
            }
        }
        
        return $customerAddress;
    }

    /**
     * Function for getting array of already send messages
     *
     * @return array
     */
    public function getAlreadySentNotificationsOrderIds()
    {
        $data = [];
        $model = $this->_notificationModel;
        $collection = $this->_notificationModel->getCollection();
        foreach ($collection as $notification) {
            $data[] = $notification->getOrderId();
        }

        return $data;
    }

    /**
     *
     * @return array
     */
    public function getAllOrderStatus()
    {
        return $this->_scopeConfig->getValue(self::ORDER_STATUS, ScopeInterface::SCOPE_STORE);
    }
}

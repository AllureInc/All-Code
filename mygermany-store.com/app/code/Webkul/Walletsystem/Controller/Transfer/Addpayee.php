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

namespace Webkul\Walletsystem\Controller\Transfer;

use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;
use Webkul\Walletsystem\Model\WalletUpdateData;
use Webkul\Walletsystem\Model\Wallettransaction;
use Magento\Store\Model\StoreManagerInterface;

class Addpayee extends \Magento\Framework\App\Action\Action
{
    /**
     * @var PageFactory
     */
    protected $_resultPageFactory;
    /**
     * @var \Webkul\Walletsystem\Helper\Mail
     */
    protected $walletHelper;
    /**
     * @var Webkul\Walletsystem\Model\WalletUpdateData
     */
    protected $_walletUpdate;

    protected $_customerModel;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    protected $_jsonHelper;

    protected $walletPayee;
    protected $customerSession;
    /**
     * @param Context     $context
     * @param PageFactory $resultPageFactory
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        WalletUpdateData $walletUpdate,
        \Magento\Customer\Model\CustomerFactory $customerModel,
        StoreManagerInterface $storeManager,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Walletsystem\Model\WalletNotification $walletNotification,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->_walletUpdate = $walletUpdate;
        $this->_customerModel = $customerModel;
        $this->_walletNotification = $walletNotification;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig;
        $this->_jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        $this->customerSession = $customerSession;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if (!$this->customerSession->isLoggedIn()) {
            $result = [
                'backUrl' => $this->_url->getUrl('customer/account/login')
            ];
            return $this->getResponse()->representJson(
                $this->_jsonHelper->jsonEncode($result)
            );
        }
        $params = $this->getRequest()->getParams();
        $result = $this->validateParams($params);
        if (!$this->getRequest()->isAjax()) {
            return $this->resultRedirectFactory->create()->setPath(
                'walletsystem/transfer/index',
                ['_secure' => $this->getRequest()->isSecure()]
            );
        }
        return $this->getResponse()->representJson(
            $this->_jsonHelper->jsonEncode($result)
        );
    }
    protected function validateParams($params)
    {

        $result = [
            'error' => 0
        ];
        $error = 0;
        if (isset($params) && is_array($params) && count($params)
        && !preg_match('#<script(.*?)>(.*?)</script>#is', $params['nickname'])
        ) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'customer_id':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                    case 'customer_email':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                }
            }
            if ($error==1) {
                $result['error'] = 1;
                $result['error_msg'] = __("Please try again later");
            } else {
            }
            $customer = $this->_customerModel->create();
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
            if (isset($websiteId)) {
                $customer->setWebsiteId($websiteId);
            }
            $customer->loadByEmail($params['customer_email']);
            if ($customer && $customer->getId()) {
                if ($customer->getId() == $params['customer_id']) {
                    $result['error_msg'] = __("You can not add yourself in your payee list.");
                    $result['error'] = 1;
                } elseif ($this->alreadyAddedInPayee($params, $customer)) {
                    $result['error_msg'] = __("Customer with %1 email address id already present in payee list", $params['customer_email']);
                    $result['error'] = 1;
                } else {
                    $result = $this->addPayeeToCustomer($params, $customer);
                }
            } else {
                $result['error_msg'] = __(
                    "No customer found with email address %1",
                    $params['customer_email']
                );
                $result['error'] = 1;
            }
        } else {
            $result['error'] = 1;
            $result['error_msg'] = __(
                "Data is not validate"
            );
            $this->messageManager->addError(__("Data is not validate"));
        }
        return $result;
    }
    public function addPayeeToCustomer($params, $customer)
    {
        $payeeModel = $this->walletPayee->create();
        $configStatus = $this->walletHelper->getPayeeStatus();
        if (!$configStatus) {
            $status = $payeeModel::PAYEE_STATUS_ENABLE;
        } else {
            $status = $payeeModel::PAYEE_STATUS_DISABLE;
        }
        $payeeModel->setCustomerId($params['customer_id'])
            ->setNickName($params['nickname'])
            ->setPayeeCustomerId($customer->getEntityId())
            ->setStatus($status)
            ->setWebsiteId($customer->getWebsiteId())
            ->save();

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $payeeApprovalRequired = $this->scopeConfig->getValue('walletsystem/transfer_settings/payeestatus', $storeScope);
        if ($payeeApprovalRequired) {
            $this->setNotificationMessageForAdmin();
        }
        if ($payeeApprovalRequired) {
            $displayCustomMessage = $this->scopeConfig->getValue('walletsystem/transfer_settings/show_payee_message', $storeScope);
            if ($displayCustomMessage) {
                $message = __($this->scopeConfig->getValue('walletsystem/transfer_settings/show_payee_message_content', $storeScope));
            }
        }
        if (!isset($message)) {
            $message = __("Payee is added in your list");
        }
        $this->messageManager->addSuccess($message);
        $result = [
            'error' => 0,
            'success_msg' => __('Payee is added in your list'),
            'backUrl' => $this->_url->getUrl('walletsystem/transfer/index')
        ];
        return $result;
    }
    public function alreadyAddedInPayee($params, $customer)
    {
        $payeeModel = $this->walletPayee->create()->getCollection()
            ->addFieldToFilter('customer_id', $params['customer_id'])
            ->addFieldToFilter('payee_customer_id', $customer->getEntityId())
            ->addFieldToFilter('website_id', $customer->getWebsiteId());
        if ($payeeModel->getSize()) {
            return true;
        }
        return false;
    }

    public function setNotificationMessageForAdmin()
    {
        $notificationModel = $this->_walletNotification->getCollection();
        if (!$notificationModel->getSize()) {
            $this->_walletNotification->setPayeeCounter(1);
            $this->_walletNotification->save();
        } else {
            foreach ($notificationModel->getItems() as $notification) {
                $notification->setPayeeCounter($notification->getPayeeCounter()+1);
            }
        }
        $notificationModel->save();
    }
}

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

class Payeeupdate extends \Magento\Framework\App\Action\Action
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
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->_walletUpdate = $walletUpdate;
        $this->_customerModel = $customerModel;
        $this->storeManager = $storeManager;
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
        if (isset($params) && is_array($params)) {
            foreach ($params as $key => $value) {
                switch ($key) {
                    case 'id':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                    case 'nickname':
                        if ($value == '') {
                            $error = 1;
                        }
                        break;
                }
            }
            if ($error==1) {
                $result['error'] = 1;
                $result['error_msg'] = __("Please try again later");
                $this->messageManager->addError(__("Please try again later"));
            } else {
                $result = $this->updatePayeeNickName($params);
            }
        }
        return $result;
    }
    public function updatePayeeNickName($params)
    {
        $payeeModel = $this->walletPayee->create()->load($params['id']);
        $configStatus = $this->walletHelper->getPayeeStatus();
        if (!$configStatus) {
            $status = $payeeModel::PAYEE_STATUS_ENABLE;
        } else {
            $status = $payeeModel::PAYEE_STATUS_DISABLE;
        }
        $payeeModel->setNickName($params['nickname'])
            ->save();
        $this->messageManager->addSuccess(__("Payee is updated"));
        $result = [
            'error' => 0,
            'success_msg' => __('Payee is updated')
        ];
        return $result;
    }
}

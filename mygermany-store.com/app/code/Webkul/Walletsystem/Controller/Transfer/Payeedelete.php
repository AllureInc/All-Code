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

class Payeedelete extends \Magento\Customer\Controller\AbstractAccount
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
        \Webkul\Walletsystem\Model\WalletPayeeFactory $walletPayee
    ) {
        $this->_resultPageFactory = $resultPageFactory;
        $this->walletHelper = $walletHelper;
        $this->_walletUpdate = $walletUpdate;
        $this->_customerModel = $customerModel;
        $this->storeManager = $storeManager;
        $this->_jsonHelper = $jsonHelper;
        $this->walletPayee = $walletPayee;
        parent::__construct($context);
    }
    /**
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $params = $this->getRequest()->getParams();
        $this->validateParams($params);
        return $this->resultRedirectFactory->create()->setPath(
            'walletsystem/transfer/index',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
    protected function validateParams($params)
    {
        if (isset($params) && is_array($params) && array_key_exists('id', $params) && $params['id']!='') {
            $this->deletePayee($params);
        } else {
            $this->messageManager->addError(__("There is some error during executing this process, please try again later."));
        }
    }
    public function deletePayee($params)
    {
        $payeeModel = $this->walletPayee->create()->load($params['id']);
        $payeeModel->delete();
        $this->messageManager->addSuccess(__("Payee is successfully deleted"));
    }
}

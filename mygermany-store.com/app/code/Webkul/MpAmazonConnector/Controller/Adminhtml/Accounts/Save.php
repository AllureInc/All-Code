<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Accounts;

use Magento\Framework\Locale\Resolver;
use Webkul\MpAmazonConnector\Model\AccountsFactory;
use Webkul\MpAmazonConnector\Controller\Adminhtml\Accounts;

class Save extends Accounts
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

    /**
     * @var \Webkul\MpAmazonConnector\Helper\Data
     */
    private $dataHelper;

    /**
     * @param \Magento\Backend\App\Action\Context $context
     * @param \Magento\Framework\View\Result\PageFactory $resultPageFactory
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        AccountsFactory $accountsFactory,
        \Webkul\MpAmazonConnector\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountsFactory = $accountsFactory;
        $this->dataHelper =  $helper;
        $this->backendSession = $context->getSession();
        parent::__construct($context);
    }

    /**
     * @return void
     */
    public function execute()
    {
        $id = (int)$this->getRequest()->getParam('entity_id');
        $data = $this->getRequest()->getParams();
        if (!$data) {
            $this->_redirect('mpamazonconnect/*/');
            return;
        }
        $data = $this->getRawData($data);
        $amzClient = $this->dataHelper->validateAmzCredentials($data);
        if ($amzClient) {
            $extraInfo = $this->dataHelper->getAmazonParticipation($data['marketplace_id']);
            $data = array_merge($data, $extraInfo);
            $model = $this->accountsFactory->create()->load($id);

            if (isset($data['created_at'])) {
                unset($data['created_at']);
            }

            if (!$id && isset($data['seller_id'])) {
                $sellerAccountDetails = $this->accountsFactory
                                            ->create()
                                        ->getCollection()
                                        ->addFieldToFilter('seller_id', $data['seller_id']);
            } else {
                $sellerAccountDetails = $this->accountsFactory
                                            ->create()
                                        ->getCollection()
                                        ->addFieldToFilter('entity_id', $id);
            }
            
            if ($sellerAccountDetails->getSize()) {
                foreach ($sellerAccountDetails as $sellerAccount) {
                    $sellerAccount->addData($data);
                    $sellerAccount->save();
                }
            } else {
                $sellerAccount = $this->accountsFactory->create();
                $sellerAccount->addData($data);
                $id = $sellerAccount->save()->getEntityId();
            }
            
            $this->messageManager->addSuccess(
                __('Amazon information saved successfuly.')
            );
            $this->redirectToEdit($data, $id);
        } else {
            $this->messageManager->addError(__('Amazon account details are not correct'));
            $this->_redirect('mpamazonconnect/*/');
        }
    }

    /**
     * @param \Magento\User\Model\User $model
     * @param array $data
     * @return void
     */
    private function redirectToEdit(array $data, $id)
    {
        $this->_getSession()->setAmzAccountData($data);
        $data['entity_id'] = $id;
        $arguments = $data['entity_id'] ? ['id' => $data['entity_id']]: [];
        $arguments = array_merge(
            $arguments,
            ['_current' => true, 'active_tab' => $data['active_tab']]
        );
        if (isset($data['entity_id']) && isset($data['back'])) {
            $this->_redirect('mpamazonconnect/*/edit', $arguments);
        } else {
            $this->_redirect('mpamazonconnect/*/index', $arguments);
        }
    }

    /**
     * decrypt encrpted data
     *
     * @param array $data
     * @return array
     */
    private function getRawData($data)
    {
        $oldData = $this->backendSession->getData('mpamazon_config');
        if ($data['access_key_id'] === '*****') {
            $data['access_key_id'] = $oldData['access_key_id'];
        }
        if ($data['secret_key'] === '*****') {
            $data['secret_key'] = $oldData['secret_key'];
        }
        return $data;
    }

}

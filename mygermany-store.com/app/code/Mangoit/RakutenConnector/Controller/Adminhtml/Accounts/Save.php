<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Accounts;

use Magento\Framework\Locale\Resolver;
use Mangoit\RakutenConnector\Model\AccountsFactory;
use Mangoit\RakutenConnector\Controller\Adminhtml\Accounts;

class Save extends Accounts
{
     /**
      * @var \Magento\Framework\Controller\Result\JsonFactory
      */
    private $resultJsonFactory;

    /**
     * @var \Mangoit\RakutenConnector\Helper\Data
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
        \Mangoit\RakutenConnector\Helper\Data $helper
    ) {
        $this->resultPageFactory = $resultPageFactory;
        $this->accountsFactory = $accountsFactory;
        $this->dataHelper =  $helper;
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
            $this->_redirect('rakutenconnect/*/');
            return;
        }

        $rktnClient = $this->dataHelper->validateRakutenAccount($data);
        if ($rktnClient) {

            if (isset($data['marketplace_id'])) {
                $extraInfo = $this->dataHelper->getAmazonParticipation($data['marketplace_id']);
                $data = array_merge($data, $extraInfo);
            }
            
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
                __('Rakuten information saved successfuly.')
            );
            $this->redirectToEdit($data, $id);
        } else {
            $this->messageManager->addError(__('Rakuten account details are not correct'));
            $this->_redirect('rakutenconnect/*/');
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
            $this->_redirect('rakutenconnect/*/edit', $arguments);
        } else {
            $this->_redirect('rakutenconnect/*/index', $arguments);
        }
    }
}

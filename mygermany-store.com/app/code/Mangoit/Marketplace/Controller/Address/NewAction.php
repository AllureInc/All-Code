<?php
/**
 *
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Controller\Address;

use Magento\Framework\App\Action\HttpGetActionInterface as HttpGetActionInterface;

class NewAction extends \Magento\Customer\Controller\Address\NewAction
{
    protected $_helper;

    /**
     * @return \Magento\Framework\Controller\Result\Forward
     */
    public function execute()
    {
        $this->_helper = $this->_objectManager->create('Mangoit\Marketplace\Helper\Data');
        /** @var \Magento\Framework\Controller\Result\Forward $resultForward */
        $resultForward = $this->resultForwardFactory->create();
        $this->customerHasAddress();
        return $resultForward->forward('form');
    }

    public function customerHasAddress()
    {
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');

        if ($customerSession->getCustomerId()) {
            $sellerStatus = $this->getSellerStatus($customerSession->getCustomerId());
            if ($sellerStatus) {
                $customer = $customerModel->load($customerSession->getCustomerId());
                if ($customer->getDefaultBilling() || $customer->getDefaultShipping()) {
                    /*return true;*/
                } else {
                    $this->messageManager->addWarning($this->_helper->getAddAddressMessage());
                    /*return false;*/
                }
            } else {
                $this->messageManager->addSuccess($this->_helper->getProfileApprovalWaitingMessage());
            }
        } else {
            /*return true;*/
        } 

    }
    public function getSellerStatus($sellerId = 0)
    {
        $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
        $sellerStatus = 0;
        if (!$sellerId) {
            $sellerId = $customerSession->getCustomerId();
        }
        $webkulHelper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $model = $webkulHelper->getSellerCollectionObj($sellerId);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }
        return $sellerStatus;
    } 
}

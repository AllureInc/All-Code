<?php
/**
 * Mangoit Software.
 *
 * @category  Webkul
 * @package   Mangoit_Marketplace
 * @author    Mangoit
 * @copyright Copyright (c) 2010-2018 Mangoit
 */

namespace Mangoit\Marketplace\Controller\Seller;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\View\Result\PageFactory;

/**
 * Marketplace Seller Profile controller.
 */
class Profile extends \Webkul\Marketplace\Controller\Seller\Profile
{
    /**
     * Marketplace Seller's Profile Page.
     *
     * @return \Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        $helper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $customer = $this->_objectManager->create('Magento\Customer\Model\Session');
        if (!$helper->getSellerProfileDisplayFlag()) {
            $this->getRequest()->initForward();
            $this->getRequest()->setActionName('noroute');
            $this->getRequest()->setDispatched(false);

            return false;
        }
        $shopUrl = $helper->getProfileUrl();
        if (!$shopUrl) {
            $shopUrl = $this->getRequest()->getParam('shop');
        }
        if ($shopUrl) {
            $data = $helper->getSellerDataByShopUrl($shopUrl);
            $sellerData = $data->getFirstItem();
            $isProfileApproved = $sellerData->getIsProfileApproved();
            $trustworthy = $sellerData->getTrustworthy();
            $isSeller = false;
            if ($customer->isLoggedIn()) {
                $sellerId = $sellerData->getSellerId();
                $customerId = $customer->getCustomer()->getEntityId();
                if ($sellerId == $customerId) {
                    $isSeller = true;
                }
            }
            $adminLogin = $this->getRequest()->getParam('adminlogin');
             //if ($data->getSize() && ($isProfileApproved)) {
            if ($data->getSize() && ($isProfileApproved || ($trustworthy) || ($isSeller) || ($adminLogin))) {
                $resultPage = $this->_resultPageFactory->create();
                if($data->getFirstItem()->getVendorShopLayout() == 'layout2'){
                    $resultPage->addHandle('marketplace_seller_profile2');
                    $resultPage->getLayout()->getUpdate()->removeHandle('marketplace_seller_profile');
                }
                // Custom code to update shop layout End

                $resultPage->getConfig()->getTitle()->set(
                    __('Marketplace Seller Profile')
                );

                return $resultPage;
            }
        }

        return $this->resultRedirectFactory->create()->setPath(
            'marketplace',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }
}

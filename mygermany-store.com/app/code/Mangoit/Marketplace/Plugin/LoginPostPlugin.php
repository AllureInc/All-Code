<?php

/**
 *
 */
namespace Mangoit\Marketplace\Plugin;

/**
 *
 */
class LoginPostPlugin
{

    /**
     * Change redirect after login to home instead of dashboard.
     *
     * @param \Magento\Customer\Controller\Account $subject
     * @param \Magento\Framework\Controller\Result\Redirect $result
     */
    public function afterExecute(
        \Magento\Customer\Controller\Account\LoginPost $subject,
        $result)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $webkulHelper = $objectManager->create('Webkul\Marketplace\Helper\Data');
        $sellerData = 0;
        if (!empty($webkulHelper->getSellerData())) {
            $sellerData = $webkulHelper->getSellerData()->getFirstItem()->getBecomeSellerRequest();
        }
        
        if ($sellerData) {
            $result->setPath('marketplace/account/becomeseller/'); // Change this to what you want
        }
        return $result;
    }

}

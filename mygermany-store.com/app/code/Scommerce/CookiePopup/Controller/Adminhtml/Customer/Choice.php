<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Controller\Adminhtml\Customer;

/**
 * Class Choice
 * @package Scommerce\CookiePopup\Controller\Adminhtml\Customer
 */
class Choice extends \Magento\Customer\Controller\Adminhtml\Index
{
    /**
     * Choice Action
     *
     * @return \Magento\Framework\View\Result\Layout
     */
    public function execute()
    {
        $this->initCurrentCustomer();

        $resultLayout = $this->resultLayoutFactory->create();
        return $resultLayout;
    }
}
<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Plugin;

/**
 * Class ExportPlugin
 * @package Scommerce\CookiePopup\Plugin
 */
class ExportPlugin
{
    /** @var \Scommerce\CookiePopup\Model\Service\ChoiceService  */
    protected $choiceService;

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /**
     * ExportPlugin constructor.
     * @param \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */
    public function __construct(
        \Scommerce\CookiePopup\Model\Service\ChoiceService $choiceService,
        \Magento\Store\Model\StoreManagerInterface $storeManager
    )
    {
        $this->choiceService = $choiceService;
        $this->storeManager = $storeManager;
    }

    /**
     * Add Cookie choice data to export
     *
     * @param \Scommerce\Gdpr\Model\Service\Export $subject
     * @param $result
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @return string
     */
    public function afterExecute(\Scommerce\Gdpr\Model\Service\Export $subject, $result, \Magento\Customer\Model\Data\Customer $customer)
    {
        return $result . PHP_EOL . $subject->getStorage()->addRecord($this->getChoicesString($customer->getId()))->render();
    }

    /**
     * @param $customerId
     * @return array
     */
    private function getChoicesString($customerId)
    {
        $data = [];
        $choices = $this->choiceService->getCustomerChoices($customerId)->getItems();
        foreach ($choices as $choice) {
            $data[] = [
                $choice->getChoiceName(),
                $choice->getCookieName(),
                $this->getStoreName($choice->getStoreLinkId()),
                $choice->getStatus()
            ];
        }

        return
            [
                'Customer Cookie Settings',
                [
                    'Choice Name', 'Cookie Name', 'Store View', 'Status'
                ],
                $data
            ];
    }

    /**
     * @param $storeId
     * @return string
     */
    private function getStoreName($storeId)
    {
        return $this->storeManager->getStore($storeId)->getName();
    }
}
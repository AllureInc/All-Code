<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\Gdpr\Model\Service;

/**
 * Anonymize service
 *
 * Class Anonymize
 * @package Scommerce\Gdpr\Model\Service
 */
class Anonymize
{
    /** @var \Magento\Framework\Registry */
    private $registry;

    /** @var \Scommerce\Gdpr\Model\Service\Context */
    private $context;

    /** @var \Scommerce\Gdpr\Model\Service\Anonymize\Sale */
    private $sale;

    private $consentService;

    /**
     * @param \Magento\Framework\Registry $registry
     * @param \Scommerce\Gdpr\Model\Service\Context $context
     * @param \Scommerce\Gdpr\Model\Service\Anonymize\Sale $sale
     */
    public function __construct(
        \Magento\Framework\Registry $registry,
        \Scommerce\Gdpr\Model\Service\Context $context,
        \Scommerce\Gdpr\Model\Service\Anonymize\Sale $sale,
        \Scommerce\Gdpr\Model\Service\ConsentService $consentService

    ) {
        $this->registry = $registry;
        $this->context = $context;
        $this->sale = $sale;
        $this->consentService = $consentService;
    }

    /**
     * Anonymize customer data
     *
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @return bool
     * @throws \Exception
     */
    public function execute(\Magento\Customer\Model\Data\Customer $customer = null, $email = null)
    {
        $key = 'isSecureArea';
        $isSecure = $this->enable($key);
        try {
            $result = $this->exec($customer, $email);
            $this->restore($key, $isSecure);
            return $result;
        } catch (\Exception $e) {
            $this->restore($key, $isSecure);
        }
    }

    /**
     * Anonymize customer data
     *
     * @param \Magento\Customer\Model\Data\Customer $customer
     * @param null $email
     * @return bool
     */
    private function exec(\Magento\Customer\Model\Data\Customer $customer = null, $email = null)
    {
        if ($email == null) {
            $orders = $this->context->getOrderCollection($customer->getId());
        } else {
            $orders = $this->context->getOrderCollection()
                ->addFieldToFilter('customer_email', $email);
        }
        foreach ($orders as $order) {
            $this->sale->order($order);
        }

        if ($email == null) {
            $quotes = $this->context->getQuoteCollection($customer->getId());
        } else {
            $quotes = $this->context->getQuoteCollection()
                ->addFieldToFilter('customer_email', $email);
        }
        foreach ($quotes as $quote) {
            $this->sale->quote($quote);
        }

        if ($email == null) {
            $subscriber = $this->context->getSubscriber($customer->getEmail());
        } else {
            $subscriber = $this->context->getSubscriber($email);
        }
        if ($subscriber->getId()) {
            $subscriber->delete();
        }

        if ($email == null) {
            $this->consentService->deleteCustomerConsent($customer->getEmail(), $customer->getId());
        } else {
            $this->consentService->deleteCustomerConsent($email);
        }

        if ($email == null) {
            $this->context->getCustomerRepository()->delete($customer);
            $this->context->getHelper()->sendDeletionEmail($customer);
        }
        return true;
    }

    /**
     * Enable secure by registry
     *
     * @param string $key
     * @return bool|null
     */
    private function enable($key)
    {
        $value = $this->registry->registry($key);
        $this->registry->unregister($key);
        $this->registry->register($key, true);
        return $value;
    }

    /**
     * Restore registry secure value
     *
     * @param string $key
     * @param bool|null $value
     */
    private function restore($key, $value)
    {
        $this->registry->unregister($key);
        if ($value !== null) {
            $this->registry->register($key, $value);
        }
    }

    private function deleteConsentByEmail()
    {

    }
}

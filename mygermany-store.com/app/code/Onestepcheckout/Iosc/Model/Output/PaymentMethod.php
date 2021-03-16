<?php
/**
 * OneStepCheckout
 *
 * NOTICE OF LICENSE
 *
 * This source file is subject to One Step Checkout AS software license.
 *
 * License is available through the world-wide-web at this URL:
 * https://www.onestepcheckout.com/LICENSE.txt
 * If you did not receive a copy of the license and are unable to
 * obtain it through the world-wide-web, please send an email
 * to mail@onestepcheckout.com so we can send you a copy immediately.
 *
 * @category   onestepcheckout
 * @package    onestepcheckout_iosc
 * @copyright  Copyright (c) 2017 OneStepCheckout  (https://www.onestepcheckout.com/)
 * @license    https://www.onestepcheckout.com/LICENSE.txt
 */
namespace Onestepcheckout\Iosc\Model\Output;

class PaymentMethod implements OutputManagementInterface
{

    /**
     *
     * {@inheritDoc}
     * @see \Onestepcheckout\Iosc\Model\Output\OutputManagement::getOutputKey()
     */
    public function getOutputKey()
    {
        return 'paymentMethod';
    }

    public $scopeConfig = null;
    public $restrictedMethods = '';
    /**
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentInterface
     * @param \Magento\Framework\Api\SimpleDataObjectConverter $simpleDataObjectConverter
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Quote\Api\Data\PaymentExtensionFactory $paymentExtensionFactory
     * @param \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Quote\Api\PaymentMethodManagementInterface $paymentMethodManagement,
        \Magento\Quote\Api\Data\PaymentInterface $paymentInterface,
        \Magento\Framework\Api\SimpleDataObjectConverter $simpleDataObjectConverter,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Quote\Api\Data\PaymentExtensionFactory $paymentExtensionFactory,
        \Magento\Quote\Model\Quote\TotalsCollector $totalsCollector
    ) {

        $this->scopeConfig = $scopeConfig;
        $this->paymentMethodManagement = $paymentMethodManagement;
        $this->paymentInterface = $paymentInterface;
        $this->simpleDataObjectConverter = $simpleDataObjectConverter;
        $this->checkoutSession = $checkoutSession;
        $this->paymentExtensionFactory =  $paymentExtensionFactory;
        $this->totalsCollector = $totalsCollector;
    }

    /**
     * {@inheritDoc}
     * @see \Onestepcheckout\Iosc\Model\Output\OutputManagement::processPayload()
     */
    public function processPayload($input)
    {
        $data = [];

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->checkoutSession->getQuote();

        if (!$quote->getId()) {
            return $data;
        }

        if (isset($input[$this->getOutputKey()]) &&
            !$this->isRestricted($input[$this->getOutputKey()])
        ) {
            $shippingAddress = $quote->getShippingAddress();

            $shippingAddress->setCollectShippingRates(true);
            $quote->setOscCollectRates("1");

            $this->totalsCollector->collectAddressTotals($quote, $shippingAddress);

            try {
                $paymentData = $input[$this->getOutputKey()];
                if (! empty($paymentData)) {
                    if (isset($paymentData['extension_attributes']) &&
                        is_array($paymentData['extension_attributes'])
                    ) {
                        $paymentData['extension_attributes'] = $this->handleExtAttributes($paymentData);
                    }

                    $method = $this->paymentInterface;

                    foreach ($paymentData as $k => $v) {
                        $methodName = 'set' . $this->simpleDataObjectConverter->snakeCaseToUpperCamelCase($k);
                        if (method_exists($method, $methodName)) {
                            call_user_func([
                                $method,
                                $methodName
                            ], $v);
                        }
                    }
                }
                $this->paymentMethodManagement->set($quote->getId(), $method);
                $data['response']['selected']['success'] = true;
                $data['response']['selected']['error'] = false;
                $data['response']['selected']['message'] = $method->getMethod();
            } catch (\Exception $e) {
                $data['selected']['success'] = false;
                $data['selected']['error'] = true;
                $data['selected']['message'] = $e->getMessage();
            }
        }

        return $data;
    }

    /**
     *
     * @param array $paymentData
     * @return unknown
     */
    private function handleExtAttributes($paymentData)
    {
        $extensionAttributes = $this->paymentExtensionFactory->create();
        foreach ($paymentData['extension_attributes'] as $k => $v) {
            $methodName = 'set' . $this->simpleDataObjectConverter->snakeCaseToUpperCamelCase($k);
            if (method_exists($extensionAttributes, $methodName)) {
                call_user_func([
                    $extensionAttributes,
                    $methodName
                ], $v);
            }
        }
        $methodName = false;
        return $extensionAttributes;
    }

    /**
     * Match method against method codes that are allowed to be processed
     * @param array $method
     * @return boolean
     */
    public function isRestricted($input)
    {
        $match = $input['method'] ?? 'empty';
        return (boolean)in_array($match, $this->getRestrictedMethods());
    }

    /**
     *
     * @return array
     */
    public function getRestrictedMethods()
    {

        if (empty($this->restrictedMethods)) {
            $this->restrictedMethods = explode(
                ',',
                $this
                    ->scopeConfig
                    ->getValue(
                        'onestepcheckout_iosc/payments/skip_on_ajax',
                        \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                    ) ?? ''
            );
        }

        return $this->restrictedMethods;
    }
}

<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Plenty\Core\Rest\Client;
use Plenty\Order\Model\Logger;
use Plenty\Order\Rest\Response\PaymentDataBuilder;
use Plenty\Order\Helper\Data as Helper;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Payment
 * @package Plenty\Order\Rest
 */
class Payment extends AbstractOrder implements PaymentInterface
{
    /**
     * Payment constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param PaymentDataBuilder $paymentDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        PaymentDataBuilder $paymentDataBuilder
    ) {
        $this->_responseParser = $paymentDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return PaymentDataBuilder
     */
    public function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param int $page
     * @param null $paymentId
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchPayments(
        $page = 1,
        $paymentId = null
    ) : Collection {
        $params = ['page' => $page];

        try {
            $this->_api()
                ->get($this->_helper()->getPaymentsUrl($paymentId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param int $page
     * @param null $pluginKey
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchPaymentMethods(
        $page = 1,
        $pluginKey = null
    ) : Collection {
        $params = ['page' => $page];

        try {
            $this->_api()
                ->get($this->_helper()->getPaymentMethodsUrl($pluginKey) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse());
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param array $requestParams
     * @param null $paymentId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPayment(array $requestParams, $paymentId = null)
    {
        try {
            if (null === $paymentId) {
                $this->_api()
                    ->post($this->_helper()->getPaymentsUrl(), $requestParams);
            } else {
                $this->_api()
                    ->put($this->_helper()->getPaymentsUrl(), $requestParams);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param array $requestParams
     * @param bool $update
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPaymentMethod(array $requestParams, $update = false)
    {
        try {
            if (false !== $update) {
                $this->_api()
                    ->put($this->_helper()->getPaymentMethodsUrl(), $requestParams);
            } else {
                $this->_api()
                    ->post($this->_helper()->getPaymentMethodsUrl(), $requestParams);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param array $requestParams
     * @param null $propertyId
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPaymentProperty(array $requestParams, $propertyId = null)
    {
        try {
            if (null === $propertyId) {
                $this->_api()
                    ->post($this->_helper()->getPaymentPropertyUrl(), $requestParams);
            } else {
                $this->_api()
                    ->put($this->_helper()->getPaymentPropertyUrl($propertyId), $requestParams);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $paymentId
     * @param $orderId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPaymentOrderRelation($paymentId, $orderId)
    {
        try {
            $this->_api()
                ->post($this->_helper()->getPaymentOrderRelationUrl($paymentId, $orderId));

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param $paymentId
     * @param $contactId
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createPaymentContactRelation($paymentId, $contactId)
    {
        try {
            $this->_api()
                ->post($this->_helper()->getPaymentContactRelationUrl($paymentId, $contactId));

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}
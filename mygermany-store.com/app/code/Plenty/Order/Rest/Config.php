<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Plenty\Core\Rest\Client;
use Plenty\Core\Rest\Config as CoreConfig;
use Plenty\Core\Model\Logger;
use Plenty\Core\Rest\Response\ConfigDataBuilder;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Order\Api\Data\Config\SourceInterface;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class Config
 * @package Plenty\Order\Rest
 */
class Config extends CoreConfig implements ConfigInterface
{
    /**
     * Config constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param ConfigDataBuilder $configDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        ConfigDataBuilder $configDataBuilder
    ) {
        $this->_helper = $helper;
        $this->_responseParser = $configDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger, $configDataBuilder);
    }

    /**
     * @return Helper
     */
    protected function _helper()
    {
        return $this->_helper;
    }

    /**
     * @param int $page
     * @param null $statusId
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchOrderStatuses($page = 1, $statusId = null)
    {
        $params = ['page' => $page];

        try {
            $this->_api()
                ->get($this->_helper()->getOrderStatusesUrl($statusId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_ORDER_STATUS);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param int $page
     * @param null $methodId
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchPaymentMethods($page = 1, $methodId = null)
    {
        $params = ['page' => $page];

        try {
            $this->_api()
                ->get($this->_helper()->getPaymentMethodsUrl($methodId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_PAYMENT_METHOD);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }

    /**
     * @param int $page
     * @param null $profileId
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws NoSuchEntityException
     */
    public function getSearchShippingProfiles($page = 1, $profileId = null)
    {
        $params = ['page' => $page];

        try {
            $this->_api()
                ->get($this->_helper()->getShippingProfilesUrl($profileId) . '?' . http_build_query($params));
            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
            $response = $this->_responseParser()
                ->buildResponse($this->_api()->getResponse(), SourceInterface::CONFIG_SOURCE_SHIPPING_PROFILE);
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $response;
    }
}
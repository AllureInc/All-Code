<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Exception\LocalizedException;

use Plenty\Core\Rest\Client;
use Plenty\Customer\Helper\Data as CustomerHelper;
use Plenty\Customer\Model\Logger;
use Plenty\Customer\Rest\Response\ContactDataBuilder;
use Plenty\Order\Helper\Data as OrderHelper;


/**
 * Class Address
 * @package Plenty\Order\Rest
 */
class Address extends \Plenty\Customer\Rest\Address implements AddressInterface
{
    /**
     * Address constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param CustomerHelper $helper
     * @param Logger $logger
     * @param ContactDataBuilder $contactDataBuilder
     * @param OrderHelper $orderHelper
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        CustomerHelper $helper,
        Logger $logger,
        ContactDataBuilder $contactDataBuilder,
        OrderHelper $orderHelper
    ) {
        $this->_helper = $orderHelper;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger, $contactDataBuilder);
    }

    /**
     * @return CustomerHelper|OrderHelper
     */
    public function _helper()
    {
        return $this->_helper;
    }

    /**
     * @param $orderId
     * @param $relationTypeId
     * @return \Magento\Framework\Data\Collection|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getOrderAddress($orderId, $relationTypeId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getOrderAddressUrl($orderId, $relationTypeId));
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
     * @param array $request
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createOrderAddress(array $request)
    {
        try {
            $this->_api()
                ->post($this->_helper()->getOrderAddressUrl(), $request);

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

}
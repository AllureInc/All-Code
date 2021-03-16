<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest;

use Plenty\Core\Rest\Client;
use Plenty\Customer\Model\Logger;
use Plenty\Customer\Rest\Response\ContactDataBuilder;
use Plenty\Customer\Helper\Data as Helper;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\DataObject;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Address
 * @package Plenty\Customer\Rest
 */
class Address extends AbstractCustomer implements AddressInterface
{
    /**
     * Contact constructor.
     * @param Client $httpClientFactory
     * @param CollectionFactory $dataCollectionFactory
     * @param Helper $helper
     * @param Logger $logger
     * @param ContactDataBuilder $contactDataBuilder
     */
    public function __construct(
        Client $httpClientFactory,
        CollectionFactory $dataCollectionFactory,
        Helper $helper,
        Logger $logger,
        ContactDataBuilder $contactDataBuilder
    ) {
        $this->_responseParser = $contactDataBuilder;
        parent::__construct($httpClientFactory, $dataCollectionFactory, $helper, $logger);
    }

    /**
     * @return ContactDataBuilder
     */
    public function _responseParser()
    {
        return $this->_responseParser;
    }

    /**
     * @param $addressId
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAccountAddress($addressId)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getAccountAddressUrl($addressId));
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
     * @param $contactId
     * @param null $addressTypeId
     * @return Collection|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getContactAddress($contactId, $addressTypeId = null)
    {
        try {
            $this->_api()
                ->get($this->_helper()->getContactAddressUrl($contactId, $addressTypeId));
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
     * @param array $params
     * @param null $addressId
     * @param bool $delete
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createAccountAddress(array $params, $addressId = null, $delete = false)
    {
        try {
            if (null !== $addressId && $delete) {
                $this->_api()
                    ->delete($this->_helper()->getAccountAddressUrl($addressId));
            } elseif (null === $addressId) {
                $this->_api()
                    ->post($this->_helper()->getAccountAddressUrl(), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getAccountAddressUrl($addressId), $params);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }

    /**
     * @param array $params
     * @param $contactId
     * @param null $addressId
     * @param bool $delete
     * @return array|mixed
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createContactAddress(array $params, $contactId, $addressId = null, $delete = false)
    {
        if (empty($params) || !$contactId) {
            return [];
        }

        try {
            if (null !== $addressId && $delete) {
                $this->_api()
                    ->delete($this->_helper()->getContactAddressUrl($contactId, $addressId));
            } elseif (null === $addressId) {
                $this->_api()
                    ->post($this->_helper()->getContactAddressUrl($contactId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getContactAddressUrl($contactId, $addressId), $params);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}
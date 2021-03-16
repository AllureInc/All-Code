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
 * Class Contact
 * @package Plenty\Customer\Rest
 */
class Contact extends AbstractCustomer implements ContactInterface
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
     * @param int $page
     * @param null $contactId
     * @param null $with
     * @param null $name
     * @param null $email
     * @param null $contactEmail
     * @param null $contactAddress
     * @param null $countryId
     * @param null $postalCode
     * @param null $town
     * @param null $typeId
     * @param null $number
     * @param null $createdAtBefore
     * @param null $updatedAtBefore
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getSearchContacts(
        $page = 1,
        $contactId = null,
        $with = null, //  The following parameters are available: addresses, accounts, options, orderSummary, primaryBillingAddress.
        $name = null, // Filter that restricts the search result to contacts with a specific name
        $email = null, // Filter that restricts the search result to contacts with a specific email address
        $contactEmail = null, // Filter that restricts the search result to contacts resembling to the given email address
        $contactAddress = null, // Filter that restricts the search result to contacts with a specific address
        $countryId = null, // Filter that restricts the search result to contacts with a specific country
        $postalCode = null, // Filter that restricts the search result to contacts with a specific postal code
        $town = null, // Filter that restricts the search result to contacts with a specific town
        $typeId = null, // Filter that restricts the search result to contacts with a specific contact type
        $number = null, // Filter that restricts the search result to contacts with a specific number
        $createdAtBefore = null, // Filter that restricts the search result to contacts that were created after a specific date.
        $updatedAtBefore = null // Filter that restricts the search result to contacts that were updated before a specific date.
    ) : Collection {
        $params = ['page' => $page];
        $with ? $params['with'] = $with : null;
        $name ? $params['name'] = $name : null;
        $email ? $params['email'] = $email : null;
        $contactEmail ? $params['contactEmail'] = $contactEmail : null;
        // $contactId ? $params['contactId'] = $contactId : null;
        $contactAddress ? $params['contactAddress'] = $contactAddress : null;
        $countryId ? $params['countryId'] = $countryId : null;
        $postalCode ? $params['postalCode'] = $postalCode : null;
        $town ? $params['town'] = $town : null;
        $typeId ? $params['typeId'] = $typeId : null;
        $number ? $params['number'] = $number : null;
        $createdAtBefore ? $params['createdAtBefore'] = $createdAtBefore : null;
        $updatedAtBefore ? $params['updatedAtBefore'] = $updatedAtBefore : null;

        try {
            $this->_api()
                ->get($this->_helper()->getContactUrl($contactId) . '?' . http_build_query($params));
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
     * @param $contactEmail
     * @return Collection
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getContactByEmail($contactEmail) : DataObject
    {
        return $this->getSearchContacts(null, null, null, null, $contactEmail)
            ->getFirstItem();
    }

    /**
     * @param $contactId
     * @param null $addressTypeId
     * @return Collection
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
     * @param $request
     * @param null $contactId
     * @param bool $delete
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createContact($request, $contactId = null, $delete = false)
    {
        if (empty($request)) {
            return [];
        }

        try {
            if (null !== $contactId && $delete) {
                $this->_api()
                    ->delete($this->_helper()->getContactUrl($contactId));
            } elseif (null === $contactId) {
                $this->_api()
                    ->post($this->_helper()->getContactUrl(), $request);
            } else {
                $this->_api()
                    ->put($this->_helper()->getContactUrl($contactId), $request);
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
     * @param null $addressTypeId
     * @param bool $delete
     * @return array
     * @throws LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function createContactAddress(array $params, $contactId, $addressTypeId = null, $delete = false)
    {
        if (empty($params) || !$contactId) {
            return [];
        }

        try {
            if (null !== $addressTypeId && $delete) {
                $this->_api()
                    ->delete($this->_helper()->getContactAddressUrl($contactId, $addressTypeId));
            } elseif (null === $addressTypeId) {
                $this->_api()
                    ->post($this->_helper()->getContactAddressUrl($contactId), $params);
            } else {
                $this->_api()
                    ->put($this->_helper()->getContactAddressUrl($contactId, $addressTypeId), $params);
            }

            $this->_logResponse(__FUNCTION__, $this->getResponseFull($this->_getDebugLevel(), __METHOD__));
        } catch (\Exception $e) {
            $this->_logError($e->getMessage(), __METHOD__);
            throw new LocalizedException(__($e->getMessage()));
        }

        return $this->_api()->getResponse();
    }
}
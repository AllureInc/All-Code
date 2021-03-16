<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest;

use Magento\Framework\Data\Collection;

/**
 * Interface ContactInterface
 * @package Plenty\Customer\Rest
 */
interface ContactInterface
{
    /**
     * @return mixed
     */
    public function _responseParser();

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
    ) : Collection;

    /**
     * @param $contactEmail
     * @return mixed
     */
    public function getContactByEmail($contactEmail);

    /**
     * @param $request
     * @param null $contactId
     * @param bool $delete
     * @return mixed
     */
    public function createContact($request, $contactId = null, $delete = false);

    /**
     * @param $contactId
     * @param null $addressTypeId
     * @return mixed
     */
    public function getContactAddress($contactId, $addressTypeId = null);

    /**
     * @param array $params
     * @param $contactId
     * @param null $addressTypeId
     * @param bool $delete
     * @return mixed
     */
    public function createContactAddress(array $params, $contactId, $addressTypeId = null, $delete = false);
}
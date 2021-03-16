<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Helper\Rest;

/**
 * Interface RouteInterface
 * @package Plenty\Customer\Helper\Rest
 */
interface RouteInterface
{
    /** @see https://developers.plentymarkets.com/rest-doc/account */

    const ACCOUNTS_URL                         = '/rest/accounts';
    const ACCOUNT_LOGIN_URL                  = '/rest/account/login';
    const ACCOUNT_LOGOUT_URL        = '/rest/account/logout';
    const ACCOUNT_LOGIN_REFRESH_URL        = '/rest/account/login/refresh';
    const ACCOUNT_ADDRESS_URL        = '/rest/accounts/addresses';
    const ACCOUNT_ADDRESS_RELATION_TYPES_URL        = '/rest/accounts/addresses/relation_types';
    const ACCOUNT_ADDRESS_OPTION_TYPES_URL        = '/rest/accounts/addresses/option_types';
    const ACCOUNT_CONTACT_URL        = '/rest/accounts/contacts';
    const ACCOUNT_CONTACT_TYPES_URL        = '/rest/accounts/contacts/types';
    const ACCOUNT_CONTACT_POSITION_URL        = '/rest/accounts/contacts/positions';
    const ACCOUNT_CONTACT_DEPARTMENT_URL        = '/rest/accounts/contacts/departments';
    const ACCOUNT_CONTACT_OPTION_TYPES_URL        = '/rest/accounts/contacts/option_types';
    const ACCOUNT_CONTACT_OPTION_SUB_TYPES_URL        = '/rest/accounts/contacts/option_sub_types';
    const ACCOUNT_CONTACT_BANKS_URL        = '/rest/accounts/contacts/banks';
    const ACCOUNT_CONTACT_CLASSES_URL        = '/rest/accounts/contacts/classes';
    const ACCOUNT_CONTACT_EVENTS_URL        = '/rest/accounts/contacts/contact_events';

    /**
     * @param null $accountId
     * @return mixed
     */
    public function getAccountsUrl($accountId = null) : string;

    /**
     * @return string
     */
    public function getAccountLoginUrl() : string;

    /**
     * @return string
     */
    public function getAccountLogoutUrl() : string;

    /**
     * @return string
     */
    public function getAccountLoginRefreshUrl() : string;

    /**
     * @param null $addressId
     * @return string
     */
    public function getAccountAddressUrl($addressId = null) : string;

    /**
     * @return string
     */
    public function getAccountAddressRelationTypesUrl() : string;

    /**
     * @param null $optionTypeId
     * @return string
     */
    public function getAccountAddressOptionTypesUrl($optionTypeId = null) : string;

    /**
     * @param null $contactId
     * @return string
     */
    public function getContactUrl($contactId = null) : string;

    /**
     * @param null $typeId
     * @return string
     */
    public function getContactTypeUrl($typeId = null) : string;

    /**
     * @param null $positionId
     * @return string
     */
    public function getContactConfigPositionUrl($positionId = null) : string;

    /**
     * @param null $departmentId
     * @return string
     */
    public function getContactConfigDepartmentsUrl($departmentId = null) : string;

    /**
     * @param null $optionTypeId
     * @return string
     */
    public function getContactConfigOptionTypesUrl($optionTypeId = null) : string;

    /**
     * @param null $optionSubTypeId
     * @return string
     */
    public function getContactConfigOptionSubTypesUrl($optionSubTypeId = null) : string;

    /**
     * @param null $contactBankId
     * @return string
     */
    public function getContactBankAccountUrl($contactBankId = null) : string;

    /**
     * @param $contactId
     * @return string
     */
    public function getListContactBankAccountsUrl($contactId) : string;

    /**
     * @param $contactId
     * @param null $addressTypeId
     * @return string
     */
    public function getContactAddressUrl($contactId, $addressTypeId = null) : string;

    /**
     * @param $contactId
     * @param null $accountId
     * @return string
     */
    public function getContactAccountUrl($contactId, $accountId = null) : string;

    /**
     * @param $contactId
     * @param null $optionId
     * @return string
     */
    public function getContactOptionsUrl($contactId, $optionId = null) : string;

    /**
     * @param null $contactClassId
     * @return string
     */
    public function getContactClassesUrl($contactClassId = null) : string;

    /**
     * @param null $contactEventId
     * @return string
     */
    public function getContactEventsUrl($contactEventId = null) : string;
}

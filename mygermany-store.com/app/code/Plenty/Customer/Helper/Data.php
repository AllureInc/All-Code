<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Plenty\Customer\Helper\Rest\RouteInterface;
use Plenty\Core\Helper\Data as CoreHelper;

/**
 * Class Data
 * @package Plenty\Customer\Helper
 */
class Data extends CoreHelper implements RouteInterface
{
    // DEVELOPER
    const XML_PATH_ENABLE_DEBUGGING                          = 'plenty_customer/dev/debug_enabled';
    const XML_PATH_CONFIG_DEV_DEBUG_DIRECTORY_NAME           = 'plenty_customer/dev/debug_directory';
    const XML_PATH_CONFIG_DEV_DEBUG_LEVEL                    = 'plenty_customer/dev/debug_level';

    /**
     * @param null $accountId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountsUrl($accountId = null) : string
    {
        if (null === $accountId) {
            return $this->getAppUrl(self::ACCOUNTS_URL);
        }

        return $this->getAppUrl(self::ACCOUNTS_URL.'/'.$accountId);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountLoginUrl() : string
    {
        return $this->getAppUrl(self::ACCOUNT_LOGIN_URL);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountLogoutUrl() : string
    {
        return $this->getAppUrl(self::ACCOUNT_LOGIN_URL);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountLoginRefreshUrl() : string
    {
        return $this->getAppUrl(self::ACCOUNT_LOGIN_REFRESH_URL);
    }

    /**
     * @param null $addressId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountAddressUrl($addressId = null) : string
    {
        if (null === $addressId) {
            return $this->getAppUrl(self::ACCOUNT_ADDRESS_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_ADDRESS_URL.'/'.$addressId);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountAddressRelationTypesUrl() : string
    {
        return $this->getAppUrl(self::ACCOUNT_ADDRESS_RELATION_TYPES_URL);
    }

    /**
     * @param null $optionTypeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getAccountAddressOptionTypesUrl($optionTypeId = null) : string
    {
        if (null === $optionTypeId) {
            return $this->getAppUrl(self::ACCOUNT_ADDRESS_OPTION_TYPES_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_ADDRESS_OPTION_TYPES_URL.'/'.$optionTypeId);
    }

    /**
     * @param null $contactId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactUrl($contactId = null) : string
    {
        if (null === $contactId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId);
    }

    /**
     * @param null $typeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactTypeUrl($typeId = null) : string
    {
        if (null === $typeId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_TYPES_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_TYPES_URL.'/'.$typeId);
    }

    /**
     * @param null $positionId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactConfigPositionUrl($positionId = null) : string
    {
        if (null === $positionId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_POSITION_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_POSITION_URL.'/'.$positionId);
    }

    /**
     * @param null $departmentId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactConfigDepartmentsUrl($departmentId = null) : string
    {
        if (null === $departmentId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_DEPARTMENT_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_DEPARTMENT_URL.'/'.$departmentId);
    }

    /**
     * @param null $optionTypeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactConfigOptionTypesUrl($optionTypeId = null) : string
    {
        if (null === $optionTypeId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_OPTION_TYPES_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_OPTION_TYPES_URL.'/'.$optionTypeId);
    }

    /**
     * @param null $optionSubTypeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactConfigOptionSubTypesUrl($optionSubTypeId = null) : string
    {
        if (null === $optionSubTypeId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_OPTION_SUB_TYPES_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_OPTION_SUB_TYPES_URL.'/'.$optionSubTypeId);
    }

    /**
     * @param null $contactBankId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactBankAccountUrl($contactBankId = null) : string
    {
        if (null === $contactBankId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_BANKS_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_BANKS_URL.'/'.$contactBankId);
    }

    /**
     * @param $contactId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getListContactBankAccountsUrl($contactId) : string
    {
        return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/banks');
    }

    /**
     * @param $contactId
     * @param null $addressTypeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactAddressUrl($contactId, $addressTypeId = null) : string
    {
        if (null === $addressTypeId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/addresses');
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/addresses/'.$addressTypeId);
    }

    /**
     * @param $contactId
     * @param null $accountId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactAccountUrl($contactId, $accountId = null) : string
    {
        if (null === $accountId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/accounts');
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/accounts/'.$accountId);
    }

    /**
     * @param $contactId
     * @param null $optionId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactOptionsUrl($contactId, $optionId = null) : string
    {
        if (null === $optionId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/options');
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_URL.'/'.$contactId.'/options/'.$optionId);
    }

    /**
     * @param null $contactClassId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactClassesUrl($contactClassId = null) : string
    {
        if (null === $contactClassId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_CLASSES_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_CLASSES_URL.'/'.$contactClassId);
    }

    /**
     * @param null $contactEventId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getContactEventsUrl($contactEventId = null) : string
    {
        if (null === $contactEventId) {
            return $this->getAppUrl(self::ACCOUNT_CONTACT_EVENTS_URL);
        }

        return $this->getAppUrl(self::ACCOUNT_CONTACT_EVENTS_URL.'/'.$contactEventId);
    }

    /**
     * @return bool
     * @throws NoSuchEntityException
     */
    public function isDebugOn() : bool
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_DEBUGGING);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getLogPath()
    {
        return $this->_getConfig(self::XML_PATH_CONFIG_DEV_DEBUG_DIRECTORY_NAME) ?
            $this->_getConfig(self::XML_PATH_CONFIG_DEV_DEBUG_DIRECTORY_NAME) : 'core';
    }

    /**
     * @return array
     * @throws NoSuchEntityException
     */
    public function getDebugLevel()
    {
        return explode(',', $this->_getConfig(self::XML_PATH_CONFIG_DEV_DEBUG_LEVEL));
    }
}
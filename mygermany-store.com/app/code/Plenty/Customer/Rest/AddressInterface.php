<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest;

use Magento\Framework\Data\Collection;

/**
 * Interface AddressInterface
 * @package Plenty\Customer\Rest
 */
interface AddressInterface
{
    /**
     * @return mixed
     */
    public function _responseParser();

    /**
     * @param $addressId
     * @return mixed
     */
    public function getAccountAddress($addressId);

    /**
     * @param $contactId
     * @param null $addressTypeId
     * @return mixed
     */
    public function getContactAddress($contactId, $addressTypeId = null);

    /**
     * @param array $params
     * @param null $addressId
     * @param bool $delete
     * @return mixed
     */
    public function createAccountAddress(array $params, $addressId = null, $delete = false);

    /**
     * @param array $params
     * @param $contactId
     * @param null $addressId
     * @param bool $delete
     * @return mixed
     */
    public function createContactAddress(array $params, $contactId, $addressId = null, $delete = false);
}
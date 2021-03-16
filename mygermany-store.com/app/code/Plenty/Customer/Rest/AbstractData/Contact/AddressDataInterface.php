<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest\AbstractData\Contact;

/**
 * Interface AddressDataInterface
 * @package Plenty\Customer\Rest\AbstractData\Contact
 */
interface AddressDataInterface
{
    const TYPE_ID_BILLING_ADDRESS       = 1;
    const TYPE_ID_DELIVERY_ADDRESS      = 2;
    const TYPE_ID_SENDER_ADDRESS        = 3;
    const TYPE_ID_RETURN_ADDRESS        = 4;
    const TYPE_ID_CLIENT_ADDRESS        = 5;
    const TYPE_ID_CONTRACTOR_ADDRESS    = 6;
    const TYPE_ID_WAREHOUSE_ADDRESS     = 7;

    const ID                            = 'id';
    const GENDER                        = 'gender';
    const NAME1                         = 'name1';
    const NAME2                         = 'name2';
    const NAME3                         = 'name3';
    const NAME4                         = 'name4';
    const ADDRESS1                      = 'address1';
    const ADDRESS2                      = 'address2';
    const ADDRESS3                      = 'address3';
    const ADDRESS4                      = 'address4';
    const POSTAL_CODE                   = 'postalCode';
    const TOWN                          = 'town';
    const COUNTRY_ID                    = 'countryId';
    const STATE_ID                      = 'stateId';
    const CHECKED_AT                    = 'checkedAt';
    const CREATED_AT                    = 'createdAt';
    const UPDATED_AT                    = 'updatedAt';
    const OPTIONS                       = 'options';

    /**
     * Example
     *
     * "options": [
        {
            "id": 1,
            "addressId": 1,
            "typeId": 1,
            "value": "DE250560740",
            "position": 1,
            "createdAt": "2012-03-10T23:10:28+00:00",
            "updatedAt": "2013-06-03T23:06:51+01:00"
        },
        {
            "id": 2,
            "addressId": 1,
            "typeId": 4,
            "value": "+49 561 - 50 656 100",
            "position": 4,
            "createdAt": "2012-03-10T23:10:28+00:00",
            "updatedAt": "2013-06-03T23:06:51+01:00"
        },
        {
            "id": 4,
            "addressId": 1,
            "typeId": 5,
            "value": "info@plentymarkets.eu",
            "position": 5,
            "createdAt": "2012-03-10T23:10:28+00:00",
            "updatedAt": "2013-06-03T23:06:51+01:00"
        },
        {
            "id": 6,
            "addressId": 1,
            "typeId": 8,
            "value": "0",
            "position": 8,
            "createdAt": "2012-03-10T23:10:28+00:00",
            "updatedAt": "2013-06-03T23:06:51+01:00"
        }
    ]
     */

    const OPTION_TYPE_VAT_NUMBER            = 1;
    const OPTION_TYPE_EXTERNAL_ADDRESS_ID   = 2;
    const OPTION_TYPE_ENTRY_CERTIFICATE     = 3;
    const OPTION_TYPE_TELEPHONE             = 4;
    const OPTION_TYPE_EMAIL                 = 5;
    const OPTION_TYPE_POST_NUMBER           = 6;
    const OPTION_TYPE_PERSONAL_ID           = 7;
    const OPTION_TYPE_AGE_RATING_BBFC       = 8;
    const OPTION_TYPE_BIRTHDAY              = 9;
    const OPTION_TYPE_SESSION_ID            = 10;
    const OPTION_TYPE_TITLE                 = 11;

}
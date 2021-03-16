<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest\AbstractData;

/**
 * Interface ContactDataInterface
 * @package Plenty\Customer\Rest\AbstractData
 */
interface ContactDataInterface
{
    const TYPE_CUSTOMER                     = 1;
    const TYPE_SALES_LEAD                   = 2;
    const TYPE_SALES_REPRESENTATIVE         = 3;
    const TYPE_SUPPLIER                     = 4;
    const TYPE_MANUFACTURER                 = 5;
    const TYPE_PARTNER                      = 6;

    const ID                                = 'id';
    const NUMBER                            = 'number';
    const EXTERNAL_ID                       = 'externalId';
    const TYPE_ID                           = 'typeId';
    const FIRST_NAME                        = 'firstName';
    const LAST_NAME                         = 'lastName';
    const GENDER                            = 'gender';
    const TITLE                             = 'title';
    const FORM_OF_ADDRESS                   = 'formOfAddress';
    const NEWSLETTER_ALLOWANCE_AT           = 'newsletterAllowanceAt';
    const CLASS_ID                          = 'classId';
    const BLOCKED                           = 'blocked';
    const RATING                            = 'rating';
    const BOOK_ACCOUNT                      = 'bookAccount';
    const LANG                              = 'lang';
    const REFERRER_ID                       = 'referrerId';
    const USER_ID                           = 'userId';
    const BIRTHDAY_AT                       = 'birthdayAt';
    const LAST_LOGIN_AT                     = 'lastLoginAt';
    const LAST_ORDER_AT                     = 'lastOrderAt';
    const CREATED_AT                        = 'createdAt';
    const UPDATED_AT                        = 'updatedAt';
    const VALUTA                            = 'valuta';
    const DISCOUNT_DAYS                     = 'discountDays';
    const DISCONT_PERCENT                   = 'discountPercent';
    const TIME_FOR_PAYMENT_ALLOWED_DAYS     = 'timeForPaymentAllowedDays';
    const SALES_REPRESENTATIVE_CONTACT_ID   = 'salesRepresentativeContactId';
    const PLENTY_ID                         = 'plentyId';
    const EMAIL                             = 'email';
    const EBAY_NAME                         = 'ebayName';
    const PRIVATE_PHONE                     = 'privatePhone';
    const PRIVATE_FAX                       = 'privateFax';
    const PRIVATE_MOBILE                    = 'privateMobile';
    const PAYPAL_EMAIL                      = 'paypalEmail';
    const PAYPAL_PAYER_ID                   = 'paypalPayerId';
    const KLARNA_PERSONAL_ID                = 'klarnaPersonalId';
    const DHL_POST_IDENT                    = 'dhlPostIdent';
    const SINGLE_ACCESS                     = 'singleAccess';
    const CONTACT_PERSON                    = 'contactPerson';
    const MARKETPLACE_PARTNER               = 'marketplacePartner';
    const FULL_NAME                         = 'fullName';
    const OPTIONS                           = 'options';
    /**
     * "options": [
        {
            "id": 275,
            "contactId": 228,
            "typeId": 1,
            "subTypeId": 4,
            "value": "2308328080238023",
            "priority": 0,
            "createdAt": "2018-06-21T09:31:29+01:00",
            "updatedAt": "2018-06-21T09:31:29+01:00"
            },
            {
            "id": 276,
            "contactId": 228,
            "typeId": 2,
            "subTypeId": 4,
            "value": "citygab2@softcommerce.co.uk",
            "priority": 0,
            "createdAt": "2018-06-21T09:31:29+01:00",
            "updatedAt": "2018-06-21T09:31:29+01:00"
        }
     */
    const OPTIONS_TYPE_TELEPHONE                = 1;
    const OPTIONS_TYPE_EMAIL                    = 2;
    const OPTIONS_TYPE_TELEFAX                  = 3;
    const OPTIONS_TYPE_WEB_PAGE                 = 4;
    const OPTIONS_TYPE_MARKETPLACE              = 5;
    const OPTIONS_TYPE_ID_NUMBER                = 6;
    const OPTIONS_TYPE_PAYMENT                  = 7;
    const OPTIONS_TYPE_USERNAME                 = 8;
    const OPTIONS_TYPE_GROUP                    = 9;
    const OPTIONS_TYPE_ACCESS                   = 10;
    const OPTIONS_TYPE_ADDITIONAL               = 11;

    const OPTIONS_SUBTYPE_WORK                  = 1;
    const OPTIONS_SUBTYPE_MOBILE_PRIVATE        = 2;
    const OPTIONS_SUBTYPE_MOBILE_WORK           = 3;
    const OPTIONS_SUBTYPE_PRIVATE               = 4;
    const OPTIONS_SUBTYPE_PAYPAL                = 5;
    const OPTIONS_SUBTYPE_EBAY                  = 6;
    const OPTIONS_SUBTYPE_AMAZON                = 7;
    const OPTIONS_SUBTYPE_KLARNA                = 8;
    const OPTIONS_SUBTYPE_DHL                   = 9;
    const OPTIONS_SUBTYPE_FORUM                 = 10;
    const OPTIONS_SUBTYPE_GUEST                 = 11;
    const OPTIONS_SUBTYPE_CONTACT_PERSON        = 12;
    const OPTIONS_SUBTYPE_MARKETPLACE_PARTNER   = 13;

}
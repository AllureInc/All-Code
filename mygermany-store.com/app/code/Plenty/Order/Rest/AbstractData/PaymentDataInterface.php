<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\AbstractData;

/**
 * Interface OrderDataInterface
 * @package Plenty\Order\Rest\AbstractData
 */
interface PaymentDataInterface
{

    const TYPE_CREDIT                                   = 'credit';
    const TYPE_DEBIT                                    = 'debit';

    // PAYMENT METHOD
    const PAYMENT_METHOD_INVOICE                        = 2;

    // PAYMENT STATUS
    const STATUS_AWAITING_APPROVAL                      = 1;
    const STATUS_APPROVED                               = 2;
    const STATUS_CAPTURED                               = 3;
    const STATUS_PARTIALLY_CAPTURED                     = 4;
    const STATUS_CANCELED                               = 5;
    const STATUS_REFUSED                                = 6;
    const STATUS_AWAITING_RENEWAL                       = 7;
    const STATUS_EXPIRED                                = 8;
    const STATUS_REFUNDED                               = 9;
    const STATUS_PARTIALLY_REFUNDED                     = 10;

    // TRANSACTION TYPE
    const TRANSACTION_TYPE_INTERIM_REPORT               = 1;
    const TRANSACTION_TYPE_BOOKED_PAYMENT               = 2;
    const TRANSACTION_TYPE_SPLIT_PAYMENT                = 3;

    // PAYMENT PROPERTY TYPE
    const PROPERTY_TYPE_TRANSACTION_ID                  = 1;
    const PROPERTY_TYPE_REFERENCE_ID                    = 2;
    const PROPERTY_TYPE_BOOKING_TEXT                    = 3;
    const PROPERTY_TYPE_TRANSACTION_PASSWORD            = 1;
    const PROPERTY_TYPE_TRANSACTION_CODE                = 5;
    const PROPERTY_TYPE_AUTHORISATION_ID                = 6;
    const PROPERTY_TYPE_CAPTURE_ID                      = 7;
    const PROPERTY_TYPE_REFUND_ID                       = 8;
    const PROPERTY_TYPE_CREDIT_NOTE_ID                  = 9;
    const PROPERTY_TYPE_ORDER_REFERENCE                 = 10;
    const PROPERTY_TYPE_SENDER_NAME                     = 11;
    const PROPERTY_TYPE_SENDER_EMAIL                    = 12;
    const PROPERTY_TYPE_SENDERS_SORT_CODE               = 13;
    const PROPERTY_TYPE_SENDERS_BANK_NAME               = 14;
    const PROPERTY_TYPE_SENDERS_BANK_ACCOUNT_NO         = 16;
    const PROPERTY_TYPE_SENDERS_BANK_ACCOUNT_COUNTRY    = 17;
    const PROPERTY_TYPE_SENDERS_IBAN                    = 18;
    const PROPERTY_TYPE_SENDERS_BIC                     = 19;
    const PROPERTY_TYPE_RECIPIENT_NAME                  = 20;
    const PROPERTY_TYPE_RECIPIENT_BANK_ACCOUNT          = 21;
    const PROPERTY_TYPE_PAYMENT_REFERENCE_TEXT          = 22;
    const PROPERTY_TYPE_PAYMENT_ORIGIN                  = 23;
    const PROPERTY_TYPE_SHIPPING_ADDRESS                = 24;
    const PROPERTY_TYPE_INVOICE_ADDRESS                 = 25;
    const PROPERTY_TYPE_ITEM_BUYER                      = 26;
    const PROPERTY_TYPE_ITEM_NUMBER                     = 27;
    const PROPERTY_TYPE_ITEM_TRANSACTION_ID             = 28;
    const PROPERTY_TYPE_EXTERNAL_TRANSACTION_TYPE       = 29;
    const PROPERTY_TYPE_EXTERNAL_TRANSACTION_STATUS     = 30;

    const ID                                            = 'id';
    const AMOUNT                                        = 'amount';
    const EXCHANGE_RATIO                                = 'exchangeRatio';
    const MOP_ID                                        = 'mopId';
    const PARENT_ID                                     = 'parentId';
    const DELETED                                       = 'deleted';
    const UNACCOUNTABLE                                 = 'unaccountable';
    const CURRENCY                                      = 'currency';
    const TYPE                                          = 'type';
    const HASH                                          = 'hash';
    const RECEIVED_AT                                   = 'receivedAt';
    const IMPORTED_AT                                   = 'importedAt';
    const STATUS                                        = 'status';
    const TRANSACTION_TYPE                              = 'transactionType';
    const IS_SYSTEM_CURRENCY                            = 'isSystemCurrency';
    const PARENT                                        = 'parent';
    const METHOD                                        = 'method';
    const HISTORIES                                     = 'histories';
    const PROPERTIES                                    = 'properties';
    /**
     * EXAMPLE OF PROPERTIES
     *
     * "properties": [
        {
            "id": 1,
            "paymentId": 1,
            "typeId": 23,
            "value": "0",
            "createdAt": "2017-03-31T09:49:46+01:00",
            "updatedAt": "-0001-11-30T00:00:00+00:00"
        },
        {
            "id": 32,
            "paymentId": 1,
            "typeId": 34,
            "value": "0",
            "createdAt": "2017-03-31T09:49:46+01:00",
            "updatedAt": "-0001-11-30T00:00:00+00:00"
        }
    ],
     */
    const ORDER                                         = 'order';
    /**
     * EXAMPLE OF ORDER
     *
     * "order": {
            "id": 1,
            "paymentId": 1,
            "orderId": 1952,
            "assignedAt": "2017-03-24 18:41:16"
        }
     */
}
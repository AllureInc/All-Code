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
interface OrderDataInterface
{
    /**
     * TypeId of an order
     * @see https://developers.plentymarkets.com/rest-doc/order/details#create-an-order
     */
    const ORDER_TYPE_SALE                           = 1;
    const ORDER_TYPE_DELIVERY                       = 2;
    const ORDER_TYPE_RETURNS                        = 3;
    const ORDER_TYPE_CREDIT_NOTE                    = 4;
    const ORDER_TYPE_WARRANTY                       = 5;
    const ORDER_TYPE_REPAIR                         = 6;
    const ORDER_TYPE_OFFER                          = 7;
    const ORDER_TYPE_ADVANCE_ORDER                  = 8;
    const ORDER_TYPE_MULTI_ORDER                    = 9;
    const ORDER_TYPE_MULTI_CREDIT_NOTE              = 10;
    const ORDER_TYPE_MULTI_DELIVERY                 = 11;
    const ORDER_TYPE_REORDER                        = 12;
    const ORDER_TYPE_PARTIAL_DELIVERY               = 13;

    /**
     * Sale item type
     * 1 for sale item // 4 for promotional coupon // 6 for shipping
     */
    const ITEM_TYPE_VARIATION                       = 1;
    const ITEM_TYPE_BUNDLE                          = 2;
    const ITEM_TYPE_BUNDLE_COMPONENT                = 3;
    const ITEM_TYPE_PROMOTIONAL_COUPON              = 4;
    const ITEM_TYPE_GIFT_CARD                       = 5;
    const ITEM_TYPE_SHIPPING_COSTS                  = 6;
    const ITEM_TYPE_PAYMENT_SURCHARGE               = 7;
    const ITEM_TYPE_GIFT_WRAP                       = 8;
    const ITEM_TYPE_UNASSIGEND_VARIATION            = 9;
    const ITEM_TYPE_DEPOSIT                         = 10;
    const ITEM_TYPE_ORDER                           = 11;

    /**
     * TypeId of order item property
     * @see https://developers.plentymarkets.com/rest-doc/order_order_item_order_item_property/details#get-all-order-item-propertys-for-one-order-item-by-its-order-item-id
     */
    const ORDER_ITEM_PROPERTY_WAREHOUSE             = 1;
    const ORDER_ITEM_PROPERTY_SHIPPING_PROFILE      = 2;
    const ORDER_ITEM_PROPERTY_PAYMENT_METHOD        = 3;
    const ORDER_ITEM_PROPERTY_ITEM                  = 4;
    const ORDER_ITEM_PROPERTY_WEIGHT                = 11;
    const ORDER_ITEM_PROPERTY_COUPON_CODE           = 18;
    const ORDER_ITEM_PROPERTY_COUPON_TYPE           = 19;
    const ORDER_ITEM_PROPERTY_CATEGORY_ID           = 22;

    /**
     * Address Relations
     * @see https://developers.plentymarkets.com/rest-doc/account_address_configuration/details#list-address-relation-types
     */
    const ORDER_ADDRESS_BILLING                     = 1;
    const ORDER_ADDRESS_SHIPPING                    = 2;
    const ORDER_ADDRESS_SENDER                      = 3;
    const ORDER_ADDRESS_RETURN                      = 4;
    const ORDER_ADDRESS_CLIENT                      = 5;
    const ORDER_ADDRESS_CONTRACTOR                  = 6;
    const ORDER_ADDRESS_WAREHOUSE                   = 7;
    const ORDER_ADDRESS_POS                         = 8;

    /**
     * OrderRelationReferenceRepositoryContract
     * @see https://developers.plentymarkets.com/api-doc/Order
     */
    const ORDER_RELATION_SENDER                     = 'sender';
    const ORDER_RELATION_RECEIVER                   = 'receiver';

    const ORDER_RELATION_REFERENCE_CONTACT          = 'contact';
    const ORDER_RELATION_REFERENCE_WAREHOUSE        = 'warehouse';
    const ORDER_RELATION_REFERENCE__ACCOUNT         = 'account';


    /**
     * ORDER DATES TYPE ID
     * @see https://developers.plentymarkets.com/api-doc/Order#element_19
     */
    const ORDER_DATES_TYPE_DELETE_DATE = 1;
    const ORDER_DATES_TYPE_ENTRY_DATE = 2;
    const ORDER_DATES_TYPE_PAYMENT_DATE = 3;
    const ORDER_DATES_TYPE_DELIVERY_DATE = 4;

    /**
     * PROPERTIES OF THE ORDER
     * @see https://developers.plentymarkets.com/rest-doc/order_order_property/details
     */
    const ORDER_PROPERTIES_WAREHOUSE                    = 1;
    const ORDER_PROPERTIES_SHIPPING_PROFILE             = 2;
    const ORDER_PROPERTIES_PAYMENT_METHOD               = 3;
    const ORDER_PROPERTIES_PAYMENT_STATUS               = 4;
    const ORDER_PROPERTIES_EXTERNAL_SHIPPING_PROFILE    = 5;
    const ORDER_PROPERTIES_DOCUMENT_LANGUAGE            = 6;
    const ORDER_PROPERTIES_EXTERNAL_ORDER_ID            = 7;
    const ORDER_PROPERTIES_CUSTOMER_SIGN                = 8;
    const ORDER_PROPERTIES_DUNNING_LEVEL                = 9;
    const ORDER_PROPERTIES_SELLER_ACCOUNT               = 10;
    const ORDER_PROPERTIES_WEIGHT                       = 11;
    const ORDER_PROPERTIES_WIDTH                        = 12;
    const ORDER_PROPERTIES_LENGTH                       = 13;
    const ORDER_PROPERTIES_HEIGHT                       = 14;
    const ORDER_PROPERTIES_FLAG                         = 15;
    const ORDER_PROPERTIES_EXTERNAL_TOKEN_ID            = 16;
    const ORDER_PROPERTIES_EXTERNAL_ITEM_ID             = 17;
    const ORDER_PROPERTIES_COUPON_CODE                  = 18;
    const ORDER_PROPERTIES_COUPON_TYPE                  = 19;
    const ORDER_PROPERTIES_SALES_TAX_ID_NUMBER          = 34;
    const ORDER_PROPERTIES_MAIN_DOCUMENT_NUMBER         = 33;
    const ORDER_PROPERTIES_PAYMENT_TRANSACTION_ID       = 45;

    const RELATION                                      = 'relation';
    const ADDRESS_ID                                    = 'addressId';

    /** Flag that states if the current currency is the same as the system currency or not. */
    /** The original net price without any surcharges or discounts. */
    const PRICE_ORIGINAL_NET                            = 'priceOriginalNet';

    /** The total gross price including surcharges and discounts [READONLY]. */
    const PRICE_GROSS                                   = 'priceGross';

    /** The purchase price of the variation. */
    const PURCHASE_PRICE                                = 'purchasePrice';

    const ID                                            = 'id';
    const REFERRER_ID                                   = 'referrerId';
    const STATUS_NAME                                   = 'statusName';
    const PLENTY_ID                                     = 'plentyId';
    const TYPE_ID                                       = 'typeId';
    const LOCK_STATUS                                   = 'lockStatus';
    const LOCATION_ID                                   = 'locationId';
    const CREATED_AT                                    = 'createdAt';
    const UPDATED_AT                                    = 'updatedAt';
    const STATUS_ID                                     = 'statusId';
    const OWNER_ID                                      = 'ownerId';
    const RELATIONS                                     = 'relations';
    /**
     * EXAMPLE OF RELATIONS
     *
     * "relations":
     *  [
            {
                "orderId": 3023,
                "referenceType": "warehouse",
                "referenceId": 1,
                "relation": "sender"
            },
            {
                "orderId": 3023,
                "referenceType": "contact",
                "referenceId": 225,
                "relation": "receiver"
            }
        ],
     */
    const PROPERTIES                                    = 'properties';
    /**
     * EXAMPLE OF PROPERTIES
     *
     * "properties":
     *  [
            {
                "orderId": 3023,
                "typeId": 3,
                "value": "8"
            },
            {
                "orderId": 3023,
                "typeId": 6,
                "value": "en"
            },
            {
                "orderId": 3023,
                "typeId": 7,
                "value": "100000116"
            },
        ],
     */
    const DATES                                             = 'dates';
    /**
     * EXAMPLE OF DATES
     *
     * "dates":
     *  [
            {
                "orderId": 3023,
                "typeId": 2,
                "date": "2018-08-28T10:35:56+01:00"
            },
            {
                "orderId": 3023,
                "typeId": 4,
                "date": "2019-01-10T11:35:51+00:00"
            }
        ],
     */
    const ORDER_ITEMS                                       = 'orderItems';
    /**
     * EXAMPLE OF ORDER ITEMS
     *
     *  "orderItems":
     * [
        {
            "id": 7839,
            "orderId": 3023,
            "typeId": 6,
            "referrerId": 9,
            "itemVariationId": 0,
            "quantity": 1,
            "orderItemName": "Shipping costs",
            "attributeValues": "",
            "shippingProfileId": 0,
            "countryVatId": 4,
            "vatField": 0,
            "vatRate": 20,
            "position": "0",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00",
            "warehouseId": null,
            "orderProperties": [],
            "properties": [],
            "dates": [],
            "amounts": [
                {
                "id": 9046,
                "orderItemId": 7839,
                "isSystemCurrency": true,
                "currency": "GBP",
                "exchangeRate": 1,
                "purchasePrice": 0,
                "priceOriginalGross": 12.5,
                "priceOriginalNet": 10.4167,
                "priceGross": 12.5,
                "priceNet": 10.4167,
                "surcharge": 0,
                "discount": 0,
                "isPercentage": true,
                "createdAt": "2018-08-28T10:36:10+01:00",
                "updatedAt": "2018-08-28T10:36:10+01:00"
                }
            ],
        "references": []
        },
        {
            "id": 7840,
            "orderId": 3023,
            "typeId": 1,
            "referrerId": 9,
            "itemVariationId": 28891,
            "quantity": 1,
            "orderItemName": "Sofa Simple 03",
            "attributeValues": null,
            "shippingProfileId": 7,
            "countryVatId": 4,
            "vatField": 0,
            "vatRate": 20,
            "position": "0",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00",
            "warehouseId": 1,
            "orderProperties": [],
        "properties": [
            {
            "id": 8100,
            "orderItemId": 7840,
            "typeId": 1,
            "value": "1",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00"
            },
            {
            "id": 8101,
            "orderItemId": 7840,
            "typeId": 2,
            "value": "7",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00"
            },
            {
            "id": 8103,
            "orderItemId": 7840,
            "typeId": 11,
            "value": "1",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00"
            },
            {
            "id": 8102,
            "orderItemId": 7840,
            "typeId": 21,
            "value": "1",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00"
            },
            {
            "id": 8104,
            "orderItemId": 7840,
            "typeId": 29,
            "value": "Sofa Simple 03",
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00"
            }
        ],
        "dates": [],
        "amounts": [
            {
            "id": 9047,
            "orderItemId": 7840,
            "isSystemCurrency": true,
            "currency": "GBP",
            "exchangeRate": 1,
            "purchasePrice": 0,
            "priceOriginalGross": 95,
            "priceOriginalNet": 79.1667,
            "priceGross": 95,
            "priceNet": 79.1667,
            "surcharge": 0,
            "discount": 0,
            "isPercentage": true,
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00"
            }
        ],
        "references": []
        }
        ],
     */
    const AMOUNTS                                                   = 'amounts';
    /**
     * EXAMPLE OF AMOUNTS
     *
     * "amounts": [
        {
            "id": 5968,
            "orderId": 3023,
            "isSystemCurrency": true,
            "isNet": false,
            "currency": "GBP",
            "exchangeRate": 1,
            "netTotal": 89.58,
            "grossTotal": 107.5,
            "vatTotal": 17.92,
            "invoiceTotal": 107.5,
            "paidAmount": 0,
            "giftCardAmount": 0,
            "createdAt": "2018-08-28T10:36:10+01:00",
            "updatedAt": "2018-08-28T10:36:10+01:00",
            "shippingCostsGross": 12.5,
            "shippingCostsNet": 10.42,
            "prepaidAmount": 0,
            "vats": [
                {
                    "id": 6667,
                    "orderAmountId": 5968,
                    "countryVatId": 4,
                    "vatField": 0,
                    "vatRate": 20,
                    "value": 17.92,
                    "createdAt": "2018-08-28T10:36:10+01:00",
                    "updatedAt": "2018-08-28T10:36:10+01:00",
                    "netTotal": 89.58,
                    "grossTotal": 107.5
                }
            ]
        }
    ],
     */
    const ORDER_REFERENCES                                              = 'orderReferences';
    const ADDRESS_RELATIONS                                             = 'addressRelations';
    /**
     * EXAMPLE OF ADDRESS RELATIONS
     *
     * "addressRelations": [
        {
            "id": 5496,
            "orderId": 3023,
            "typeId": 1,
            "addressId": 726
        },
        {
            "id": 5497,
            "orderId": 3023,
            "typeId": 2,
            "addressId": 726
        }
    ]
     */

    // ORDER RELATIONS
    const ORDER_WITH_ADDRESS                            = 'addresses';
    const ORDER_WITH_COMMENTS                           = 'comments';
    const ORDER_WITH_RELATIONS                          = 'relations';
    const ORDER_WITH_PAYMENTS                           = 'payments';
    const ORDER_WITH_CONTACT_SENDER                     = 'contactSender';
    const ORDER_WITH_CONTACT_RECEIVER                   = 'contactReceiver';
    const ORDER_WITH_WAREHOUSE_SENDER                   = 'warehouseSender';
    const ORDER_WITH_WAREHOUSE_RECEIVER                 = 'warehouseReceiver';
    const ORDER_WITH_ORDER_ITEMS_VARIATION              = 'orderItems.variation';
    const ORDER_WITH_ORDER_ITEMS_GIFT_CARD_CODES        = 'orderItems.giftCardCodes';
    const ORDER_WITH_ORDER_ITEMS_TRANSACTIONS           = 'orderItems.transactions';
    const ORDER_WITH_ORDER_ITEMS_SERIAL_NUMBERS         = 'orderItems.serialNumbers';
    const ORDER_WITH_ORDER_ITEMS_VARIATION_BARCODES     = 'orderItems.variationBarcodes';
    const ORDER_WITH_ORDER_ITEMS_COMMENTS               = 'orderItems.comments';
    const ORDER_WITH_ORIGIN_ORDER_REFERENCES            = 'originOrderReferences';
}
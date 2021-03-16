<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile\Config\Source\Api\Order;

use Magento\Framework\Data\OptionSourceInterface;

/**
 * Class Filters
 * @package Plenty\Order\Profile\Config\Source\Api\Order
 */
class Filters implements OptionSourceInterface
{
    /**
     * @see https://developers.plentymarkets.com/rest-doc/order/details#list-orders-by-filter-options
     */
    const ADDRESS                           = 'addresses';
    const RELATIONS                         = 'relations';
    const COMMENTS                          = 'comments';
    const LOCATIONS                         = 'location';
    const PAYMENTS                          = 'payments';
    const DOCUMENTS                         = 'documents';
    const CONTACT_SENDER                    = 'contactSender';
    const CONTACT_RECEIVER                  = 'contactReceiver';
    const WAREHOUSE_SENDER                  = 'warehouseSender';
    const WAREHOUSE_RECEIVER                = 'warehouseReceiver';
    const ORDER_ITEMS_VARIATION             = 'orderItems.variation';
    const ORDER_ITEMS_GIFTCARD_CODES        = 'orderItems.giftCardCodes';
    const ORDER_ITEMS_TRANSACTIONS          = 'orderItems.transactions';
    const ORDER_ITEMS_SERIAL_NUMBERS        = 'orderItems.serialNumbers';
    const ORDER_ITEMS_VARIATION_BARCODES    = 'orderItems.variationBarcodes';
    const ORDER_ITEMS_COMMENTS              = 'orderItems.comments';
    const ORIGINAL_ORDER_REFERENCES         = 'originOrderReferences';

    /**
     * @return array
     */
    public function toOptionArray()
    {
        return [
            ['value' => self::ADDRESS, 'label' => __('Address')],
            ['value' => self::RELATIONS, 'label' => __('Relations')],
            ['value' => self::COMMENTS, 'label' => __('Comments')],
            ['value' => self::LOCATIONS, 'label' => __('Locations')],
            ['value' => self::PAYMENTS, 'label' => __('Payments')],
            ['value' => self::DOCUMENTS, 'label' => __('Documents')],
            ['value' => self::CONTACT_SENDER, 'label' => __('Contact sender')],
            ['value' => self::CONTACT_RECEIVER, 'label' => __('Contact receiver')],
            ['value' => self::WAREHOUSE_SENDER, 'label' => __('Warehouse sender')],
            ['value' => self::WAREHOUSE_RECEIVER, 'label' => __('Warehouse receiver')],
            ['value' => self::ORDER_ITEMS_VARIATION, 'label' => __('Order items variation')],
            ['value' => self::ORDER_ITEMS_GIFTCARD_CODES, 'label' => __('Order items giftcard codes')],
            ['value' => self::ORDER_ITEMS_TRANSACTIONS, 'label' => __('Order items transactions')],
            ['value' => self::ORDER_ITEMS_SERIAL_NUMBERS, 'label' => __('Order items serial numbers')],
            ['value' => self::ORDER_ITEMS_VARIATION_BARCODES, 'label' => __('Order items variation barcodes')],
            ['value' => self::ORDER_ITEMS_COMMENTS, 'label' => __('Order items comments')],
            ['value' => self::ORIGINAL_ORDER_REFERENCES, 'label' => __('Original order references')]


        ];
    }
}
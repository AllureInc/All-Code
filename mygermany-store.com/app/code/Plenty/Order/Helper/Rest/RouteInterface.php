<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Helper\Rest;

/**
 * Interface RouteInterface
 * @package Plenty\Order\Helper\Rest
 */
interface RouteInterface
{
    const ORDER_URL                         = '/rest/orders';
    const ORDER_STATUS_URL                  = '/rest/orders/statuses';
    const ORDER_SHIPPING_PROFILE_URL        = '/rest/orders/shipping/presets';
    const ORDER_ADDRESS_URL                 = '/rest/orders/addresses';
    const ORDER_SHIPPING_INFO_URL           = '/rest/orders/shipping/shipping_information';
    const ORDER_PROPERTY_TYPES              = '/rest/orders/properties/types';
    const ORDER_STATUSES_URL                = '/rest/orders/statuses';

    const PAYMENT_URL                       = '/rest/payment';
    const PAYMENTS_URL                      = '/rest/payments';
    const PAYMENT_METHODS_URL               = '/rest/payments/methods';
    const ORDER_PAYMENT_URL                 = '/rest/payments/orders';
    const PAYMENT_PROPERTIES_URL            = '/rest/payments/properties';

    /**
     * REST Get orders
     * @see https://developers.plentymarkets.com/rest-doc/order
     *
     * @param null $orderId
     * @return string
     */
    public function getOrderUrl($orderId = null) : string;

    /**
     * @return string
     */
    public function getOrderStatusUrl() : string;

    /**
     * REST Create Order shipping package
     * @see https://developers.plentymarkets.com/rest-doc/order_shipping_package/details#create-an-order-shipping-package
     *
     * @param $orderId
     * @param null $shippingPackageId
     * @return string
     */
    public function getOrderShippingPackageUrl($orderId, $shippingPackageId = null) : string;

    /**
     * @param null $profileId
     * @param null $lang
     * @return string
     */
    public function getShippingProfilesUrl($profileId = null, $lang = null) : string;

    /**
     * Create an address for existing order
     * @see @see https://developers.plentymarkets.com/rest-doc/account_address/details#create-an-address
     *
     * @param null $orderId
     * @param null $relationTypeId
     * @return string
     */
    public function getOrderAddressUrl($orderId = null, $relationTypeId = null) : string;

    /**
     * @param null $orderId
     * @return string
     */
    public function geOrderShippingInfoUrl($orderId = null) : string;

    /**
     * @param null $typeId
     * @return mixed
     */
    public function getOrderPropertyTypesUrl($typeId = null) : string;

    /**
     * @param null $statusId
     * @return string
     */
    public function getOrderStatusesUrl($statusId = null) : string;

    /**
     * @param null $paymentId
     * @return string
     */
    public function getPaymentsUrl($paymentId = null) : string;

    /**
     * @param null $pluginKey
     * @return string
     */
    public function getPaymentMethodsUrl($pluginKey = null) : string;

    /**
     * @param $oderId
     * @return string
     */
    public function getOrderPaymentsUrl($oderId) : string;

    /**
     * @see https://developers.plentymarkets.com/rest-doc/payment_payment/details#create-payment-order-relation
     *
     * @param $paymentId
     * @param null $orderId
     * @return string
     */
    public function getPaymentOrderRelationUrl($paymentId, $orderId = null) : string;

    /**
     * @see https://developers.plentymarkets.com/rest-doc/payment_payment/details#create-payment-contact-relation
     *
     * @param $paymentId
     * @param null $contactId
     * @return string
     */
    public function getPaymentContactRelationUrl($paymentId, $contactId = null) : string;

    /**
     * @see https://developers.plentymarkets.com/rest-doc/payment_property/details#get-a-property
     *
     * @param null $propertyId
     * @return string
     */
    public function getPaymentPropertyUrl($propertyId = null) : string;

    /**
     * @param $paymentId
     * @return string
     */
    public function getPropertiesOfPaymentUrl($paymentId) : string ;
}

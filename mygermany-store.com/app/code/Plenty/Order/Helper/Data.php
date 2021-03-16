<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Helper;

use Magento\Framework\Exception\NoSuchEntityException;
use Plenty\Order\Helper\Rest\RouteInterface;
use Plenty\Core\Helper\Data as CoreHelper;

/**
 * Class Data
 * @package Plenty\Order\Helper
 */
class Data extends CoreHelper implements RouteInterface
{
    // GENERAL
    const XML_PATH_UPDATE_STOCK_ON_ADDTOCART                = 'plenty_stock/general/update_on_addtocart';
    const XML_PATH_UPDATE_STOCK_ON_SAVE                     = 'plenty_stock/general/update_on_save';

    // DEVELOPER
    const XML_PATH_CONFIG_DEV_LOG_DIRECTORY                  = 'log/plenty/';
    const XML_PATH_ENABLE_DEBUGGING                          = 'plenty_item/dev/debug_enabled';
    const XML_PATH_CONFIG_DEV_DEBUG_DIRECTORY_NAME           = 'plenty_item/dev/debug_directory';
    const XML_PATH_CONFIG_DEV_DEBUG_LEVEL                    = 'plenty_item/dev/debug_level';

    /**
     * @param null $orderId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderUrl($orderId = null) : string
    {
        if (null === $orderId) {
            return $this->getAppUrl(self::ORDER_URL);
        }

        return $this->getAppUrl(self::ORDER_URL.'/'.$orderId);
    }

    /**
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderStatusUrl() : string
    {
        return $this->getAppUrl(self::ORDER_STATUS_URL);
    }

    /**
     * @param $orderId
     * @param null $shippingPackageId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderShippingPackageUrl($orderId, $shippingPackageId = null) : string
    {
        if (null === $shippingPackageId) {
            return $this->getAppUrl(self::ORDER_URL.'/'.$orderId.'/shipping/packages');
        }

        return $this->getAppUrl(self::ORDER_URL.'/'.$orderId.'/shipping/packages/'.$shippingPackageId);
    }

    /**
     * @param null $profileId
     * @param null $lang
     * @return string
     * @throws NoSuchEntityException
     */
    public function getShippingProfilesUrl($profileId = null, $lang = null) : string
    {
        if (null === $profileId && null === $lang) {
            return $this->getAppUrl(self::ORDER_SHIPPING_PROFILE_URL);
        }

        if (null !== $lang) {
            return $this->getAppUrl(self::ORDER_SHIPPING_PROFILE_URL.'/preview/'.$lang);
        }

        return $this->getAppUrl(self::ORDER_SHIPPING_PROFILE_URL.'/'.$profileId);
    }

    /**
     * @param null $orderId
     * @param null $relationTypeId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderAddressUrl($orderId = null, $relationTypeId = null) : string
    {
        if (null === $orderId) {
            return $this->getAppUrl(self::ORDER_ADDRESS_URL);
        }

        if (null === $relationTypeId) {
            return $this->getAppUrl(self::ORDER_URL.'/'.$orderId.'/addresses');
        }

        return $this->getAppUrl(self::ORDER_URL.'/'.$orderId.'/addresses/'.$relationTypeId);
    }

    /**
     * @param null $orderId
     * @return string
     * @throws NoSuchEntityException
     */
    public function geOrderShippingInfoUrl($orderId = null) : string
    {
        if (null === $orderId) {
            return $this->getAppUrl(self::ORDER_SHIPPING_INFO_URL);
        }
        return $this->getAppUrl(self::ORDER_URL.'/'.$orderId.'/shipping/shipping_information');
    }

    /**
     * @param null $typeId
     * @return mixed|string
     * @throws NoSuchEntityException
     */
    public function getOrderPropertyTypesUrl($typeId = null) : string
    {
        if (null === $typeId) {
            return $this->getAppUrl(self::ORDER_PROPERTY_TYPES);
        }

        return $this->getAppUrl(self::ORDER_PROPERTY_TYPES.'/'.$typeId);
    }

    /**
     * @param null $statusId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderStatusesUrl($statusId = null) : string
    {
        if (null === $statusId) {
            return $this->getAppUrl(self::ORDER_STATUS_URL);
        }

        return $this->getAppUrl(self::ORDER_STATUS_URL.'/'.$statusId);
    }

    /**
     * @param null $paymentId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentsUrl($paymentId = null) : string
    {
        if (null === $paymentId) {
            return $this->getAppUrl(self::PAYMENTS_URL);
        }
        return $this->getAppUrl(self::PAYMENTS_URL.'/'.$paymentId);
    }

    /**
     * @param null $pluginKey
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentMethodsUrl($pluginKey = null) : string
    {
        if (null === $pluginKey) {
            return $this->getAppUrl(self::PAYMENT_METHODS_URL);
        }

        return $this->getAppUrl(self::PAYMENT_METHODS_URL.'/plugins/'.$pluginKey);
    }

    /**
     * @param $oderId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getOrderPaymentsUrl($oderId) : string
    {
        return $this->getAppUrl(self::ORDER_PAYMENT_URL.'/'.$oderId);
    }


    /**
     * @param $paymentId
     * @param null $orderId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentOrderRelationUrl($paymentId, $orderId = null) : string
    {
        if (null === $orderId) {
            return $this->getAppUrl(self::PAYMENT_URL.'/'.$paymentId.'/order');
        }

        return $this->getAppUrl(self::PAYMENT_URL.'/'.$paymentId.'/order/'.$orderId);
    }

    /**
     * @param $paymentId
     * @param null $contactId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentContactRelationUrl($paymentId, $contactId = null) : string
    {
        if (null === $contactId) {
            return $this->getAppUrl(self::PAYMENT_URL.'/'.$paymentId.'/contact');
        }

        return $this->getAppUrl(self::PAYMENT_URL.'/'.$paymentId.'/contact/'.$contactId);
    }

    /**
     * @param $propertyId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPaymentPropertyUrl($propertyId = null) : string
    {
        if (null === $propertyId) {
            return $this->getAppUrl(self::PAYMENT_PROPERTIES_URL);
        }

        return $this->getAppUrl(self::PAYMENT_PROPERTIES_URL. '/'.$propertyId);
    }

    /**
     * @param $paymentId
     * @return string
     * @throws NoSuchEntityException
     */
    public function getPropertiesOfPaymentUrl($paymentId) : string
    {
        return $this->getAppUrl(self::PAYMENTS_URL. '/'.$paymentId.'/properties');
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
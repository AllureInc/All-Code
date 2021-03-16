<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Order\Rest\Request\Order\ItemDataBuilder;
use Plenty\Core\Model\Profile\Config\Source\Countries;

/**
 * Class OrderDataBuilder
 * @package Plenty\Order\Rest\Request
 */
class OrderDataBuilder implements OrderDataInterface
{
    /**
     * @var Helper
     */
    private $_helper;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var ItemDataBuilder
     */
    private $_itemDataBuilder;

    /**
     * @var Countries
     */
    private $_countriesFactory;

    /**
     * @var array
     */
    private $_request;

    /**
     * OrderDataBuilder constructor.
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param ItemDataBuilder $itemDataBuilder
     * @param Countries $countriesFactory
     */
    public function __construct(
        Helper $helper,
        DateTime $dateTime,
        ItemDataBuilder $itemDataBuilder,
        Countries $countriesFactory
    ) {
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_itemDataBuilder = $itemDataBuilder;
        $this->_countriesFactory = $countriesFactory;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param array $request
     * @return $this|mixed
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @param $statusId
     * @param null $referrerId
     * @param null $warehouseId
     * @param null $shippingProfileId
     * @return array|mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function buildRequest(
        OrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder,
        $statusId,
        $referrerId = null,
        $warehouseId = null,
        $shippingProfileId = null
    ) {
        $this->_request = [];

        $orderItems = $this->_generateOrderItems($salesOrder, $referrerId, $warehouseId, $shippingProfileId);
        $this->_addShippingCosts($salesOrder, $orderItems, $referrerId);

        $this->_request = [
            'typeId' => self::ORDER_TYPE_SALE,
            'ownerId' => $this->_helper->getOwnerId(),
            'plentyId' =>$this->_helper->getPlentyId(),
            'statusId' => $statusId,
            'orderItems' => $orderItems,
            'properties' => $this->_generateOrderProperties($salesOrder, $plentyOrder, $shippingProfileId),
            'addressRelations' => $this->_generateAddressRelations($plentyOrder),
            'relations' => $this->_generateOrderRelations($plentyOrder),
            // 'dates' => $this->_generateOrderDates($salesOrder)
        ];

        return $this->_request;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param null $referrerId
     * @param null $warehouseId
     * @param null $shippingProfileId
     * @return array
     */
    private function _generateOrderItems(
        OrderInterface $salesOrder,
        $referrerId = null,
        $warehouseId = null,
        $shippingProfileId = null
    ) {
        try {
            $this->_itemDataBuilder->buildBatchRequest($salesOrder, $referrerId, $warehouseId, $shippingProfileId);
        } catch (\Exception $e) {
            return [];
        }

        return $this->_itemDataBuilder->getRequest();
    }

    /**
     * @param OrderInterface $salesOrder
     * @param array $orderItems
     * @param $referrerId
     * @return array
     */
    private function _addShippingCosts(
        OrderInterface $salesOrder,
        array &$orderItems,
        $referrerId
    ) {
        $taxRate = null;

        $shipping_amounts[] = [
            'isSystemCurrency'      => true,
            'currency'              => $salesOrder->getBaseCurrencyCode(),
            'exchangeRate'          => 1,
            'priceOriginalGross'    => $salesOrder->getBaseShippingInclTax(),
            'surcharge'             => 0,
            'discount'              => 0,
            'isPercentage'          => false
        ];

        array_push($orderItems,  [
            'typeId'            => self::ITEM_TYPE_SHIPPING_COSTS,
            'referrerId'        => $referrerId,
            'quantity'          => 1,
            'countryVatId'      => $this->_getCountryId($salesOrder),
            'vatField'          => 0,
            'vatRate'           => number_format($taxRate),
            'orderItemName'     => $salesOrder->getShippingDescription(),
            'amounts'           =>  $shipping_amounts,
            // 'properties' => $orderItems_properties,
            // 'orderProperties' => $orderItems_orderProperties
        ]);

        return $orderItems;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @param null $shippingProfileId
     * @return array
     */
    private function _generateOrderProperties(
        OrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder,
        $shippingProfileId = null
    ) {
        $properties = [];

        if ($paymentMethodId = $plentyOrder->getPlentyPaymentMethodId()) {
            array_push($properties, [
                'typeId' => self::ORDER_PROPERTIES_PAYMENT_METHOD,
                'value' => (string) $paymentMethodId
            ]);
        }

        if ($paymentStatusId = $plentyOrder->getPlentyPaymentStatusId()) {
            array_push($properties, [
                'typeId' => self::ORDER_PROPERTIES_PAYMENT_STATUS,
                'value' => (string) $paymentStatusId
            ]);
        }

        array_push($properties, [
            'typeId' => self::ORDER_PROPERTIES_EXTERNAL_ORDER_ID,
            'value' => (string) $salesOrder->getIncrementId()
        ]);

        if ($shippingProfileId) {
            array_push($properties, [
                'typeId' => self::ORDER_ITEM_PROPERTY_SHIPPING_PROFILE,
                'value' => (string) $shippingProfileId
            ]);
        }

        return $properties;
    }

    /**
     * @param PlentyOrderInterface $plentyOrder
     * @return array
     */
    private function _generateAddressRelations(PlentyOrderInterface $plentyOrder)
    {
        $addressRelations = [];
        if ($billingAddressId = $plentyOrder->getPlentyBillingAddressId()) {
            array_push($addressRelations, [
                'typeId' => self::ORDER_ADDRESS_BILLING,
                'addressId' => $billingAddressId
            ]);
        }

        if (!$shippingAddressId = $plentyOrder->getPlentyShippingAddressId()) {
            $shippingAddressId = $plentyOrder->getPlentyBillingAddressId();
        }

        if ($shippingAddressId) {
            array_push($addressRelations, [
                'typeId' => self::ORDER_ADDRESS_SHIPPING,
                'addressId' => $shippingAddressId
            ]);
        }

        return $addressRelations;
    }

    /**
     * @param PlentyOrderInterface $plentyOrder
     * @return array
     */
    private function _generateOrderRelations(PlentyOrderInterface $plentyOrder)
    {
        $relations = [];
        if ($contactId = $plentyOrder->getPlentyContactId()) {
            array_push($relations, [
                'referenceType' => self::ORDER_RELATION_REFERENCE_CONTACT,
                'referenceId' => $contactId,
                'relation' => self::ORDER_RELATION_RECEIVER
            ]);
        }

        return $relations;
    }

    /**
     * @param OrderInterface $salesOrder
     * @return array
     */
    private function _generateOrderDates(OrderInterface $salesOrder) : array
    {
        $orderDates[] = [
            'typeId' => self::ORDER_DATES_TYPE_ENTRY_DATE,
            'date' => date(\DateTime::W3C, strtotime($salesOrder->getCreatedAt()))
        ];

        return $orderDates;
    }

    /**
     * @param OrderInterface $salesOrder
     * @return int|null
     */
    private function _getCountryId(OrderInterface $salesOrder) : ?int
    {
        return $this->_countriesFactory
            ->getCountryIdByCode($salesOrder->getBillingAddress()->getCountryId());
    }
}
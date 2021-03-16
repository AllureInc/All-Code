<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderItemInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Catalog\Model\Product\Type as CatalogProductType;
use Magento\ConfigurableProduct\Model\Product\Type\Configurable;

use Plenty\Order\Helper\Data as Helper;
use Plenty\Core\Model\Profile\Config\Source\Countries;
use Plenty\Item\Rest\Variation as VariationClient;
use Plenty\Item\Api\Data\Import\Item\VariationInterface;

/**
 * Class PaymentDataBuilder
 * @package Plenty\Order\Rest\Request
 */
class ItemDataBuilder implements ItemDataInterface
{
    /**
     * @var Helper
     */
    private $_helper;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var array
     */
    private $_orderCountryId;

    /**
     * @var Countries
     */
    private $_countriesFactory;

    /**
     * @var VariationClient
     */
    private $_variationClient;

    /**
     * ItemDataBuilder constructor.
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param Countries $countriesFactory
     * @param VariationClient $variationClient
     */
    public function __construct(
        Helper $helper,
        DateTime $dateTime,
        Countries $countriesFactory,
        VariationClient $variationClient
    ) {
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_countriesFactory = $countriesFactory;
        $this->_variationClient = $variationClient;
    }

    /**
     * @return array
     */
    public function getRequest()
    {
        return $this->_request;
    }

    /**
     * @param $request
     * @return $this
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param OrderItemInterface $salesItem
     * @return $this
     */
    public function buildRequest(
        OrderInterface $salesOrder,
        OrderItemInterface $salesItem
    ) {
        $this->_request = [];
        $this->_request[] = $this->_buildRequest($salesOrder, $salesItem);

        return $this;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param null $referrerId
     * @param null $warehouseId
     * @param null $shippingProfileId
     * @return $this
     * @throws \Exception
     */
    public function buildBatchRequest(
        OrderInterface $salesOrder,
        $referrerId = null,
        $warehouseId = null,
        $shippingProfileId = null
    ) {
        $this->_request = [];

        if (!$salesItems = $salesOrder->getAllVisibleItems()) {
            throw new \Exception(__('Order has no items. [Order: %1]', $salesOrder->getIncrementId()));
        }

        /** @var OrderItemInterface $salesItem */
        foreach($salesItems as $salesItem) {
            if ($salesItem->getProductType() === Configurable::TYPE_CODE) {
                /** @var OrderItemInterface $childItem */
                foreach ($salesItem->getChildrenItems() as $childItem) {
                    $this->_request[] = $this->_buildRequest($salesOrder, $childItem);
                }
                continue;
            }

            $this->_request[] = $this->_buildRequest($salesOrder, $salesItem, $referrerId, $warehouseId, $shippingProfileId);
        }

        return $this;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param OrderItemInterface $salesItem
     * @param null $referrerId
     * @param null $warehouseId
     * @param null $shippingProfileId
     * @return array
     */
    protected function _buildRequest(
        OrderInterface $salesOrder,
        OrderItemInterface $salesItem,
        $referrerId = null,
        $warehouseId = null,
        $shippingProfileId = null
    ) {
        $orderItems_properties = [];
        // Add warehouse
        if ($warehouseId) {
            array_push($orderItems_properties, [
                'typeId' => self::ORDER_ITEM_PROPERTY_WAREHOUSE,
                'value' => (string) $warehouseId
            ]);
        }

        $orderItems_orderProperties[] = [
            'propertyId' => self::ORDER_ITEM_PROPERTY_ITEM,
            'value' => 'image.jpg',
            'fileUrl' => ''
        ];

        $promoItemFlag = $salesOrder->getAppliedRuleIds();
        $isPercentage = false;

        $itemVariationId = $this->_getPlentyVariationId($salesItem);

        $qty = $salesItem->getQtyOrdered();
        $price = $salesItem->getBaseOriginalPrice();
        $taxRate = $salesItem->getTaxPercent();
        $discount = $salesItem->getDiscountAmount() / $salesItem->getQtyOrdered();
        $itemName = $salesItem->getName();
        $typeId = self::ITEM_TYPE_VARIATION;

        if ($salesItem->getProductType() === CatalogProductType::TYPE_BUNDLE) {
            $typeId = self::ITEM_TYPE_BUNDLE;
        }

        $parentItem = $salesItem->getParentItem();
        if ($parentItem && $parentItem->getProductType() === Configurable::TYPE_CODE) {
            $qty = $parentItem->getQtyOrdered();
            $price = $parentItem->getBaseOriginalPrice();
            $taxRate = $parentItem->getTaxPercent();
            $discount = $parentItem->getDiscountAmount() / $parentItem->getQtyOrdered();
            $itemName = $parentItem->getName();
        }

        // Adds OrderItems Amounts
        $orderItems_amounts[] = [
            'isSystemCurrency' => true,
            'currency' => $salesOrder->getBaseCurrencyCode(),
            'exchangeRate' => 1,
            'priceOriginalGross' => $price
                ? $price
                : 0.00,
            'priceNet' => $price
                ? $price
                : 0,
            'surcharge' => 0,
            'discount' => $discount,
            'isPercentage' => $isPercentage
        ];

        $params = [
            'typeId' => $typeId,
            'referrerId' => $referrerId,
            'itemVariationId' => $itemVariationId
                ? $itemVariationId
                : -2,
            'quantity' => $qty,
            'countryVatId' => $this->_getCountryId($salesOrder),
            'vatField' => 0,
            'vatRate' => number_format($taxRate),
            'orderItemName' => $itemName,
            'shippingProfileId' => $shippingProfileId,
            'amounts' =>  $orderItems_amounts,
            'properties' => $orderItems_properties,
            'orderProperties' => $orderItems_orderProperties
        ];

        return $params;
    }

    /**
     * @param OrderInterface $salesOrder
     * @return int|null
     */
    private function _getCountryId(OrderInterface $salesOrder)
    {
        if (!isset($this->_orderCountryId[$salesOrder->getEntityId()])) {
            $this->_orderCountryId[$salesOrder->getEntityId()] = $this->_countriesFactory
                ->getCountryIdByCode($salesOrder->getBillingAddress()->getCountryId());
        }
        return $this->_orderCountryId[$salesOrder->getEntityId()];
    }

    /**
     * @param OrderItemInterface $salesItem
     * @return int|null
     */
    private function _getPlentyVariationId(OrderItemInterface $salesItem)
    {
        if ($itemVariationId = $salesItem->getData('plenty_variation_id')) {
            return $itemVariationId;
        }

        if ($itemVariationId = $salesItem->getProduct()->getData('plenty_variation_id')) {
            return $itemVariationId;
        }

        try {
            $variationResponse = $this->_variationClient->getVariationBySku($salesItem->getSku());
        } catch (\Exception $e) {
            return false;
        }

        return $variationResponse->getData(VariationInterface::VARIATION_ID);
    }
}
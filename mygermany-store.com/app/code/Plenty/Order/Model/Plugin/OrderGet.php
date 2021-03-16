<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderExtensionFactory;
use Magento\Sales\Api\Data\OrderItemExtensionFactory;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Plenty\Order\Api\OrderExportRepositoryInterface;
use Plenty\Order\Api\Data\Export\OrderInterface as OrderExportInterface;

/**
 * Class OrderGet
 * @package Plenty\Order\Model\Plugin
 */
class OrderGet
{
    /**
     * @var OrderExportRepositoryInterface
     */
    private $_orderExportRepository;

    /**
     * @var OrderExtensionFactory
     */
    private $_orderExtensionFactory;

    /**
     * @var OrderItemExtensionFactory
     */
    private $_orderItemExtensionFactory;

    /**
     * OrderGet constructor.
     * @param OrderExportRepositoryInterface $orderExportRepository
     * @param OrderExtensionFactory $orderExtensionFactory
     * @param OrderItemExtensionFactory $orderItemExtensionFactory
     */
    public function __construct(
        OrderExportRepositoryInterface $orderExportRepository,
        OrderExtensionFactory $orderExtensionFactory,
        OrderItemExtensionFactory $orderItemExtensionFactory
    ) {
        $this->_orderExportRepository = $orderExportRepository;
        $this->_orderExtensionFactory = $orderExtensionFactory;
        $this->_orderItemExtensionFactory = $orderItemExtensionFactory;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     * @return OrderInterface
     */
    public function afterGet(
        OrderRepositoryInterface $subject, OrderInterface $resultOrder
    ) {
        $resultOrder = $this->_attachPlentyOrder($resultOrder);
        return $resultOrder;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderSearchResultInterface $searchResult
     * @return OrderSearchResultInterface
     */
    public function afterGetList(
        OrderRepositoryInterface $subject, OrderSearchResultInterface $searchResult
    ) {
        $orders = $searchResult->getItems();

        /** @var OrderInterface $order */
        foreach ($orders as &$order) {
            $this->_attachPlentyOrder($order);
        }

        return $searchResult;
    }

    /**
     * @param OrderInterface $order
     * @return OrderInterface
     */
    private function _attachPlentyOrder(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if ($extensionAttributes && $extensionAttributes->getPlentyOrder()) {
            return $order;
        }

        try {
            /** @var OrderExportInterface $plentyOrder */
            $plentyOrder = $this->_orderExportRepository->get($order->getEntityId());
        } catch (NoSuchEntityException $e) {
            return $order;
        }

        /** @var \Magento\Sales\Api\Data\OrderExtension $orderExtension */
        $orderExtension = $extensionAttributes
            ? $extensionAttributes
            : $this->_orderExtensionFactory->create();
        $orderExtension->setPlentyOrder($plentyOrder);
        $order->setExtensionAttributes($orderExtension);

        return $order;
    }
}
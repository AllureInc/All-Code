<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Export;

use Plenty\Order\Api\Data\Export;
use Plenty\Order\Api\OrderExportRepositoryInterface;
use Plenty\Order\Model\ResourceModel\Export\Order as ResourceOrder;
use Plenty\Order\Model\Export\OrderFactory as OrderExportFactory;

use Magento\Sales\Model\OrderFactory;
use Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\CouldNotDeleteException;

/**
 * Class OrderRepository
 * @package Plenty\Order\Model\Export
 */
class OrderRepository implements OrderExportRepositoryInterface
{
    /**
     * @var ResourceOrder
     */
    private $_resource;

    /**
     * @var OrderFactory
     */
    private $_orderFactory;

    /**
     * @var OrderExportFactory
     */
    private $_orderExportFactory;

    /**
     * @var ResourceOrder\CollectionFactory
     */
    private $_orderExportCollectionFactory;

    /**
     * @var CollectionProcessorInterface
     */
    private $_collectionProcessor;

    /**
     * OrderRepository constructor.
     * @param ResourceOrder $resource
     * @param OrderFactory $orderFactory
     * @param \Plenty\Order\Model\Export\OrderFactory $orderExportFactory
     * @param ResourceOrder\CollectionFactory $orderExportCollectionFactory
     * @param CollectionProcessorInterface $collectionProcessor
     */
    public function __construct(
        ResourceOrder $resource,
        OrderFactory $orderFactory,
        OrderExportFactory $orderExportFactory,
        ResourceOrder\CollectionFactory $orderExportCollectionFactory,
        CollectionProcessorInterface $collectionProcessor
    ) {
        $this->_resource = $resource;
        $this->_orderFactory = $orderFactory;
        $this->_orderExportFactory = $orderExportFactory;
        $this->_orderExportCollectionFactory = $orderExportCollectionFactory;
        $this->_collectionProcessor = $collectionProcessor;
    }

    /**
     * @param int $orderId
     * @return Export\OrderInterface
     */
    public function get($orderId)
    {
        /** @var Export\OrderInterface $order */
        $order = $this->_orderExportFactory->create()
            ->load($orderId, Order::ORDER_ID);

        return $order;
    }

    /**
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     * @return \Magento\Framework\Api\SearchCriteriaInterface|mixed
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria)
    {
        /** @var ResourceOrder\Collection $collection */
        $collection = $this->_orderExportCollectionFactory->create();
        $this->_collectionProcessor->process($searchCriteria, $collection);
        return $searchCriteria;
    }

    /**
     * @param Export\OrderInterface $orderExport
     * @return Export\OrderInterface
     * @throws CouldNotSaveException
     */
    public function save(Export\OrderInterface $orderExport)
    {
        try {
            $this->_resource->save($orderExport);
        } catch (\Exception $e) {
            throw new CouldNotSaveException(
                __('Could not save order for export. [Reason: %1]', $e->getMessage()),
                $e
            );
        }

        return $orderExport;
    }

    /**
     * @param Export\OrderInterface $order
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function delete(Export\OrderInterface $order)
    {
        try {
            $this->_resource->delete($order);
        } catch (\Exception $exception) {
            throw new CouldNotDeleteException(__($exception->getMessage()));
        }
        return true;
    }

    /**
     * @param $orderId
     * @return bool|mixed
     * @throws CouldNotDeleteException
     */
    public function deleteById($orderId)
    {
        return $this->delete($this->get($orderId));
    }
}

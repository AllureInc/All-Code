<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Plugin;

use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Framework\Exception\CouldNotSaveException;
use Plenty\Order\Api\OrderExportRepositoryInterface;

/**
 * Class OrderSave
 * @package Plenty\Order\Model\Plugin
 */
class OrderSave
{
    /**
     * @var OrderExportRepositoryInterface
     */
    private $_orderExportRepository;

    /**
     * OrderSave constructor.
     * @param OrderExportRepositoryInterface $orderExportRepository
     */
    public function __construct(
        OrderExportRepositoryInterface $orderExportRepository
    ) {
        $this->_orderExportRepository = $orderExportRepository;
    }

    /**
     * @param OrderRepositoryInterface $subject
     * @param OrderInterface $resultOrder
     * @return OrderInterface
     * @throws CouldNotSaveException
     */
    public function afterSave(
        OrderRepositoryInterface $subject, OrderInterface $resultOrder
    ) {
        // $resultOrder = $this->_savePlentyOrder($resultOrder);

        return $resultOrder;
    }

    private function _savePlentyOrder(OrderInterface $order)
    {
        $extensionAttributes = $order->getExtensionAttributes();
        if (null !== $extensionAttributes
            && null !== $extensionAttributes->getPlentyOrder()
        ) {
            $plentyOrder = $extensionAttributes->getPlentyOrder();
            try {
                $this->_orderExportRepository->save($plentyOrder);
            } catch (\Exception $e) {
                throw new CouldNotSaveException(
                    __('Could not add order to export. [Reason: %1]', $e->getMessage()),
                    $e
                );
            }
        }

        return $order;
    }
}
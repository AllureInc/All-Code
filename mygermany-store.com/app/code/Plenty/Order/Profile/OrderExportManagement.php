<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile;

use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\OrderRepositoryInterface;

use Magento\Sales\Model\Order as SalesOrder;
use Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;
use Plenty\Order\Api\Data\Profile\OrderExportInterface;
use Plenty\Order\Api\OrderExportManagementInterface;
use Plenty\Order\Api\OrderExportRepositoryInterface;
use Plenty\Order\Api\AddressExportManagementInterface;
use Plenty\Order\Api\ContactExportManagementInterface;
use Plenty\Order\Api\PaymentExportManagementInterface;
use Plenty\Order\Rest\Order as OrderClient;
use Plenty\Order\Rest\Request\OrderDataBuilder;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Order\Model\Logger;
use Plenty\Core\Model\Source\Status;

/**
 * Class OrderExportManagement
 * @package Plenty\Order\Model
 */
class OrderExportManagement extends AbstractManagement
    implements OrderExportManagementInterface
{
    /**
     * @var OrderExportInterface
     */
    private $_profileEntity;

    /**
     * @var OrderDataBuilder
     */
    private $_orderDataBuilder;

    /**
     * @var OrderExportRepositoryInterface
     */
    private $_orderExportRepository;

    /**
     * @var PlentyOrderInterface
     */
    private $_plentyOrder;

    /**
     * @var AddressExportManagementInterface
     */
    private $_addressManagement;

    /**
     * @var ContactExportManagementInterface
     */
    private $_contactManagement;

    /**
     * @var PaymentExportManagementInterface
     */
    private $_paymentManagement;

    /**
     * @var OrderRepositoryInterface
     */
    private $_salesOrderRepository;

    /**
     * @var OrderInterface
     */
    private $_salesOrder;

    /**
     * @var int|null
     */
    private $_orderId;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;

    /**
     * OrderExportManagement constructor.
     * @param OrderClient $orderClient
     * @param OrderDataBuilder $orderDataBuilder
     * @param OrderExportRepositoryInterface $orderExportRepository
     * @param AddressExportManagementInterface $addressExportManagement
     * @param ContactExportManagementInterface $contactExportManagement
     * @param PaymentExportManagementInterface $paymentExportManagement
     * @param OrderRepositoryInterface $salesOrderRepository
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        OrderClient $orderClient,
        OrderDataBuilder $orderDataBuilder,
        OrderExportRepositoryInterface $orderExportRepository,
        AddressExportManagementInterface $addressExportManagement,
        ContactExportManagementInterface $contactExportManagement,
        PaymentExportManagementInterface $paymentExportManagement,
        OrderRepositoryInterface $salesOrderRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $orderClient;
        $this->_orderDataBuilder = $orderDataBuilder;
        $this->_orderExportRepository = $orderExportRepository;
        $this->_addressManagement = $addressExportManagement;
        $this->_contactManagement = $contactExportManagement;
        $this->_paymentManagement = $paymentExportManagement;
        $this->_salesOrderRepository = $salesOrderRepository;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        parent::__construct($dateTime, $helper, $logger, $serializer, $data);
    }

    /**
     * @return OrderExportInterface
     * @throws \Exception
     */
    public function getProfileEntity(): OrderExportInterface
    {
        if (!$this->_profileEntity) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        return $this->_profileEntity;
    }

    /**
     * @param OrderExportInterface $profileEntity
     * @return $this|OrderExportManagement
     * @throws \Exception
     */
    public function setProfileEntity(OrderExportInterface $profileEntity)
    {
        if (!$profileEntity instanceof OrderExportInterface) {
            throw new \Exception(__('Class must implement %1.', get_class(OrderExportInterface::class)));
        }

        $this->_profileEntity = $profileEntity;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getOrderId()
    {
        return $this->_orderId;
    }

    /**
     * @param $orderId
     * @return $this|OrderExportManagementInterface
     */
    public function setOrderId($orderId)
    {
        $this->_orderId = $orderId;
        return $this;
    }

    /**
     * @return $this|OrderExportManagementInterface
     * @throws \Exception
     */
    public function execute()
    {
        $collectionSize = $this->getProfileEntity()->getExportBatchSize()
            ?? OrderExportInterface::DEFAULT_EXPORT_BATCH_SIZE;

        if (!$searchCriteria = $this->getProfileEntity()->getImportSearchCriteria()) {
            $searchCriteria = $this->_searchCriteriaBuilder
                ->addFilter(PlentyOrderInterface::PLENTY_ORDER_STATUS, Status::PENDING, 'eq')
                ->setPageSize((int) $collectionSize)
                ->create();
        }

        /** @var SalesOrderCollection $collection */
        $collection = $this->_salesOrderRepository->getList($searchCriteria);

        if (!$collection->getSize()) {
            $this->addResponse(__('Orders are up to date.'), Status::SUCCESS);
            return $this;
        }

        $this->_initExportResponse();

        /** @var SalesOrder $salesOrder */
        foreach ($collection as $salesOrder) {
            $this->_initialize();

            try {
                $this->_export($salesOrder);
            } catch (\Exception $e) {
                $this->setResponse(
                    [
                        Status::ERROR => [
                            __('Could not %1 order. [Order %2, Reason: %3]',
                                $this->getOrderId()
                                    ? 'update'
                                    : 'create',
                                $this->_getSalesOrder()->getIncrementId(),
                                $e->getMessage()
                            )
                        ]
                    ], __METHOD__
                );
            }

            $this->_finalize();
        }

        $this->setResponse($this->getExportResponse());

        return $this;
    }

    /**
     * @param OrderInterface $salesOrder
     * @return $this
     * @throws \Exception
     */
    protected function _export(OrderInterface $salesOrder)
    {
        if (!$this->_canExportOrder($salesOrder)) {
            throw new \Exception(
                __('Could not export order. [Order ID %1, Reason: Order status not allowed for export. Ensure order status filters are selected in configuration tab.]',
                    $salesOrder->getIncrementId())
            );
        }

        $this->_initSalesOrder($salesOrder)
            ->_initPlentyOrder()
            ->_createContact()
            ->_createAddress()
            ->_createPayment()
            ->_createOrder()
            ->_createPaymentOrderRelation()
            ->_createOrderAddress()
            ->_createComment();

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createContact()
    {
        if ($this->_getPlentyOrder()->getPlentyOrderId()
            && $this->_getPlentyOrder()->getPlentyContactId()
        ) {
            return $this;
        }

        $this->_contactManagement->setProfileEntity($this->getProfileEntity())
            ->execute($this->_getSalesOrder(), $this->_getPlentyOrder());

        $this->setResponse($this->_contactManagement->getResponse(), 'contact');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createAddress()
    {
        if ($this->_getPlentyOrder()->getPlentyOrderId()
            && $this->_getPlentyOrder()->getPlentyBillingAddressId()
            && $this->_getPlentyOrder()->getPlentyShippingAddressId()
        ) {
            return $this;
        }

        $this->_addressManagement->setProfileEntity($this->getProfileEntity())
            ->setAddressEntity(
            $this->_getPlentyOrder()->getPlentyContactId()
                ? AddressExportManagementInterface::ADDRESS_ENTITY_CONTACT
                : AddressExportManagementInterface::ADDRESS_ENTITY_ACCOUNT
            )->execute($this->_getSalesOrder(), $this->_getPlentyOrder());

        $this->setResponse($this->_addressManagement->getResponse(), 'address');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createPayment()
    {
        if ($this->_getPlentyOrder()->getPlentyOrderId()
            && $this->_getPlentyOrder()->getPlentyPaymentId()
        ) {
            return $this;
        }

        $this->_paymentManagement->setProfileEntity($this->getProfileEntity())
            ->execute($this->_getSalesOrder(), $this->_getPlentyOrder());

        $this->setResponse($this->_paymentManagement->getResponse(), 'payment');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createOrder()
    {
        if ($this->_getPlentyOrder()->getPlentyOrderId()) {
            return $this;
        }

        $this->setOrderId($this->_getPlentyOrder()->getPlentyOrderId());

        $referrerId = $this->getProfileEntity()->getOrderReferrerId($this->_getSalesOrder()->getStoreId());
        $warehouseId = $this->getProfileEntity()->getMainWarehouseId();
        $statusId = $this->getProfileEntity()
            ->getPlentyStatusIdByOrderStatusCode($this->_getSalesOrder()->getStatus());
        $shippingProfileId = $this->getProfileEntity()
            ->getShippingProfileId($this->_getSalesOrder()->getShippingDescription(), $this->_getSalesOrder()->getStoreId());

        try {
            $this->_orderDataBuilder->buildRequest(
                $this->_getSalesOrder(),
                $this->_getPlentyOrder(),
                $statusId,
                $referrerId,
                $warehouseId,
                $shippingProfileId
            );

            $response = $this->_api->createOrder($this->_orderDataBuilder->getRequest(), $this->getOrderId());
        } catch (\Exception $e) {
            $this->setResponse(
                [
                    Status::ERROR => [
                       __('Could not %1 order. [Order %2, Reason: %3]',
                           $this->getOrderId()
                               ? 'update'
                               : 'create',
                           $this->_getSalesOrder()->getIncrementId(),
                           $e->getMessage()
                       )
                   ]
                ], 'order'
            );
            return $this;
        }

        if (!isset($response['id'])) {
            $this->setResponse(
                [
                    Status::ERROR => [
                        __('Could not retrieve order ID from response data. Refer to log for more details. [Order: %1]',
                            $this->_getSalesOrder()->getIncrementId())
                    ]
                ], 'order'
            );

            return $this;
        }

        $this->setOrderId($response['id']);

        $this->setResponse(
            [
                Status::SUCCESS => [
                    __('Order has been %1. [Magento Order: %2, Plenty Order: %3]',
                        $this->_getPlentyOrder()->getPlentyOrderId()
                            ? 'updated'
                            : 'created',
                        $this->_getSalesOrder()->getIncrementId(),
                        $this->getOrderId()
                    )
                ]
            ], 'order'
        );

        $this->_registerOrderResponse($response);

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createPaymentOrderRelation()
    {
        if ($this->_getPlentyOrder()->getPlentyOrderId()
            && !$this->_getPlentyOrder()->getPlentyPaymentId()
            && ($this->_getPlentyOrder()->getPlentyPaymentOrderAssignmentId()
                || $this->_getPlentyOrder()->getPlentyPaymentContactAssignmentId())
        ) {
            return $this;
        }

        $this->_paymentManagement->setProfileEntity($this->getProfileEntity())
            ->createPaymentRelations($this->_getSalesOrder(), $this->_getPlentyOrder());

        $this->setResponse($this->_paymentManagement->getResponse(), 'payment_order_relation');

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _createOrderAddress()
    {
        if ($this->_getPlentyOrder()->getPlentyBillingAddressId()
            && $this->_getPlentyOrder()->getPlentyShippingAddressId()
        ) {
            return $this;
        }
        $this->_addressManagement->setProfileEntity($this->getProfileEntity())
            ->setAddressEntity(AddressExportManagementInterface::ADDRESS_ENTITY_ORDER)
            ->execute($this->_getSalesOrder(), $this->_getPlentyOrder());

        $this->setResponse($this->_addressManagement->getResponse(), 'order_address');

        return $this;
    }

    /**
     * @return $this
     */
    protected function _createComment()
    {
        /** @todo Implement it */
        return $this;
    }

    /**
     * @return $this
     */
    private function _initialize()
    {
        $this->_plentyOrder =
        $this->_salesOrder =
        $this->_response =
        $this->_error = null;
        return $this;
    }

    /**
     * @param array $response
     * @return $this
     * @throws \Exception
     */
    private function _registerOrderResponse(array $response)
    {
        $this->_getPlentyOrder()->setPlentyOrderId($this->getOrderId());

        if (isset($response['referrerId'])) {
            $this->_getPlentyOrder()->setPlentyReferrerId($response['referrerId']);
        }

        if (isset($response['statusName'])) {
            $this->_getPlentyOrder()->setPlentyStatusName($response['statusName']);
        }

        if (isset($response['lockStatus'])) {
            $this->_getPlentyOrder()->setPlentyStatusLock($response['lockStatus']);
        }

        if (isset($response['locationId'])) {
            $this->_getPlentyOrder()->setPlentyLocationId($response['locationId']);
        }

        if (isset($response['statusId'])) {
            $this->_getPlentyOrder()->setPlentyStatusId($response['statusId']);
        }

        return $this;
    }

    /**
     * @return $this
     */
    private function _initExportResponse()
    {
        $this->_exportResponse = [];
        return $this;
    }

    /**
     * @param OrderInterface $order
     * @return $this
     */
    private function _initSalesOrder(OrderInterface $order)
    {
        $this->_salesOrder = $order;
        return $this;
    }

    /**
     * @return OrderInterface
     * @throws \Exception
     */
    private function _getSalesOrder()
    {
        if (!$this->_salesOrder) {
            throw new \Exception(__('Order is not set.'));
        }
        return $this->_salesOrder;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    protected function _initPlentyOrder()
    {
        $this->_plentyOrder = $this->_orderExportRepository->get($this->_getSalesOrder()->getEntityId());

        if ($this->_getPlentyOrder()->getId() && $this->_getPlentyOrder()->getProfileId()) {
            $this->_getPlentyOrder()->setUpdatedAt($this->_dateTime->gmtDate());
            return $this;
        }

        $this->_getPlentyOrder()->setProfileId($this->getProfileEntity()->getProfile()->getId())
            ->setOrderId($this->_getSalesOrder()->getEntityId())
            ->setOrderIncrementId($this->_getSalesOrder()->getIncrementId())
            ->setCustomerId($this->_getSalesOrder()->getCustomerId())
            ->setStatus(Status::PROCESSING)
            ->setMessage(__('Processing order %1.', $this->_getSalesOrder()->getIncrementId()))
            ->setCreatedAt($this->_dateTime->gmtDate());

        return $this;
    }

    /**
     * @return PlentyOrderInterface
     * @throws \Exception
     */
    private function _getPlentyOrder()
    {
        if (!$this->_plentyOrder) {
            throw new \Exception(__('Plenty order is not set.'));
        }
        return $this->_plentyOrder;
    }

    /**
     * @param OrderInterface $salesOrder
     * @return bool
     * @throws \Exception
     */
    private function _canExportOrder(OrderInterface $salesOrder)
    {
        return in_array($salesOrder->getStatus(), $this->getProfileEntity()->getOrderStatusFilter());
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _finalize()
    {
        $this->_registerPlentyOrder()
            ->_updateSalesOrder();
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _registerPlentyOrder()
    {
        $this->_getPlentyOrder()->setStatus(
            empty($this->_error)
                ? Status::CREATED
                : Status::ERROR
        );

        $statuses = [];
        foreach ($this->getResponse() as $entity => $message) {
            if (!is_array($message) || !$status = key($message)) {
                continue;
            }
            $statuses[$status] = $status;
        }

        $status = in_array(Status::ERROR, $statuses)
            ? Status::ERROR
            : Status::SUCCESS;

        $this->_exportResponse[$status][$this->_getSalesOrder()->getIncrementId()] = $this->getResponse();

        $this->_getPlentyOrder()
            ->setStatus($status)
            ->setMessage($this->_serializer->serialize($this->getResponse()))
            ->setProcessedAt($this->_dateTime->gmtDate());

        $this->_orderExportRepository->save($this->_getPlentyOrder());

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    private function _updateSalesOrder()
    {
        if ($plentyOrderId = $this->_getPlentyOrder()->getPlentyOrderId()) {
            $this->_getSalesOrder()->setData('plenty_order_id', $plentyOrderId);
        }

        if ($this->_getPlentyOrder()->getStatus() !== Status::PENDING) {
            $this->_getSalesOrder()
                ->setData('plenty_order_status', $this->_getPlentyOrder()->getStatus());
        }

        if (empty($this->getResponse())) {
            return $this;
        }

        $html = '<b>'.__('PlentyMarkets Synchronisation.').'</b><br />';
        foreach ($this->getResponse() as $response) {
            if (!is_array($response)) {
                continue;
            }
            foreach ($response as $item) {
                if (!is_array($item)) {
                    continue;
                }
                foreach ($item as $message) {
                    $html .= '<i>' . __($message) . '</i><br />';
                }
            }
        }

        $this->_getSalesOrder()
            ->addCommentToStatusHistory($html)
            ->setIsCustomerNotified(false)
            ->setIsVisibleOnFront(false);

        try {
            $this->_salesOrderRepository->save($this->_getSalesOrder());
        } catch (\Exception $e) {
            throw new \Exception(__('Could not update sales order. [Order: %1, Reason: %2]',
                $this->_getSalesOrder()->getIncrementId(), $e->getMessage()));
        }

        return $this;
    }
}
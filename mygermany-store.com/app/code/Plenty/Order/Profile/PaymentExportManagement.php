<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile;

use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;

use Plenty\Core\Model\Source\Status;
use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;
use Plenty\Order\Api\Data\Profile\OrderExportInterface;
use Plenty\Order\Api\PaymentExportManagementInterface;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Order\Rest\Payment as PaymentClient;
use Plenty\Order\Rest\Request\PaymentDataBuilder;
use Plenty\Order\Model\Logger;

/**
 * Class Payment
 * @package Plenty\Order\Model\Export\Service
 */
class PaymentExportManagement extends AbstractManagement
    implements PaymentExportManagementInterface
{
    /**
     * @var OrderExportInterface
     */
    private $_profileEntity;

    /**
     * @var PaymentDataBuilder
     */
    private $_paymentBuilder;

    /**
     * @var int
     */
    private $_paymentId;

    /**
     * @var int
     */
    private $_paymentOrderAssignmentId;

    /**
     * @var int
     */
    private $_paymentContactAssignmentId;

    /**
     * PaymentExportManagement constructor.
     * @param PaymentClient $paymentClient
     * @param PaymentDataBuilder $paymentDataBuilder
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        PaymentClient $paymentClient,
        PaymentDataBuilder $paymentDataBuilder,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $paymentClient;
        $this->_paymentBuilder = $paymentDataBuilder;
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
     * @return $this|PaymentExportManagementInterface
     * @throws \Exception
     */
    public function setProfileEntity($profileEntity)
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
    public function getPaymentId()
    {
        return $this->_paymentId;
    }

    /**
     * @param $paymentId
     * @return $this
     */
    public function setPaymentId($paymentId)
    {
        $this->_paymentId = $paymentId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaymentOrderAssignmentId()
    {
        return $this->_paymentOrderAssignmentId;
    }

    /**
     * @param $id
     * @return $this|PaymentExportManagementInterface
     */
    public function setPaymentOrderAssignmentId($id)
    {
        $this->_paymentOrderAssignmentId = $id;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getPaymentContactAssignmentId()
    {
        return $this->_paymentContactAssignmentId;
    }

    /**
     * @param $id
     * @return $this|PaymentExportManagementInterface
     */
    public function setPaymentContactAssignmentId($id)
    {
        $this->_paymentContactAssignmentId = $id;
        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return $this|int|PaymentExportManagementInterface|null
     * @throws \Exception
     */
    public function execute(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    ) {
        $this->_initResponseData();

        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set. [Order: %1]', $salesOrder->getIncrementId()));
        }

        if (!$this->getProfileEntity()->getIsActivePaymentExport()) {
            $this->addResponse(
                __('Payment export is disabled. [Order: %1]', $salesOrder->getIncrementId()),
                Status::SUCCESS
            );
            return $this;
        }

        if (!$payment = $salesOrder->getPayment()) {
            $this->addResponse(
                __('Could not export payment data. Order has no payment. [Order: %1]', $salesOrder->getIncrementId()),
                Status::SUCCESS
            );
            return $this;
        }

        if (null === $payment->getAmountPaid()
            || $salesOrder->getInvoiceCollection()->getSize() < 1
        ) {
            $this->addResponse(
                __('Could not export payment data. Order has no invoices. [Order: %1]', $salesOrder->getIncrementId()),
                Status::SUCCESS
            );
            return $this;
        }

        $this->setPaymentId($plentyOrder->getPlentyPaymentId());

        try {
            $this->_paymentBuilder->buildRequest(
                $salesOrder,
                $this->getProfileEntity()
                    ->getMopIdByOrderPaymentMethodCode($salesOrder->getPayment()->getMethod())
            );

            $request = $this->_paymentBuilder->getRequest();

            if ($this->getPaymentId()) {
                $request['id'] = $this->getPaymentId();
            }

            if (isset($request['mopId'])) {
                $plentyOrder->setPlentyPaymentMethodId($request['mopId']);
            }

            if (isset($request['status'])) {
                $plentyOrder->setPlentyPaymentStatusId($request['status']);
            }

            $response = $this->_api->createPayment($request, $this->getPaymentId());
        } catch (\Exception $e) {
            $this->addResponse(
                __('Could not %1 payment. [Order %2, Reason: %3]',
                    $this->getPaymentId()
                        ? 'update'
                        : 'create',
                    $salesOrder->getIncrementId(),
                    $e->getMessage()
                ), Status::ERROR
            );
            return $this;
        }

        if (!isset($response['id'])) {
            $this->addResponse(
                __('Could not retrieve payment ID from response data. Refer to log for more details. [Order: %1]',
                    $salesOrder->getIncrementId()
                ), Status::ERROR
            );
            return $this;
        }

        $this->setPaymentId($response['id']);

        $this->addResponse(
            __('Payment has been %1. [Order: %2, Payment ID: %3]',
                $plentyOrder->getPlentyPaymentId()
                    ? 'updated'
                    : 'created',
                $salesOrder->getIncrementId(),
                $this->getPaymentId()
            ), Status::SUCCESS
        );

        $plentyOrder->setPlentyPaymentId($this->getPaymentId());

        if (isset($response['status'])) {
            $plentyOrder->setPlentyPaymentStatusId($response['status']);
        }

        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return $this|mixed
     */
    public function createPaymentRelations(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    ) {
        $this->_initResponseData();
        $this->_createPaymentOrderRelation($salesOrder, $plentyOrder);
        $this->_createPaymentContactRelation($salesOrder, $plentyOrder);
        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return $this
     */
    protected function _createPaymentOrderRelation(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    ) {
        if (!$plentyOrder->getPlentyOrderId()
            || !$plentyOrder->getPlentyPaymentId()
            || $plentyOrder->getPlentyPaymentOrderAssignmentId()
        ) {
            return $this;
        }

        try {
            $response = $this->_api->createPaymentOrderRelation($plentyOrder->getPlentyPaymentId(), $plentyOrder->getPlentyOrderId());
        } catch (\Exception $e) {
            $this->addResponse(
                __('Could not create payment-order relation. [Order %1, Reason: %2]',
                    $salesOrder->getIncrementId(), $e->getMessage()
                ), Status::ERROR
            );
            return $this;
        }

        if (!isset($response['id'])) {
            $this->addResponse(
                __('Could not retrieve payment assignment ID from response data. Refer to log for more details. [Order: %1]',
                    $salesOrder->getIncrementId()
                ), Status::ERROR
            );
            return $this;
        }

        $this->setPaymentOrderAssignmentId($response['id']);

        $this->addResponse(
            __('Payment-order relation has been created. [Order: %1, Payment ID: %2, Assignment ID: %3]',
                $salesOrder->getIncrementId(),
                $plentyOrder->getPlentyPaymentId(),
                $this->getPaymentOrderAssignmentId()
            ), Status::SUCCESS
        );

        $plentyOrder->setPlentyPaymentOrderAssignmentId($this->getPaymentOrderAssignmentId());

        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return $this|bool|int|null
     */
    protected function _createPaymentContactRelation(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    ) {
        return $this;
        if (!$plentyOrder->getPlentyOrderId()
            || !$plentyOrder->getPlentyPaymentId()
            || $plentyOrder->getPlentyPaymentAssignmentId()
        ) {
            return $this->getPaymentOrderAssignmentId();
        }

        try {
            $response = $this->_api()->createPaymentOrderRelation($plentyOrder->getPlentyPaymentId(), $plentyOrder->getPlentyOrderId());
        } catch (\Exception $e) {
            $this->_exportResponse['error'][] = __('Could not create payment-order relation. [Order %1, Reason: %2]',
                $salesOrder->getIncrementId(), $e->getMessage());
            return false;
        }

        if (!isset($response['id'])) {
            $this->_exportResponse['error'][] = __('Could not retrieve payment assignment id from response data. Refer to log for more details. [Order: %1]',
                $salesOrder->getIncrementId());
            return false;
        }

        $this->_paymentContactAssignmentId = $response['id'];

        $this->_exportResponse['success'][] = __('Payment-order relation has been created. [Order: %1, Payment ID: %2, Assignment ID: %3]',
            $salesOrder->getIncrementId(), $plentyOrder->getPlentyPaymentId(), $this->getPaymentOrderAssignmentId());

        $plentyOrder->setPlentyPaymentAssignmentId($this->getPaymentOrderAssignmentId());

        return $this->getPaymentOrderAssignmentId();
    }

    /**
     * reset data response
     */
    private function _initResponseData()
    {
        $this->_response =
        $this->_paymentId =
        $this->_paymentOrderAssignmentId =
        $this->_paymentContactAssignmentId =
            null;
    }
}
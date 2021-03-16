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
use Plenty\Order\Api\ContactExportManagementInterface;
use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;
use Plenty\Order\Api\Data\Profile\OrderExportInterface;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Customer\Helper\Data as CustomerHelper;
use Plenty\Order\Rest\Contact as ContactClient;
use Plenty\Order\Rest\Request\ContactDataBuilder;
use Plenty\Order\Model\Logger;

/**
 * Class Contact
 * @package Plenty\Order\Model\Export\Service
 */
class ContactExportManagement extends AbstractManagement
    implements ContactExportManagementInterface
{
    /**
     * @var OrderExportInterface
     */
    private $_profileEntity;

    /**
     * @var ContactDataBuilder
     */
    private $_contactDataBuilder;

    /**
     * @var int
     */
    private $_contactId;

    /**
     * ContactExportManagement constructor.
     * @param ContactClient $contactClient
     * @param ContactDataBuilder $contactDataBuilder
     * @param CustomerHelper $customerHelper
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ContactClient $contactClient,
        ContactDataBuilder $contactDataBuilder,
        CustomerHelper $customerHelper,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $contactClient;
        $this->_helper = $customerHelper;
        $this->_contactDataBuilder = $contactDataBuilder;
        parent::__construct($dateTime, $helper, $logger, $serializer, $data);
    }

    /**
     * @return OrderExportInterface
     */
    public function getProfileEntity(): OrderExportInterface
    {
        return $this->_profileEntity;
    }

    /**
     * @param OrderExportInterface $profileEntity
     * @return $this|ContactExportManagementInterface
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
    public function getContactId()
    {
        return $this->_contactId;
    }

    /**
     * @param int $contactId
     * @return $this|ContactExportManagementInterface
     */
    public function setContactId($contactId)
    {
        $this->_contactId = $contactId;
        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return bool|int|ContactExportManagementInterface|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
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

        if (!$this->getProfileEntity()->getIsActiveCustomerExport()) {
            $this->addResponse(
                __('Contact export is disabled. [Order %1, Profile: %2]',
                    $salesOrder->getIncrementId(), $this->getProfileEntity()->getProfile()->getId()
                ), Status::SUCCESS
            );
            return $this;
        }

        $this->setContactId($this->_getContactId($salesOrder, $plentyOrder));

        try {
            $this->_contactDataBuilder
                ->buildRequest(
                    $salesOrder,
                    $this->getProfileEntity()
                        ->getDefaultStoreMapping()
                        ->getData(OrderExportInterface::PLENTY_STORE),
                    $this->getProfileEntity()->getOrderReferrerId($salesOrder->getStoreId())
            );
            $response = $this->_api->createContact($this->_contactDataBuilder->getRequest(), $this->getContactId());
        } catch (\Exception $e) {
            $this->addResponse(
                __('Could not %1 contact data. [Order %2, Reason: %3]',
                    $this->getContactId()
                        ? 'update'
                        : 'create',
                    $salesOrder->getIncrementId(),
                    $e->getMessage()
                ), Status::ERROR
            );
            $plentyOrder->setPlentyContactId($this->getContactId());
            return $this;
        }

        if (!isset($response['id'])) {
            $this->addResponse(
                __('Could retrieve contact id from response data. [Order: %1]', $salesOrder->getIncrementId()),
                Status::ERROR
            );
            return $this;
        }

        $this->addResponse(
            __('Contact has been %1. [Order: %2, Contact ID: %3]',
                $this->getContactId()
                    ? 'updated'
                    : 'created',
                $salesOrder->getIncrementId(),
                $this->getContactId()
            ), Status::SUCCESS
        );

        $this->setContactId($response['id']);
        $plentyOrder->setPlentyContactId($this->getContactId());

        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return int|mixed|null
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getContactId(
        SalesOrderInterface $salesOrder,
        PlentyOrderInterface $plentyOrder
    ) {
        if ($contactId = $plentyOrder->getPlentyContactId()) {
            return $contactId;
        }

        if (!$email = $salesOrder->getCustomerEmail()) {
            return null;
        }

        $response = $this->_api->getContactByEmail($email);

        return $response->getData('id');
    }

    /**
     * @return $this
     */
    private function _initResponseData()
    {
        $this->_contactId =
        $this->_response = null;
        return $this;
    }
}
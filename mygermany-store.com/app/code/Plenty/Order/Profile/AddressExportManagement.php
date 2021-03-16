<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile;

use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Plenty\Core\Model\Source\Status;
use Plenty\Order\Api\Data\Export\OrderInterface as PlentyOrderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Quote\Api\CartRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Order\Api\AddressExportManagementInterface;
use Plenty\Order\Api\Data\Profile\OrderExportInterface;
use Plenty\Order\Helper\Data as Helper;
use Plenty\Order\Rest\Address as AddressClient;
use Plenty\Order\Rest\Request\Order\AddressDataBuilder;
use Plenty\Order\Model\Logger;

/**
 * Class Address
 * @package Plenty\Order\Model\Export\Service
 */
class AddressExportManagement extends AbstractManagement
    implements AddressExportManagementInterface
{
    /**
     * @var OrderExportInterface
     */
    private $_profileEntity;

    /**
     * @var AddressDataBuilder
     */
    private $_addressDataBuilder;

    /**
     * @var string
     */
    private $_addressEntity;

    /**
     * @var int
     */
    private $_billingAddressId;

    /**
     * @var int
     */
    private $_shippingAddressId;

    /**
     * Quote repository.
     *
     * @var CartRepositoryInterface
     */
    private $_quoteRepository;

    /**
     * AddressExportManagement constructor.
     * @param AddressClient $addressClient
     * @param AddressDataBuilder $addressDataBuilder
     * @param CartRepositoryInterface $quoteRepository
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        AddressClient $addressClient,
        AddressDataBuilder $addressDataBuilder,
        CartRepositoryInterface $quoteRepository,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_api = $addressClient;
        $this->_addressDataBuilder = $addressDataBuilder;
        $this->_quoteRepository = $quoteRepository;
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
     * @return $this|AddressExportManagementInterface
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
     * @return string
     */
    public function getAddressEntity()
    {
        return $this->_addressEntity;
    }

    /**
     * @param string $addressEntity
     * @return $this
     */
    public function setAddressEntity(string $addressEntity)
    {
        $this->_addressEntity = $addressEntity;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getBillingAddressId()
    {
        return $this->_billingAddressId;
    }

    /**
     * @param $addressId
     * @return $this|AddressExportManagementInterface
     */
    public function setBillingAddressId($addressId)
    {
        $this->_billingAddressId = $addressId;
        return $this;
    }

    /**
     * @return int|null
     */
    public function getShippingAddressId()
    {
        return $this->_shippingAddressId;
    }

    /**
     * @param int $addressId
     * @return $this|AddressExportManagementInterface
     */
    public function setShippingAddressId($addressId)
    {
        $this->_shippingAddressId = $addressId;
        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param PlentyOrderInterface $plentyOrder
     * @return $this|AddressExportManagementInterface
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

        if (!$this->getAddressEntity()) {
            throw new \Exception(__('Address entity is not set. [Order: %1]', $salesOrder->getIncrementId()));
        }

        $plentyOrder->setIsBillingSameAsShipping(true);
        $addresses['billing'] = $salesOrder->getBillingAddress();
        if ($salesOrder->getShippingAddress()
            && !$this->_getIsShippingAddressSameAsBilling($salesOrder)
        ) {
            $addresses['shipping'] = $salesOrder->getShippingAddress();
            $plentyOrder->setIsBillingSameAsShipping(false);
        }

        foreach ($addresses as $addressType => $address) {
            try {
                $addressId = $this->_export($salesOrder, $address, $plentyOrder);
            } catch (\Exception $e) {
                $this->addResponse(
                    __('Could not export address. [Order %1, Address type: %2, Reason: %3]',
                        $salesOrder->getIncrementId(), ucfirst($addressType), $e->getMessage()),
                    Status::ERROR
                );
                continue;
            }

            $this->addResponse(
                __('%1 address has been created. [Order: %2, Address ID: %3]',
                    ucfirst($addressType), $salesOrder->getIncrementId(), $addressId),
                Status::SUCCESS
            );
        }

        if (false !== $plentyOrder->getIsBillingSameAsShipping()
            && !$plentyOrder->getPlentyShippingAddressId()
        ) {
            $plentyOrder->setPlentyShippingAddressId($plentyOrder->getPlentyBillingAddressId());
        }

        return $this;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @param OrderAddressInterface $salesOrderAddress
     * @param PlentyOrderInterface $plentyOrder
     * @return null|int
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Exception
     */
    protected function _export(
        SalesOrderInterface $salesOrder,
        OrderAddressInterface $salesOrderAddress,
        PlentyOrderInterface $plentyOrder
    ) {
        $request = $this->_addressDataBuilder->buildRequest($salesOrder, $salesOrderAddress);

        switch ($this->getAddressEntity()) {
            case self::ADDRESS_ENTITY_ACCOUNT :
                $response = $this->_api->createAccountAddress($request);
                break;
            case self::ADDRESS_ENTITY_ORDER :
                $response = $this->_api->createOrderAddress($request);
                break;
            default :
                $response = $this->_api->createContactAddress($request, $plentyOrder->getPlentyContactId());
                break;
        }

        if (!isset($response['id'])) {
            throw new \Exception(
                __('Could not retrieve address ID from response data. Refer to log for more details.'));
        }

        if ($salesOrderAddress->getAddressType() === self::ADDRESS_TYPE_BILLING) {
            $this->setBillingAddressId((int) $response['id']);
            $plentyOrder->setPlentyBillingAddressId($this->getBillingAddressId());
        } else {
            $this->setShippingAddressId((int) $response['id']);
            $plentyOrder->setPlentyShippingAddressId($this->getShippingAddressId());
        }

        return (int) $response['id'];
    }

    /**
     * reset response data
     */
    private function _initResponseData()
    {
        $this->_response =
        $this->_billingAddressId =
        $this->_shippingAddressId = null;
    }

    /**
     * @param SalesOrderInterface $salesOrder
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _getIsShippingAddressSameAsBilling(SalesOrderInterface $salesOrder) : bool
    {
        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->_quoteRepository->get($salesOrder->getQuoteId());
        return $quote->getShippingAddress()->getSameAsBilling();
    }
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request\Order;

use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Api\Data\OrderAddressInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Customer\Helper\Data as CustomerHelper;
use Plenty\Core\Model\Profile\Config\Source\Countries;

/**
 * Class AddressDataBuilder
 * @package Plenty\Order\Rest\Request\Order
 */
class AddressDataBuilder implements AddressDataInterface
{
    /**
     * @var CustomerHelper
     */
    private $_helper;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var Countries
     */
    private $_countriesFactory;

    /**
     * @var array
     */
    private $_errors;

    /**
     * AddressDataBuilder constructor.
     * @param CustomerHelper $itemHelper
     * @param DateTime $dateTime
     * @param Countries $countriesFactory
     */
    public function __construct(
        CustomerHelper $itemHelper,
        DateTime $dateTime,
        Countries $countriesFactory
    ) {
        $this->_helper = $itemHelper;
        $this->_dateTime = $dateTime;
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
     * @return $this|AddressDataInterface
     */
    public function setRequest(array $request)
    {
        $this->_request = $request;
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param OrderAddressInterface $salesOrderAddress
     * @return array|mixed
     * @throws \Exception
     */
    public function buildRequest(
        OrderInterface $salesOrder,
        OrderAddressInterface $salesOrderAddress
    ) {
        $this->_request = [];

        if (!$this->_canCreateAddress($salesOrderAddress)) {
            throw new \Exception(__('Could not export address. [Order: %1, Reason: %2].',
                $salesOrder->getIncrementId(), __('Address has missing data.')));
        }

        $options = [];

        // Add Tax Id option
        if ($vatId = $salesOrderAddress->getVatId()) {
            array_push($options, [
                'typeId' => self::OPTION_TYPE_VAT_NUMBER,
                'value' => $vatId
            ]);
        }

        // Add customer telephone option
        if ($telephone = $salesOrderAddress->getTelephone()) {
            array_push($options, [
                'typeId' => self::OPTION_TYPE_TELEPHONE,
                'value' => $telephone
            ]);
        }

        // Add customer email option
        if ($email = $salesOrderAddress->getEmail()) {
            array_push($options, [
                'typeId' => self::OPTION_TYPE_EMAIL,
                'value' => $email
            ]);
        }

        /* Fix customer name due to PayPal Express bug when
         * express method sets NULL to lastname on shipping address. */
        $firstName = $salesOrderAddress->getFirstname();
        $lastName = $salesOrderAddress->getLastname();
        if (null === $lastName && strpos($firstName, ' ') !== false) {
            $lastNameToArray = explode(' ', $firstName);
            if (count($lastNameToArray) < 3) {
                $firstName = $lastNameToArray[0];
                $lastName = $lastNameToArray[1];
            } else {
                $firstName = $lastNameToArray[0];
                $firstName .= ' '. $lastNameToArray[1];
                $lastName = $lastNameToArray[2];
            }
        }

        $this->setRequest(
            [
                'mage_order_id' => $salesOrder->getIncrementId(),
                'gender' => $salesOrder->getCustomerGender()
                    ? $salesOrder->getCustomerGender()
                    : '',
                'name1' => $salesOrderAddress->getCompany()
                    ? $salesOrderAddress->getCompany()
                    : '',
                'name2' => $firstName,
                'name3' => $lastName,
                'name4'  => '',
                'address1' => $salesOrderAddress->getStreetLine(1),
                'address2' => $salesOrderAddress->getStreetLine(2),
                'address3' => $salesOrderAddress->getStreetLine(3),
                'address4' => $salesOrderAddress->getStreetLine(4),
                'postalCode' => $salesOrderAddress->getPostcode(),
                'town' => $salesOrderAddress->getCity(),
                'stateId' => null,
                'countryId' => $this->_getCountryOfOrigin($salesOrderAddress->getCountryId()),
                'options' => $options,
                'contactRelations' => []
            ]
        );

        return $this->_request;
    }

    /**
     * @param OrderInterface $salesOrder
     * @param array $salesOrderAddresses
     * @return array
     */
    public function buildBatchRequest(
        OrderInterface $salesOrder, array $salesOrderAddresses
    ) : array
    {
        $this->_request = [];
        /** @todo implement batch request */
        return $this->_request;
    }

    /**
     * @param OrderAddressInterface $salesOrderAddress
     * @return bool
     */
    private function _canCreateAddress(OrderAddressInterface $salesOrderAddress) : bool
    {
        return $salesOrderAddress->getStreet()
            && $salesOrderAddress->getPostcode()
            && $salesOrderAddress->getCity();
    }

    /**
     * @param $code
     * @return int|mixed
     */
    protected function _getCountryOfOrigin($code)
    {
        return $this->_countriesFactory->getCountryIdByCode($code);
    }
}
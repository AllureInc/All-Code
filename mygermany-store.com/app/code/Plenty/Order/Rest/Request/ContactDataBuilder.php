<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest\Request;

use Magento\Sales\Api\Data\OrderInterface as SalesOrderInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plenty\Customer\Helper\Data as CustomerHelper;

/**
 * Class ContactDataBuilder
 * @package Plenty\Order\Rest\Request
 */
class ContactDataBuilder implements ContactDataInterface
{
    /**
     * @var CustomerHelper
     */
    private $_helper;

    /**
     * @var DateTime
     */
    private $_dateTime;

    /**
     * @var array
     */
    private $_request;

    /**
     * @var array
     */
    private $_errors = [];

    /**
     * ContactDataBuilder constructor.
     * @param CustomerHelper $helper
     * @param DateTime $dateTime
     */
    public function __construct(
        CustomerHelper $helper,
        DateTime $dateTime
    ) {
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
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
     * @return $this|ContactDataInterface
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
     * @param SalesOrderInterface $salesOrder
     * @param null $language
     * @param null $referrerId
     * @return $this|ContactDataInterface
     * @throws \Exception
     */
    public function buildRequest(
        SalesOrderInterface $salesOrder,
        $language = null,
        $referrerId = null
    ) {
        $this->_request = [];

        if (!$contact = $salesOrder->getBillingAddress()) {
            throw new \Exception(__('Could not retrieve contact data from order. [Order: %1]',
                $salesOrder->getIncrementId()));
        }

        /**
         * contact options
         * @see https://developers.plentymarkets.com/api-doc/Account#element_38
         */
        // Add telephone
        $options = [];
        if ($telephone = $contact->getTelephone()) {
            array_push($options, array(
                'typeId'    => self::OPTIONS_TYPE_TELEPHONE,
                'subTypeId' => self::OPTIONS_SUBTYPE_PRIVATE,
                'value'     => $telephone,
                'priority'   => 0
            ));
        }

        // Add email
        if ($email = $salesOrder->getCustomerEmail()) {
            array_push($options, array(
                'typeId'    => self::OPTIONS_TYPE_EMAIL,
                'subTypeId' => self::OPTIONS_SUBTYPE_PRIVATE,
                'value'     => $email,
                'priority'  => 0
            ));
        }

        $externalId = $salesOrder->getCustomerIsGuest() || !$salesOrder->getCustomerId()
            ? $salesOrder->getIncrementId()
            : $salesOrder->getCustomerId();

        $this->setRequest(
            [
                'mage_order_id' => $salesOrder->getIncrementId(),
                'number'        => $externalId,
                'externalId'    => $salesOrder->getCustomerId()
                    ? $salesOrder->getCustomerId()
                    : '',
                'typeId'        => self::TYPE_CUSTOMER,
                'firstName'     => $contact->getFirstname(),
                'lastName'      => $contact->getLastname(),
                'gender'        => $salesOrder->getCustomerPrefix() == 1
                    ? 'male'
                    : 'female',
                'formOfAddress' => $salesOrder->getCustomerPrefix()
                    ? $salesOrder->getCustomerPrefix()
                    : '',
                // 'newsletterAllowanceAt' => '',
                'classId'       => 1,
                'password'      => '',
                // 'blocked'    => '0',
                // 'rating'     => '3',
                'bookAccount'   => '',
                'lang'          => $language,
                'referrerId'    => $referrerId
                    ? $referrerId
                    : null,
                'options'       => $options,
            ]
        );

        return $this;
    }
}
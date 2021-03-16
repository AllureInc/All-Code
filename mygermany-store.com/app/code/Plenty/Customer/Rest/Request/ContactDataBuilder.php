<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest\Request;

use Magento\Customer\Api\Data\CustomerInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Customer\Helper\Data as Helper;
use Plenty\Core\Model\Profile\Type\AbstractType as ProfileEntity;

/**
 * Class ContactDataBuilder
 * @package Plenty\Customer\Rest\Request
 */
class ContactDataBuilder implements ContactDataInterface
{
    /**
     * @var Helper
     */
    protected $_helper;

    /**
     * @var array
     */
    protected $_request;

    /**
     * @var ProfileEntity
     */
    protected $_profileEntity;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var array
     */
    protected $_errors = [];

    /**
     * ContactDataBuilder constructor.
     * @param Helper $helper
     * @param DateTime $dateTime
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Helper $helper,
        DateTime $dateTime,
        StoreManagerInterface $storeManager
    ) {
        $this->_helper = $helper;
        $this->_dateTime = $dateTime;
        $this->_storeManager = $storeManager;
    }

    /**
     * @return ProfileEntity
     */
    public function getProfileEntity() : ProfileEntity
    {
        return $this->_profileEntity;
    }

    /**
     * @param ProfileEntity $profileEntity
     * @return null|ProfileEntity
     */
    public function setProfileEntity(ProfileEntity $profileEntity)
    {
        return $this->_profileEntity = $profileEntity;
    }

    /**
     * @return array
     */
    public function getRequest() : array
    {
        return $this->_request;
    }

    /**
     * @return array
     */
    public function getErrors() : array
    {
        return $this->_errors;
    }

    /**
     * @param CustomerInterface $customer
     * @return array
     * @throws \Exception
     */
    public function buildRequest(CustomerInterface $customer) : array
    {
        $this->_request = [];

        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        /**
         * contact options
         * @see https://developers.plentymarkets.com/api-doc/Account#element_38
         */
        // Add telephone
        $options = [];
        if ($telephone = $customer->getData('phone')) {
            array_push($options, array(
                'typeId'    => self::OPTIONS_TYPE_TELEPHONE,
                'subTypeId' => self::OPTIONS_SUBTYPE_PRIVATE,
                'value'     => $telephone,
                'priority'   => 0
            ));
        }

        // Add email
        if ($email = $customer->getEmail()) {
            array_push($options, array(
                'typeId'    => self::OPTIONS_TYPE_EMAIL,
                'subTypeId' => self::OPTIONS_SUBTYPE_PRIVATE,
                'value'     => $email,
                'priority'  => 0
            ));
        }

        $language = 'en'; // Mage::getStoreConfig('general/locale/code', $salesOrder->getStoreId());
        $externalId = $customer->getId();

        $referrerId = $this->getProfileEntity()->getOrderReferrerId($customer->getStoreId());
        $this->_request = [
            'number'        => 'mage-'.$customer->getId(),
            'externalId'    => $externalId,
            'typeId'        => self::TYPE_CUSTOMER,
            'firstName'     => $customer->getFirstname(),
            'lastName'      => $customer->getLastname(),
            'gender'        => $customer->getPrefix() == 1
                ? 'male'
                : 'female',
            'formOfAddress' => $customer->getPrefix()
                ? $customer->getPrefix()
                : '',
            // 'newsletterAllowanceAt' => '',
            'classId'       => 1,
            'password'      => '',
            // 'blocked'    => '0',
            // 'rating'     => '3',
            'bookAccount'   => '',
            'lang'          => $language
                ? substr($language, 0, strpos($language, '_'))
                : '',
            'referrerId'    => $referrerId
                ? $referrerId
                : null,
            'options'       => $options,
        ];

        return $this->_request;
    }
}
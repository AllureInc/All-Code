<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Customer\Rest\Request;

use Plenty\Core\Model\Profile\Type\AbstractType as ProfileEntity;
use Magento\Customer\Api\Data\CustomerInterface;

/**
 * Interface ContactDataInterface
 * @package Plenty\Customer\Rest\Request
 */
interface ContactDataInterface extends \Plenty\Customer\Rest\AbstractData\ContactDataInterface
{
    /**
     * @return ProfileEntity
     */
    public function getProfileEntity() : ProfileEntity;

    /**
     * @param ProfileEntity $profileEntity
     * @return mixed
     */
    public function setProfileEntity(ProfileEntity $profileEntity);

    /**
     * @return array
     */
    public function getRequest() : array;

    /**
     * @param CustomerInterface $customer
     * @return array
     */
    public function buildRequest(CustomerInterface $customer) : array;

    /**
     * @return array
     */
    public function getErrors() : array;
}
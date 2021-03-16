<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api;

use Plenty\Core\Api\Data\ProfileInterface;

/**
 * Interface ProfileEntityManagementInterface
 * @package Plenty\Core\Api
 */
interface ProfileEntityManagementInterface
{
    /**
     * @return ProfileInterface
     */
    public function getProfile() : ProfileInterface;

    /**
     * @param ProfileInterface $profile
     * @return $this
     */
    public function setProfile(ProfileInterface $profile);

    /**
     * @param null|string $key
     * @return array
     */
    public function getResponse($key = null);

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Rest;

/**
 * Interface ConfigInterface
 * @package Plenty\Order\Rest
 */
interface ConfigInterface
{
    /**
     * @param int $page
     * @param null $statusId
     * @return mixed
     */
    public function getSearchOrderStatuses(
        $page = 1,
        $statusId = null
    );

    /**
     * @param int $page
     * @param null $methodId
     * @return mixed
     */
    public function getSearchPaymentMethods($page = 1, $methodId = null);

    /**
     * @param int $page
     * @param null $profileId
     * @return mixed
     */
    public function getSearchShippingProfiles($page = 1, $profileId = null);
}
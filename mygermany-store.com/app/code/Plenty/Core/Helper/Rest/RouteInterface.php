<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Helper\Rest;

/**
 * Interface ProfileInterface
 * @package Plenty\Core\Api\Data
 */
interface RouteInterface
{
    /**
     * @see https://developers.plentymarkets.com/rest-doc/authentication/details#login
     */
    const LOGIN_URL                         = '/rest/login';
    const LOGOUT_URL                        = '/rest/logout';
    const ACCESS_TOKEN_URL                  = '/rest/oauth/access_token';
    const REFRESH_TOKEN_URL                 = '/rest/login/refresh';
    const LIST_STORES_URL                   = '/rest/webstores';
    const VAT_CONFIG_URL                    = '/rest/vat';

    const BATCH_URL                         = '/rest/batch';
    const LIST_WAREHOUSES_URL               = '/rest/stockmanagement/warehouses';

    /**
     * @return mixed
     */
    public function getAuthUrl();

    /**
     * @return mixed
     */
    public function getLoginUrl();

    /**
     * @return mixed
     */
    public function getLogoutUrl();

    /**
     * @return mixed
     */
    public function getAccessTokenUrl();

    /**
     * @return mixed
     */
    public function getRefreshTokenUrl();

    /**
     * @return mixed
     */
    public function getWebStoresUrl();

    /**
     * @param null $vatId
     * @return mixed
     */
    public function getVatConfigUrl($vatId = null);

    /**
     * @param null $warehouseId
     * @return mixed
     */
    public function getWarehousesUrl($warehouseId = null);
}
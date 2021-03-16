<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api\Data;

/**
 * Interface AuthInterface
 * @package Plenty\Core\Api\Data
 */
interface AuthInterface
{
    const ENTITY_ID             = 'entity_id';
    const TOKEN_TYPE            = 'token_type';
    const TOKEN_EXPIRES_IN      = 'expires_in';
    const ACCESS_TOKEN          = 'access_token';
    const REFRESH_TOKEN         = 'refresh_token';
    const LICENSE               = 'license';
    const DOMAIN                = 'domain';
    const MESSAGE               = 'message';
    const CREATED_AT            = 'created_at';
    const UPDATED_AT            = 'updated_at';

    public function getAccessToken();

    public function setAccessToken($token);

    public function getRefreshToken();

    public function setRefreshToken($token);

    public function getTokenType();

    public function setTokenType($tokenType);

    public function getExpiresIn();

    public function setExpiresIn($expiresIn);

    public function getMessage();

    public function setMessage($message);

    public function getCreatedAt();

    public function setCreatedAt($createdAt);

    public function getUpdatedAt();

    public function setUpdatedAt($updatedAt);
}
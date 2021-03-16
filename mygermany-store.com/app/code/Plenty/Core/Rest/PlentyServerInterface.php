<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Rest;

/**
 * Interface ClientInterface
 * @package Plenty\Core\Model\Http
 */
interface PlentyServerInterface
{
    /**
     * HTTP Response Codes
     */
    const HTTP_OK                                   = 200;
    const HTTP_CREATED                              = 201;
    const HTTP_MULTI_STATUS                         = 207;
    const HTTP_INVALID                              = 400;
    const HTTP_UNAUTHORIZED                         = 401;
    const HTTP_FORBIDDEN                            = 403;
    const HTTP_NOT_FOUND                            = 404;
    const HTTP_METHOD_NOT_ALLOWED                   = 405;
    const HTTP_NOT_ACCEPTABLE                       = 406;
    const HTTP_VALIDATION_ERROR                     = 422;
    const HTTP_INTERNAL_ERROR                       = 500;
    const HTTP_BAD_PARAMS                           = 'E1';

    /**
     * HTTP Response Exceptions
     */
    const HTTP_MESSAGE_UNAUTHENTICATED              = 'Unauthenticated.';
    const HTTP_INVALID_REFRESH_TOKEN                = 'The refresh token is invalid.';
    const HTTP_ACCESS_DENIED_EXCEPTION              = 'League\OAuth2\Server\Exception\AccessDeniedException';
    const HTTP_AUTHENTICATION_EXCEPTION             = 'Illuminate\Auth\AuthenticationException';
    const HTTP_INVALID_REFRESH_EXCEPTION            = 'League\OAuth2\Server\Exception\InvalidRefreshException';
    const HTTP_INVALID_REQUEST_EXCEPTION            = 'League\OAuth2\Server\Exception\InvalidRequestException';
    const HTTP_VALIDATION_EXCEPTION                 = 'Plenty\Exceptions\ValidationException';
    const HTTP_VALIDATION_INCORRECT_CREDENTIALS     = 'invalid_credentials';

    /**
     * HTTP Rate Limits
     */
    const HTTP_RATE_LIMIT_TIME_IN_SECONDS           = 60;
    const HTTP_RATE_LIMIT_CALLS_LIMIT               = 150;
}

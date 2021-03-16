<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Api;

use Webkul\MpPushNotification\Api\Data\UsersTokenInterface;

/**
 * MpPushNotification template CRUD interface
 * @api
 */
interface UsersTokenRepositoryInterface
{
    /**
     * get by token id
     * @param  string $token
     * @return UsersTokenInterface
     */
    public function getByToken($token);

    /**
     * get by ids
     * @param  array  $ids
     * @return UsersTokenInterface
     */
    public function getByIds(array $ids);
}

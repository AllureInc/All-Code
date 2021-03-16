<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Api\Data;

interface UsersTokenInterface
{
    /**
     * Constants for keys of data array.
     * Identical to the name of the getter in snake case.
     */
    const ID = 'entity_id';
    const TOKEN = 'token';
    const BROWSER = 'browser';
    const CREATED_AT = 'created_at';
    const NAME = 'name';

    /**
     * get id
     * @return string
     */
    public function getId();

    /**
     * set id
     * @param int $id
     * @return  UsersTokenInterface
     */
    public function setId($id);

    /**
     * get token
     * @return string
     */
    public function getToken();

    /**
     * set token
     * @param string $token
     * @return  UsersTokenInterface
     */
    public function setToken($token);

    /**
     * get browser
     * @return string
     */
    public function getBrowser();

    /**
     * set browser
     * @param string $browser
     * @return  UsersTokenInterface
     */
    public function setBrowser($browser);

    /**
     * get created time
     * @return timestamp
     */
    public function getcreatedAt();

    /**
     * set created time
     * @param timestamp $createdAt
     * @return  UsersTokenInterface
     */
    public function setCreatedAt($createdAt);

    /**
     * get name
     * @return string
     */
    public function getName();

    /**
     * set name
     * @param name $name
     * @return  UsersTokenInterface
     */
    public function setName($name);
}

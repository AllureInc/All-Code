<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api\Data;

use Plenty\Core\Model\Profile;

/**
 * Interface ProfileInterface
 * @package Plenty\Core\Api\Data
 */
interface ProfileInterface
{
    const ENTITY_ID         = 'entity_id';
    const NAME              = 'name';
    const IS_ACTIVE         = 'is_active';
    const ENTITY            = 'entity';
    const ADAPTOR           = 'adaptor';
    const STATUS            = 'status';
    const CRONTAB           = 'crontab';
    const MESSAGE           = 'message';
    const CREATED_AT        = 'created_at';
    const UPDATED_AT        = 'updated_at';

    /**
     * @return mixed
     */
    public function getId();

    /**
     * @return mixed
     */
    public function getName();

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name);

    /**
     * @return mixed
     */
    public function getIsActive();

    /**
     * @param $status
     * @return mixed
     */
    public function setIsActive($status);

    /**
     * @return mixed
     */
    public function getEntity();

    /**
     * @param $entityType
     * @return mixed
     */
    public function setEntity($entityType);

    /**
     * @return mixed
     */
    public function getAdaptor();

    /**
     * @param $type
     * @return mixed
     */
    public function setAdaptor($type);

    /**
     * @return mixed
     */
    public function getStatus();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return mixed
     */
    public function getCrontab();

    /**
     * @param $status
     * @return mixed
     */
    public function setCrontab($crontab);

    /**
     * @return mixed
     */
    public function getMessage();

    /**
     * @param $message
     * @return mixed
     */
    public function setMessage($message);


    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getCreatedAt();

    /**
     * Set created at time
     *
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * Get created at time
     *
     * @return string|null
     */
    public function getUpdatedAt();

    /**
     * Set created at time
     *
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @param $path
     * @param null $store
     * @return mixed
     */
    public function getStoreConfig($path, $store = null);

    /**
     * @return Profile\Type\AbstractType
     */
    public function getTypeInstance();

    /**
     * @param $instance
     * @return mixed
     */
    public function setTypeInstance($instance);

    /**
     * @return mixed
     */
    public function execute();
}

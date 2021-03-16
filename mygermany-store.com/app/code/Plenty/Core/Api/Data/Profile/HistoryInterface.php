<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api\Data\Profile;

/**
 * Interface HistoryInterface
 * @package Plenty\Core\Api\Data\Profile
 */
interface HistoryInterface
{
    const ENTITY_ID             = 'entity_id';
    const PROFILE_ID            = 'profile_id';
    const ACTION_CODE           = 'action_code';
    const STATUS                = 'status';
    const PARAMS                = 'params';
    const MESSAGE               = 'message';
    const CREATED_AT            = 'created_at';
    const PROCESSED_AT          = 'processed_at';

    /**
     * @return int
     */
    public function getId();

    /**
     * @return int
     */
    public function getProfileId();

    /**
     * @param $profileId
     * @return $this
     */
    public function setProfileId($profileId);

    /**
     * @return string
     */
    public function getActionCode();

    /**
     * @param $actionCode
     * @return $this
     */
    public function setActionCode($actionCode);

    /**
     * @return string
     */
    public function getStatus();

    /**
     * @param $status
     * @return $this
     */
    public function setStatus($status);

    /**
     * @return array
     */
    public function getParams();

    /**
     * @param $params
     * @return $this
     */
    public function setParams($params);

    /**
     * @return array
     */
    public function getMessage();

    /**
     * @param $message
     * @return $this
     */
    public function setMessage($message);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param $date
     * @return $this
     */
    public function setCreatedAt($date);

    /**
     * @return string
     */
    public function getProcessedAt();

    /**
     * @param $date
     * @return $this
     */
    public function setProcessedAt($date);
}
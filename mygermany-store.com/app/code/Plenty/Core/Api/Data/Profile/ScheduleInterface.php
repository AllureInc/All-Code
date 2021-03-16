<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api\Data\Profile;

/**
 * Interface ScheduleInterface
 * @package Plenty\Core\Api\Data\Profile
 */
interface ScheduleInterface
{
    const ENTITY_ID             = 'entity_id';
    const PROFILE_ID            = 'profile_id';
    const STATUS                = 'status';
    const JOB_CODE              = 'job_code';
    const PARAMS                = 'params';
    const MESSAGE               = 'message';
    const CREATED_AT            = 'created_at';
    const SCHEDULED_AT          = 'scheduled_at';
    const EXECUTED_AT           = 'executed_at';
    const FINISHED_AT           = 'finished_at';

    public function getProfileId() : int;

    public function setProfileId($id);

    public function getStatus() : string;

    public function setStatus($status);

    public function getJobCode() : string;

    public function setJobCode($code);

    public function getParams() : array;

    public function setParams($params);

    public function getMessage() : ?string;

    public function setMessage($message);

    public function getCreatedAt() : string;

    public function setCreatedAt($createdAt);

    public function getScheduledAt() : ?string;

    public function setScheduledAt($scheduledAt);

    public function getExecutedAt() : ?string;

    public function setExecutedAt($executedAt);

    public function getFinishedAt() : ?string;

    public function setFinishedAt($finishedAt);
}
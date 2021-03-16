<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Observer\Profile;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\CacheInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\App\State;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\Profiler\Driver\Standard\StatFactory;
use Magento\Framework\Lock\LockManagerInterface;
use Magento\Framework\ShellInterface;
use Magento\Framework\Exception\NoSuchEntityException;

use Plenty\Core\Helper\Data as Helper;
use Plenty\Core\Model\Profile;
use Plenty\Core\Model\ProfileFactory;
use Plenty\Core\Model\ResourceModel\Profile\Collection as ProfileCollection;
use Plenty\Core\Model\ResourceModel\Profile\CollectionFactory as ProfileCollectionFactory;
use Plenty\Core\Model\ResourceModel\Profile\History as HistoryResourceModel;
use Plenty\Core\Model\Profile\Schedule;
use Plenty\Core\Model\Profile\ScheduleFactory;
use Plenty\Core\Model\Source\Status;
use Plenty\Core\Model\Logger;

/**
 * Class CronSchedule
 * @package Plenty\Core\Observer\Profile
 * @version since 0.1.1
 */
class CronSchedule implements ObserverInterface
{
    /**#@+
     * Cache key values
     */
    const CACHE_KEY_LAST_SCHEDULE_GENERATE_AT = 'plenty_profile_cron_last_schedule_generate_at';

    const CACHE_KEY_LAST_HISTORY_CLEANUP_AT = 'plenty_profile_cron_last_history_cleanup_at';

    /**
     * Flag for internal communication between processes for running
     * all jobs in a group in parallel as a separate process
     */
    const STANDALONE_PROCESS_STARTED = 'standaloneProcessStarted';

    const DEFAULT_CRON_GROUP = 'plenty_profile';

    /**#@-*/

    /**#@+
     * List of configurable constants used to calculate and validate during handling cron jobs
     */
    const XML_PATH_SCHEDULE_GENERATE_EVERY = 'schedule_generate_every';

    const XML_PATH_SCHEDULE_AHEAD_FOR = 'schedule_ahead_for';

    const XML_PATH_SCHEDULE_LIFETIME = 'schedule_lifetime';

    const XML_PATH_HISTORY_CLEANUP_EVERY = 'history_cleanup_every';

    const XML_PATH_HISTORY_SUCCESS = 'history_success_lifetime';

    const XML_PATH_HISTORY_FAILURE = 'history_failure_lifetime';

    /**#@-*/

    /**
     * Value of seconds in one minute
     */
    const SECONDS_IN_MINUTE = 60;

    /**
     * How long to wait for cron group to become unlocked
     */
    const LOCK_TIMEOUT = 5;

    const HISTORY_LIFETIME = 1209600;

    /**
     * Static lock prefix for cron group locking
     */
    const LOCK_PREFIX = 'CRON_GROUP_';

    const LOG_MSG = 'SCHEDULE';

    const LOG_CRITICAL = 'critical';

    const LOG_ERROR = 'error';

    const LOG_WARNING = 'warning';

    const LOG_INFO = 'info';

    const LOG_DEBUG = 'debug';

    /**
     * @var null
     */
    private $_helper;

    /**
     * @var ProfileFactory 
     */
    private $_profileFactory;

    /**
     * @var ProfileCollectionFactory
     */
    private $_profileCollectionFactory;

    /**
     * @var HistoryResourceModel
     */
    private $_historyResourceModel;

    /**
     * @var ScheduleFactory
     */
    private $_scheduleFactory;
    
    /**
     * @var CacheInterface
     */
    private $_cache;

    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * @var DateTime
     */
    private $_dateTime;
    
    /**
     * @var Logger
     */
    private $_logger;

    /**
     * @var State
     */
    private $_state;

    /**
     * @var Stat
     */
    private $_statProfiler;

    /**
     * @var LockManagerInterface
     */
    private $_lockManager;

    /**
     * @var \Magento\Framework\ShellInterface
     */
    private $_shell;

    /**
     * @var array
     */
    private $_invalid = [];

    /**
     * ProcessCronSchedule constructor.
     * @param Helper $helper
     * @param ProfileFactory $profileFactory
     * @param ProfileCollectionFactory $profileCollectionFactory
     * @param ScheduleFactory $scheduleFactory
     * @param HistoryResourceModel $historyResourceModel
     * @param CacheInterface $cache
     * @param ScopeConfigInterface $scopeConfig
     * @param DateTime $dateTime
     * @param Logger $logger
     * @param State $state
     * @param StatFactory $statFactory
     * @param LockManagerInterface $lockManager
     * @param ShellInterface $shell
     */
    public function __construct(
        Helper $helper,
        ProfileFactory $profileFactory,
        ProfileCollectionFactory $profileCollectionFactory,
        ScheduleFactory $scheduleFactory,
        HistoryResourceModel $historyResourceModel,
        CacheInterface $cache,
        ScopeConfigInterface $scopeConfig,
        DateTime $dateTime,
        Logger $logger,
        State $state,
        StatFactory $statFactory,
        LockManagerInterface $lockManager,
        ShellInterface $shell
    ) {
        $this->_helper = $helper;
        $this->_profileFactory = $profileFactory;
        $this->_profileCollectionFactory = $profileCollectionFactory;
        $this->_scheduleFactory = $scheduleFactory;
        $this->_historyResourceModel = $historyResourceModel;
        $this->_cache = $cache;
        $this->_scopeConfig = $scopeConfig;
        $this->_dateTime = $dateTime;
        $this->_logger = $logger;
        $this->_state = $state;
        $this->_statProfiler = $statFactory->create();
        $this->_lockManager = $lockManager;
        $this->_shell = $shell;
    }

    /**
     * Process cron schedule
     * Generate tasks schedule
     * Cleanup tasks schedule
     * 
     * @param Observer $observer
     * @throws NoSuchEntityException
     */
    public function execute(Observer $observer)
    {
        $currentTime = $this->_dateTime->gmtTimestamp();
        $this->_lockGroup(
            function () use ($currentTime) {
                $this->_cleanupJobs($currentTime);
                $this->_generateSchedules();
                $this->processPendingJobs($currentTime);
            }
        );
    }

    /**
     * Lock group
     * It should be taken by standalone (child) process, not by the parent process.
     *
     * @param callable $callback
     * @return $this|bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _lockGroup(callable $callback)
    {
        if (!$this->_lockManager->lock(self::LOCK_PREFIX . self::DEFAULT_CRON_GROUP, self::LOCK_TIMEOUT)) {
            $this->_logResponse(self::LOG_CRITICAL, self::LOG_MSG,
                [sprintf("Could not acquire lock for cron group: %s, skipping run.", self::DEFAULT_CRON_GROUP)]);
            return false;
        }
        try {
            $callback();
        } finally {
            $this->_lockManager->unlock(self::LOCK_PREFIX . self::DEFAULT_CRON_GROUP);
        }

        return $this;
    }

    /**
     *  Generate cron schedule
     * 
     * @param $groupId
     * @return $this
     * @throws \Magento\Framework\Exception\CronException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _generateSchedules()
    {
        $lastRun = (int) $this->_cache->load(self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . self::DEFAULT_CRON_GROUP);
        $rawSchedulePeriod = (int) $this->_getCronGroupConfigValue(self::XML_PATH_SCHEDULE_GENERATE_EVERY);
        $schedulePeriod = $rawSchedulePeriod * self::SECONDS_IN_MINUTE;

        if ($lastRun > $this->_dateTime->gmtTimestamp() - $schedulePeriod) {
            $this->_logResponse(self::LOG_INFO, self::LOG_MSG, [sprintf("Too early to generate schedules.")]);
            return $this;
        }

        $this->_cache->save(
            $this->_dateTime->gmtTimestamp(),
            self::CACHE_KEY_LAST_SCHEDULE_GENERATE_AT . self::DEFAULT_CRON_GROUP,
            ['crontab'],
            null
        );
        
        $pendingSchedules = $this->_getPendingSchedules();

        $exists = [];
        /** @var Schedule $schedule */
        foreach ($pendingSchedules as $schedule) {
            $exists[$schedule->getProfileId() . '/' . $schedule->getJobCode() . '/' . $schedule->getScheduledAt()] = 1;
        }

        $jobs = $this->_profileCollectionFactory->create()
            ->addAvailabilityFilter()
            ->load();

        $this->_invalid = [];
        $this->_generateJobs($jobs, $exists);
        $this->_cleanupScheduleMismatches();

        return $this;
    }

    /**
     * @param ProfileCollection $jobs
     * @param $exists
     * @return $this|bool
     * @throws \Magento\Framework\Exception\CronException
     */
    protected function _generateJobs(ProfileCollection $jobs, array $exists)
    {
        if (!$jobs->getSize()) {
            return false;
        }

        /** @var Profile $job */
        foreach ($jobs as $job) {
            if (!$cronExpression = $job->getCrontab()) {
                continue;
            }

            $timeInterval = $this->_getScheduleTimeInterval();
            $this->_saveSchedule($job, $cronExpression, $timeInterval, $exists);
        }
        return $this;
    }

    /**
     * @param Profile $profile
     * @param $cronExpression
     * @param $timeInterval
     * @param $exists
     * @throws \Magento\Framework\Exception\CronException
     */
    protected function _saveSchedule(Profile $profile, $cronExpression, $timeInterval, $exists)
    {
        $jobCode = $profile->getEntity().'_'.$profile->getAdaptor();

        $currentTime = $this->_dateTime->gmtTimestamp();
        $timeAhead = $currentTime + $timeInterval;
        for ($time = $currentTime; $time < $timeAhead; $time += self::SECONDS_IN_MINUTE) {
            $scheduledAt = strftime('%Y-%m-%d %H:%M:00', $time);
            $alreadyScheduled = !empty($exists[$profile->getId() . '/' . $jobCode . '/' . $scheduledAt]);
            
            $schedule = $this->_createSchedule($profile->getId(), $jobCode, $cronExpression, $time);
            $valid = $schedule->trySchedule();
            if (!$valid) {
                if ($alreadyScheduled) {
                    if (!isset($this->_invalid[$profile->getId()])) {
                        $this->_invalid[$profile->getId()] = [];
                    }
                    $this->_invalid[$profile->getId()][] = $scheduledAt;
                }
                continue;
            }

            if (!$alreadyScheduled) {
                $schedule->save();
            }
        }
    }

    /**
     * Create a schedule of cron job.
     *
     * @param $profileId
     * @param $jobCode
     * @param $cronExpression
     * @param $time
     * @return Schedule
     * @throws \Magento\Framework\Exception\CronException
     */
    protected function _createSchedule($profileId, $jobCode, $cronExpression, $time)
    {
        $schedule = $this->_scheduleFactory->create()
            ->setProfileId($profileId)
            ->setCronExpr($cronExpression)
            ->setJobCode($jobCode)
            ->setStatus(Status::PENDING)
            ->setMessage(__('Scheduled.'))
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->_dateTime->gmtTimestamp()))
            ->setScheduledAt(strftime('%Y-%m-%d %H:%M', $time));

        return $schedule;
    }

    /**
     * Clean expired jobs
     *
     * @param $groupId
     * @param $currentTime
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _cleanupJobs($currentTime)
    {
        // check if history cleanup is needed
        $lastCleanup = (int) $this->_cache->load(self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . self::DEFAULT_CRON_GROUP);
        $historyCleanUp = (int) $this->_getCronGroupConfigValue(self::XML_PATH_HISTORY_CLEANUP_EVERY);

        if ($lastCleanup > $this->_dateTime->gmtTimestamp() - $historyCleanUp * self::SECONDS_IN_MINUTE) {
            $this->_logResponse(self::LOG_INFO, self::LOG_MSG, [sprintf("Too early to cleanup schedules.")]);
            return $this;
        }

        $this->_cache->save(
            $this->_dateTime->gmtTimestamp(),
            self::CACHE_KEY_LAST_HISTORY_CLEANUP_AT . self::DEFAULT_CRON_GROUP,
            ['crontab'],
            null
        );

        $this->_cleanupDisabledJobs();

        $historySuccess = (int) $this->_getCronGroupConfigValue(self::XML_PATH_HISTORY_SUCCESS);
        $historyFailure = (int) $this->_getCronGroupConfigValue(self::XML_PATH_HISTORY_FAILURE);
        $historyLifetimes = [
            Status::RUNNING => 10800,
            Status::SUCCESS => $historySuccess * self::SECONDS_IN_MINUTE,
            Status::MISSED => $historyFailure * self::SECONDS_IN_MINUTE,
            Status::ERROR => $historyFailure * self::SECONDS_IN_MINUTE,
            Status::PENDING => max($historyFailure, $historySuccess) * self::SECONDS_IN_MINUTE,
        ];

        $scheduleResource = $this->_scheduleFactory->create()->getResource();
        $connection = $scheduleResource->getConnection();
        $count = 0;
        foreach ($historyLifetimes as $status => $time) {
            $count += $connection->delete(
                $scheduleResource->getMainTable(),
                [
                    'status = ?' => $status,
                    'created_at < ?' => $connection->formatDate($currentTime - $time)
                ]
            );
        }

        if ($count) {
            $this->_logResponse(self::LOG_INFO, self::LOG_MSG, [sprintf('Schedules have been cleaned. [No: %s]', $count)]);
        }

        $this->_cleanupProfileHistory($currentTime);

        return $this;
    }

    /**
     * @return $this|bool
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _cleanupDisabledJobs()
    {
        $profiles = $this->_profileCollectionFactory->create()
            ->load();

        $jobsToCleanup = [];
        /** @var Profile $profile */
        foreach ($profiles as $profile) {
            if ($profile->getCrontab()) {
                continue;
            }
            $jobsToCleanup[] = $profile->getId();
        }

        if (empty($jobsToCleanup)) {
            return false;
        }

        $scheduleResource = $this->_scheduleFactory->create()->getResource();
        $count = $scheduleResource->getConnection()->delete(
            $scheduleResource->getMainTable(),
            [
                'status = ?' => Status::PENDING,
                'profile_id in (?)' => $jobsToCleanup,
            ]
        );

        $this->_logResponse(self::LOG_INFO, self::LOG_MSG,
            [sprintf('Disabled schedules have been cleaned. [No: %s, Profile ID: %s]', $count, implode(', ', $jobsToCleanup))]);

        return $this;
    }
    
    /**
     * Clean up scheduled jobs that do not match their cron expression anymore.
     * This can happen after cron expression is changed and cache is flushed.
     * 
     * @return $this
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _cleanupScheduleMismatches()
    {
        /** @var \Plenty\Core\Model\ResourceModel\Profile\Schedule $scheduleResource */
        $scheduleResource = $this->_scheduleFactory->create()->getResource();
        foreach ($this->_invalid as $profileId => $scheduledAtList) {

            $scheduleResource->getConnection()->delete($scheduleResource->getMainTable(), [
                'status = ?' => Status::PENDING,
                'profile_id = ?' => (int) $profileId,
                'scheduled_at in (?)' => $scheduledAtList,
            ]);

            $this->_logResponse(self::LOG_INFO, self::LOG_MSG,
                [sprintf('Invalid schedules have been cleaned. [Profile ID: %s]', $profileId)]);
        }
        return $this;
    }

    /**
     * @param $currentTime
     * @return $this
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function _cleanupProfileHistory($currentTime)
    {
        $connection = $this->_historyResourceModel->getConnection();
        $count = $connection->delete(
            $this->_historyResourceModel->getMainTable(),
            [
                'created_at < ?' => $connection->formatDate($currentTime - self::HISTORY_LIFETIME)
            ]
        );

        if ($count) {
            $this->_logResponse(self::LOG_INFO, self::LOG_MSG, [sprintf('Profile histories have been cleaned. [No: %s]', $count)]);
        }

        return $this;
    }

    /**
     * Process pending jobs.
     *
     * @param $currentTime
     * @throws NoSuchEntityException
     * @throws \Throwable
     */
    private function processPendingJobs($currentTime)
    {
        $processedJobs = [];
        $pendingSchedules = $this->_getPendingSchedules();
        /** @var Schedule $schedule */
        foreach ($pendingSchedules as $schedule) {

            // process only one job per run
            if (isset($processedJobs[$schedule->getProfileId() . '_' . $schedule->getJobCode()])) {
                continue;
            }

            $scheduledTime = strtotime($schedule->getScheduledAt());
            if ($scheduledTime > $currentTime) {
                $this->_logResponse(self::LOG_INFO, self::LOG_MSG,
                    [sprintf('Process pending jobs. Too early for job %s. [Profile ID: %s]', $schedule->getJobCode(), $schedule->getProfileId())]);
                continue;
            }

            try {
                if (!$schedule->tryLockJob()) {
                    $this->_logResponse(self::LOG_CRITICAL, self::LOG_MSG,
                        [sprintf('Process pending jobs. Failed to lock job %s. [Profile ID: %s]', $schedule->getJobCode(), $schedule->getProfileId())]);
                    continue;
                }

                /** @var Profile $profile */
                $profile = $this->_profileFactory->create();
                $profile->getResource()->load($profile, $schedule->getProfileId());

                if (!$profile->getIsActive() || !$profile->getCrontab()) {
                    $this->_logResponse(self::LOG_INFO, self::LOG_MSG,
                        [sprintf('Process pending jobs. Profile not available for job %s. [Profile ID: %s]', $schedule->getJobCode(), $schedule->getProfileId())]);
                    continue;
                }
                
                $this->_runJob($schedule, $profile, $scheduledTime, $currentTime);
            } catch (\Exception $e) {
                $this->_processError($schedule, $e);
            }

            if ($schedule->getStatus() === Status::SUCCESS) {
                $processedJobs[$schedule->getProfileId() . '_' . $schedule->getJobCode()] = true;
            }

            $schedule->save();
        }
    }

    /**
     * @param Schedule $schedule
     * @param Profile $profile
     * @param $scheduledTime
     * @param $currentTime
     * @throws \Throwable
     */
    private function _runJob(Schedule $schedule, Profile $profile, $scheduledTime, $currentTime)
    {
        $jobCode = $schedule->getJobCode();
        $scheduleLifetime = $this->_getCronGroupConfigValue(self::XML_PATH_SCHEDULE_LIFETIME);
        $scheduleLifetime = $scheduleLifetime * self::SECONDS_IN_MINUTE;
        if ($scheduledTime < $currentTime - $scheduleLifetime) {
            $this->_logResponse(self::LOG_WARNING, self::LOG_MSG,
                [sprintf('Run job. Too late for job %s. [Profile ID: %s]', $schedule->getJobCode(), $schedule->getProfileId())]);
            $schedule->setStatus(Status::MISSED);
            throw new \Exception(sprintf('Cron Job %s is missed at %s.', $jobCode, $schedule->getScheduledAt()));
        }

        $schedule->setExecutedAt(strftime('%Y-%m-%d %H:%M:%S', $this->_dateTime->gmtTimestamp()))
            ->setMessage(__('Schedule is running.'))
            ->save();


        $this->_startProfiling();
        try {
            $this->_logResponse(self::LOG_INFO, self::LOG_MSG,
                [sprintf('Run job. Cron Job %s is run. [Profile ID: %s]', $schedule->getJobCode(), $schedule->getProfileId())]);
            $profile->execute();
        } catch (\Throwable $e) {
            $schedule->setStatus(Status::ERROR);
            $this->_logResponse(self::LOG_CRITICAL, self::LOG_MSG,
                [sprintf('Run job. Cron Job %s has an error. [Profile ID: %s, Error: %s, Statistics: %s]',
                    $jobCode, $schedule->getProfileId(), $e->getMessage(), $this->_getProfilingStat())]);

            if (!$e instanceof \Exception) {
                $e = new \RuntimeException('Error when running a cron job.', 0, $e);
            }
            throw $e;
        } finally {
            $this->_stopProfiling();
        }

        $schedule->setStatus(Status::SUCCESS)
            ->setFinishedAt(strftime('%Y-%m-%d %H:%M:%S', $this->_dateTime->gmtTimestamp()))
            ->setMessage('Complete.');

        $this->_logResponse(self::LOG_INFO, self::LOG_MSG,
            [sprintf('Run job. Cron Job %s is successfully finished. [Profile ID: %s, Statistics: %s]',
                $jobCode, $schedule->getProfileId(), $this->_getProfilingStat())]);
    }

    /**
     * Process error messages.
     *
     * @param Schedule $schedule
     * @param \Exception $exception
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function _processError(Schedule $schedule, \Exception $exception)
    {
        $schedule->setMessage($exception->getMessage());
        if ($schedule->getStatus() === Status::ERROR) {
            $this->_logResponse(self::LOG_CRITICAL, self::LOG_MSG, [sprintf('Error: %s', $exception)]);
        }
        if ($schedule->getStatus() === Status::MISSED
            && $this->_state->getMode() === State::MODE_DEVELOPER
        ) {
            $this->_logResponse(self::LOG_WARNING, self::LOG_MSG, [sprintf('Missed: %s', $schedule->getMessage())]);
        }
    }

    /**
     * @return \Plenty\Core\Model\ResourceModel\Profile\Schedule\Collection
     */
    private function _getPendingSchedules()
    {
        $pendingJobs = $this->_scheduleFactory->create()->getCollection();
        $pendingJobs->addPendingFilter()
            ->orderByScheduledAt();
        return $pendingJobs;
    }

    /**
     * Get CronGroup Configuration Value.
     *
     * @param $path
     * @return mixed
     */
    private function _getCronGroupConfigValue($path)
    {
        return $this->_scopeConfig->getValue(
            'system/cron/' . self::DEFAULT_CRON_GROUP . '/' . $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @return float|int
     */
    private function _getScheduleTimeInterval()
    {
        $scheduleAheadFor = (int) $this->_getCronGroupConfigValue(self::XML_PATH_SCHEDULE_AHEAD_FOR);
        $scheduleAheadFor = $scheduleAheadFor * self::SECONDS_IN_MINUTE;
        return $scheduleAheadFor;
    }

    /**
     * Starts profiling
     *
     * @return void
     */
    private function _startProfiling()
    {
        $this->_statProfiler->clear();
        $this->_statProfiler->start('job', microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Stops profiling
     *
     * @return void
     */
    private function _stopProfiling()
    {
        $this->_statProfiler->stop('job', microtime(true), memory_get_usage(true), memory_get_usage());
    }

    /**
     * Retrieves statistics in the JSON format
     *
     * @return string
     */
    private function _getProfilingStat()
    {
        $stat = $this->_statProfiler->get('job');
        unset($stat[Stat::START]);
        return json_encode($stat);
    }

    /**
     * @param $type
     * @param $message
     * @param array $data
     * @param bool $forceDebug
     * @return $this|bool
     * @throws NoSuchEntityException
     */
    private function _logResponse($type, $message, array $data = [], $forceDebug = false)
    {
        if (false === $forceDebug && !$this->_helper->isDebugOn()) {
            return false;
        }

        switch ($type) {
            case self::LOG_CRITICAL :
                $this->_logger->critical($message, $data);
                break;
            case self::LOG_WARNING :
                $this->_logger->warning($message, $data);
                break;
            case self::LOG_INFO :
                $this->_logger->info($message, $data);
                break;
            default:
                $this->_logger->debug($data, $message);
                break;
        }

        return $this;
    }
}

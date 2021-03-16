<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Exception\CronException;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

use Plenty\Core\Helper\Data as Helper;
use Plenty\Core\Model\CoreAbstractModel;
use Plenty\Core\Model\ResourceModel;
use Plenty\Core\Api\Data\Profile\ScheduleInterface;
use Plenty\Core\Model\Logger;
use Plenty\Core\Model\Source\Status;

/**
 * Class Schedule
 * @package Plenty\Core\Model\Profile
 *
 * @method ResourceModel\Profile\Schedule getResource()
 * @method ResourceModel\Profile\Schedule\Collection getCollection()
 * @method array getCronExprArr()
 * @method Schedule setCronExprArr(array $value)
 */
class Schedule extends CoreAbstractModel implements ScheduleInterface,
    IdentityInterface
{
    /**
     * Cache Identity tag
     */
    const CACHE_TAG             = 'plenty_core_profile_schedule';

    /**
     * @var string
     */
    protected $_cacheTag        = 'plenty_core_profile_schedule';

    /**
     * @var string
     */
    protected $_eventPrefix     = 'plenty_core_profile_schedule';

    /**
     * @var TimezoneInterface
     */
    protected $_timezoneConverter;

    /**
     * Resource constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Profile\Schedule::class);
    }

    /**
     * Schedule constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param TimezoneInterface|null $timezoneConverter
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        ?TimezoneInterface $timezoneConverter = null,
        array $data = []
    ) {
        $this->_timezoneConverter = $timezoneConverter ?: ObjectManager::getInstance()->get(TimezoneInterface::class);
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int
     */
    public function getProfileId() : int
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param $id
     * @return Schedule
     */
    public function setProfileId($id)
    {
        return $this->setData(self::PROFILE_ID, $id);
    }

    /**
     * @return string
     */
    public function getStatus() : string
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return Schedule
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return string
     */
    public function getJobCode() : string
    {
        return $this->getData(self::JOB_CODE);
    }

    public function setJobCode($code)
    {
        return $this->setData(self::JOB_CODE, $code);
    }

    /**
     * @return array
     */
    public function getParams() : array
    {
        if (!$entries = $this->getData(self::PARAMS)) {
            return [];
        }
        return $this->_serializer->unserialize($entries);
    }

    /**
     * @param $params
     * @return Schedule
     */
    public function setParams($params)
    {
        if (is_array($params)) {
            $this->_serializer->serialize($params);
        }
        return $this->setData(self::PARAMS, $params);
    }

    /**
     * @return string
     */
    public function getMessage() : ?string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return Schedule
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @return string
     */
    public function getCreatedAt() : string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return Schedule
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return string
     */
    public function getScheduledAt() : ?string
    {
        return $this->getData(self::SCHEDULED_AT);
    }

    /**
     * @param $scheduledAt
     * @return Schedule
     */
    public function setScheduledAt($scheduledAt)
    {
        return $this->setData(self::SCHEDULED_AT, $scheduledAt);
    }

    /**
     * @return string
     */
    public function getExecutedAt() : ?string
    {
        return $this->getData(self::EXECUTED_AT);
    }

    /**
     * @param $executedAt
     * @return Schedule
     */
    public function setExecutedAt($executedAt)
    {
        return $this->setData(self::EXECUTED_AT, $executedAt);
    }

    /**
     * @return string
     */
    public function getFinishedAt() : ?string
    {
        return $this->getData(self::FINISHED_AT);
    }

    /**
     * @param $finishedAt
     * @return Schedule
     */
    public function setFinishedAt($finishedAt)
    {
        return $this->setData(self::FINISHED_AT, $finishedAt);
    }

    /**
     * @param $expr
     * @return $this
     * @throws CronException
     */
    public function setCronExpr($expr)
    {
        $e = preg_split('#\s+#', $expr, null, PREG_SPLIT_NO_EMPTY);
        if (sizeof($e) < 5 || sizeof($e) > 6) {
            throw new CronException(__('Invalid cron expression: %1', $expr));
        }

        $this->setCronExprArr($e);
        return $this;
    }

    /**
     * @return bool
     * @throws CronException
     */
    public function trySchedule()
    {
        $time = $this->getScheduledAt();
        $expr = $this->getCronExprArr();

        if (!$expr
            || !$time
            || !isset($expr[0])
            || !isset($expr[1])
            || !isset($expr[2])
            || !isset($expr[3])
            || !isset($expr[4])
        ) {
            return false;
        }

        if (!is_numeric($time)) {
            //convert time from UTC to admin store timezone
            //we assume that all schedules in configuration (crontab.xml and DB tables) are in admin store timezone
            $time = $this->_timezoneConverter->date($time)->format('Y-m-d H:i');
            $time = strtotime($time);
        }

        $match = $this->matchCronExpression($expr[0], strftime('%M', $time))
            && $this->matchCronExpression($expr[1], strftime('%H', $time))
            && $this->matchCronExpression($expr[2], strftime('%d', $time))
            && $this->matchCronExpression($expr[3], strftime('%m', $time))
            && $this->matchCronExpression($expr[4], strftime('%w', $time));

        return $match;
    }

    /**
     * @param $expr
     * @param $num
     * @return bool
     * @throws CronException
     */
    public function matchCronExpression($expr, $num)
    {
        // handle ALL match
        if ($expr === '*') {
            return true;
        }

        // handle multiple options
        if (strpos($expr, ',') !== false) {
            foreach (explode(',', $expr) as $e) {
                if ($this->matchCronExpression($e, $num)) {
                    return true;
                }
            }
            return false;
        }

        // handle modulus
        if (strpos($expr, '/') !== false) {
            $e = explode('/', $expr);
            if (sizeof($e) !== 2) {
                throw new CronException(__('Invalid cron expression, expecting \'match/modulus\': %1', $expr));
            }
            if (!is_numeric($e[1])) {
                throw new CronException(__('Invalid cron expression, expecting numeric modulus: %1', $expr));
            }
            $expr = $e[0];
            $mod = $e[1];
        } else {
            $mod = 1;
        }

        // handle all match by modulus
        if ($expr === '*') {
            $from = 0;
            $to = 60;
        } elseif (strpos($expr, '-') !== false) {
            // handle range
            $e = explode('-', $expr);
            if (sizeof($e) !== 2) {
                throw new CronException(__('Invalid cron expression, expecting \'from-to\' structure: %1', $expr));
            }

            $from = $this->getNumeric($e[0]);
            $to = $this->getNumeric($e[1]);
        } else {
            // handle regular token
            $from = $this->getNumeric($expr);
            $to = $from;
        }

        if ($from === false || $to === false) {
            throw new CronException(__('Invalid cron expression: %1', $expr));
        }

        return $num >= $from && $num <= $to && $num % $mod === 0;
    }

    /**
     * @param $value
     * @return bool|mixed|string
     */
    public function getNumeric($value)
    {
        static $data = [
            'jan' => 1,
            'feb' => 2,
            'mar' => 3,
            'apr' => 4,
            'may' => 5,
            'jun' => 6,
            'jul' => 7,
            'aug' => 8,
            'sep' => 9,
            'oct' => 10,
            'nov' => 11,
            'dec' => 12,
            'sun' => 0,
            'mon' => 1,
            'tue' => 2,
            'wed' => 3,
            'thu' => 4,
            'fri' => 5,
            'sat' => 6,
        ];

        if (is_numeric($value)) {
            return $value;
        }

        if (is_string($value)) {
            $value = strtolower(substr($value, 0, 3));
            if (isset($data[$value])) {
                return $data[$value];
            }
        }

        return false;
    }

    /**
     * Lock the cron job so no other scheduled instances run simultaneously.
     *
     * Sets a job to STATUS_RUNNING only if it is currently in STATUS_PENDING
     * and no other jobs of the same code are currently in STATUS_RUNNING.
     * Returns true if status was changed and false otherwise.
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Zend_Db_Statement_Exception
     */
    public function tryLockJob()
    {
        if ($this->getResource()->trySetJobUniqueStatusAtomic(
            $this->getId(),
            Status::RUNNING,
            Status::PENDING
        )) {
            $this->setStatus(Status::RUNNING);
            return true;
        }
        return false;
    }
}
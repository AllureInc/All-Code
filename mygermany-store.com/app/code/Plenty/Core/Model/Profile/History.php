<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Core\Helper\Data as Helper;
use Plenty\Core\Model\CoreAbstractModel;
use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Model\Logger;
use Plenty\Core\Model\Profile;
use Plenty\Core\Model\Source\Status;

/**
 * Class History
 * @package Plenty\Core\Model\Profile
 *
 * @method \Plenty\Core\Model\ResourceModel\Profile\History getResource()
 * @method \Plenty\Core\Model\ResourceModel\Profile\History\Collection getCollection()
 */
class History extends CoreAbstractModel implements HistoryInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_core_history';

    /**
     * @var string
     */
    protected $_cacheTag        = 'plenty_core_history';

    /**
     * @var string
     */
    protected $_eventPrefix     = 'plenty_core_history';

    /**
     * @var ScheduleFactory
     */
    private $_scheduleFactory;

    /**
     * Resource constructor.
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\ResourceModel\Profile\History::class);
    }

    /**
     * History constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param ScheduleFactory $scheduleFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        Profile\ScheduleFactory $scheduleFactory,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_scheduleFactory = $scheduleFactory;
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }
    
    /**
     * @return int
     */
    public function getProfileId()
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @param $profileId
     * @return mixed|History
     */
    public function setProfileId($profileId)
    {
        return $this->setData(self::PROFILE_ID, $profileId);
    }

    /**
     * @return string
     */
    public function getActionCode()
    {
        return $this->getData(self::ACTION_CODE);
    }

    /**
     * @param $actionCode
     * @return mixed|History
     */
    public function setActionCode($actionCode)
    {
        return $this->setData(self::ACTION_CODE, $actionCode);
    }

    /**
     * @return string
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return History
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return array
     */
    public function getParams()
    {
        if (!$entries = $this->getData(self::PARAMS)) {
            return [];
        }
        return $this->_serializer->unserialize($entries);
    }

    /**
     * @param $params
     * @return mixed|History
     */
    public function setParams($params)
    {
        return $this->setData(self::PARAMS, is_array($params)
            ? $this->_serializer->serialize($params)
            : $params);
    }

    /**
     * @return string
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return History
     */
    public function setMessage($message)
    {
        $this->setData(self::MESSAGE, $message);
        return $this;
    }

    /**
     * @return string
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $date
     * @return mixed|History
     */
    public function setCreatedAt($date)
    {
        return $this->setData(self::CREATED_AT, $date);
    }

    /**
     * @return string
     */
    public function getProcessedAt()
    {
        return $this->getData(self::PROCESSED_AT);
    }

    /**
     * @param $date
     * @return History
     */
    public function setProcessedAt($date)
    {
        return $this->setData(self::PROCESSED_AT, $date);
    }

    /**
     * @param Profile $profile
     * @param array $params
     * @return bool|Schedule
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function registerProfileSchedule(Profile $profile, array $params = [])
    {
        if ($profile->getId() != $this->getProfileId()) {
            throw new \Exception(__('Profile does not match it\'s history object. [Profile ID: %1, History ID: %2]',
                $profile->getId(), $this->getProfileId()));
        }

        $jobCode = $profile->getEntity().'_'.$profile->getAdaptor();

        $schedule = $this->_scheduleFactory->create();
        $pendingSchedules = $schedule->getResource()
            ->getPendingProfileHistory($profile);
        if ($pendingSchedules) {
            return false;
        }

        $scheduledAt = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i") + 5, date("s"),
            date("m"), date("d"), date("Y")));

        $schedule
            ->setProfileId($profile->getId())
            ->setJobCode($jobCode)
            ->setStatus(Status::PENDING)
            ->setMessage(__('Scheduled.'))
            ->setParams($this->_serializer->serialize($params))
            ->setCreatedAt(strftime('%Y-%m-%d %H:%M:%S', $this->_dateTime->gmtTimestamp()))
            ->setScheduledAt($scheduledAt);

        try {
            $schedule->save();
            $this->setStatus(Status::PENDING)
                ->save();
        } catch (\Exception $e) {
            throw new \Exception(__('Could not create profile schedule. [Profile %1, Reason: %2]',
                $profile->getId(), $e->getMessage()));
        }

        return $schedule;
    }
}
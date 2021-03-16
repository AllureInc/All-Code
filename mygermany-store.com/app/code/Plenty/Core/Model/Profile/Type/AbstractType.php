<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Type;

use Magento\Framework\DataObject;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Profiler\Driver\Standard\Stat;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Exception\LocalizedException;
use Magento\Store\Model\StoreManagerInterface;

use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Model\Profile;
use Plenty\Core\Model\Source\Status;
use Plenty\Core\Api\Data\ProfileTypeInterface;

/**
 * Class AbstractType
 * @package Plenty\Core\Model\Profile\Type
 */
abstract class AbstractType extends DataObject
    implements ProfileTypeInterface
{
    /**
     * Profile model instance
     *
     */
    private $_profile;

    /**
     * Product type instance id
     *
     * @var string
     */
    private $_typeId;

    /**
     * @var null
     */
    protected $_helper;

    /**
     * @var null
     */
    protected $_config;

    /**
     * @var DateTime
     */
    protected $_dateTime;

    /**
     * @var array
     */
    protected $_errors;

    /**
     * @var array
     */
    protected $_messages;

    /**
     * @var array
     */
    protected $_response;

    /**
     * Core event manager proxy
     *
     * @var \Magento\Framework\Event\ManagerInterface
     */
    protected $_eventManager;

    /**
     * @var HistoryInterface
     */
    protected $_history;

    /**
     * @var Profile\HistoryFactory
     */
    protected $_historyFactory;


    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var null
     */
    protected $_writeAdaptor;

    /**
     * @var Json
     */
    protected $_serializer;

    /**
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Profile\HistoryFactory $historyFactory
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Profile\HistoryFactory $historyFactory,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_eventManager = $eventManager;
        $this->_storeManager = $storeManager;
        $this->_historyFactory = $historyFactory;
        $this->_dateTime = $dateTime;
        $this->_serializer = $serializer
            ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($data);
    }

    /**
     * Returns profile configuration scope data
     *
     * @param $path
     * @param null $store
     * @return array|null|string
     * @throws
     */
    protected function _getConfig($path, $store = null)
    {
        if (null === $store) {
            $store = $this->_storeManager->getStore();
        }

        return $this->getProfile()->getStoreConfig($path, $store);
    }

    /**
     * @return HistoryInterface
     */
    public function getHistory() : HistoryInterface
    {
        return $this->_history;
    }

    /**
     * @param HistoryInterface $history
     * @return $this|mixed
     */
    public function setHistory(HistoryInterface $history)
    {
        $this->_history = $history;
        return $this;
    }

    /**
     * @param $actionCode
     * @param $status
     * @return $this|ProfileTypeInterface
     * @throws \Exception
     */
    public function createHistory($actionCode, $status)
    {
        $history = $this->_historyFactory->create();
        $history->setProfileId($this->getProfile()->getId())
            ->setActionCode($actionCode)
            ->setStatus($status)
            ->setCreatedAt($this->_dateTime->gmtDate())
            ->save();

        $this->setHistory($history);
        return $this;
    }

    /**
     * @return $this
     */
    public function handleHistory()
    {
        if (!$response = $this->getResponse()) {
            $response[] = __('$1 has been processed but response is unknown.',
                ucwords(str_replace('_', ' ', $this->getHistory()->getActionCode())));
        }

        $this->getHistory()->getResource()
            ->update(
                $this->getHistory(), [
                    'status' => key($response) == 'error'
                        ? Status::ERROR
                        : Status::COMPLETE,
                    'message' => $this->_serializer->serialize($response),
                    'processed_at' => $this->_dateTime->gmtDate()
                ]
            );

        return $this;
    }

    /**
     * @return HistoryInterface
     * @throws \Exception
     */
    protected function _initHistory()
    {
        $historyModel = $this->_historyFactory->create();

        $pendingHistory = $historyModel->getCollection()
            ->addProfileFilter($this->getProfile()->getId())
            ->addPendingFilter();
        if ($pendingHistory->getSize()) {
            $this->_history = $pendingHistory->getFirstItem();
            try {
                $this->getHistory()->getResource()
                    ->update($this->getHistory(), [Profile\History::STATUS => Status::PROCESSING]);
            } catch (\Exception $e) {
                throw new \Exception(__('Cannot find initialize profile history task. [Profile: %1, Reason: %2]',
                    $this->getProfile()->getId(), $e->getMessage()));
            }
            return $this->getHistory();
        }

        $data = [];
        foreach ($this->getProcessStages() as $stage) {
            $data[] = [
                Profile\History::PROFILE_ID     => (int) $this->getProfile()->getId(),
                Profile\History::ACTION_CODE    => $stage,
                Profile\History::STATUS         => Status::PENDING,
                Profile\History::MESSAGE        => __(ucfirst(str_replace('_', ' ', $stage)). ' scheduled.'),
                Profile\History::CREATED_AT     => $this->_dateTime->gmtDate()
            ];
        }

        try {
            $historyModel->getResource()->addRecord($data);

            $pendingHistory = $historyModel->getCollection()
                ->addProfileFilter($this->getProfile()->getId())
                ->addPendingFilter();

            $this->_history = $pendingHistory->getFirstItem();
            $this->getHistory()->getResource()
                ->update($this->getHistory(), [Profile\History::STATUS => Status::PROCESSING]);
        } catch (\Exception $e) {
            throw new \Exception(__('Cannot create profile history task. [Profile: %1, Reason: %2]',
                $this->getProfile()->getId(), $e->getMessage()));
        }

        return $this->getHistory();
    }

    /**
     * @param $status
     * @param $actionCode
     * @param null $message
     * @return Profile\History
     * @throws \Exception
     */
    protected function _createProfileHistory($status, $actionCode, $message = null)
    {
        $historyModel = $this->_historyFactory->create();
        $this->_history = $historyModel->setProfileId($this->getProfile()->getId())
            ->setActionCode($actionCode)
            ->setStatus($status)
            ->setMessage($message)
            ->save();

        return $this->_history;
    }



    /**
     * @return $this
     * @throws \Exception
     */
    protected function _handleProfileSchedule()
    {
        $historyModel = $this->_historyFactory->create();

        /** @var Profile\History $pendingHistory */
        $pendingHistory = $historyModel->getCollection()
            ->addProfileFilter($this->getProfile()->getId())
            ->addPendingFilter()
            ->excludeActionCodeFromFilter($this->getHistory()->getActionCode())
            ->getFirstItem();
        if (!$pendingHistory->getId()) {
            return $this;
        }

        $pendingHistory->registerProfileSchedule($this->getProfile(), ['action_code' => $pendingHistory->getActionCode()]);
        return $this;
    }

    /**
     * Returns profile instance
     *
     * @return Profile
     */
    public function getProfile() : Profile
    {
        return $this->_profile;
    }

    /**
     * Specify profile type instance
     *
     * @param $profile
     * @return $this
     */
    public function setProfile(Profile $profile)
    {
        $this->_profile = $profile;
        return $this;
    }

    /**
     * @return string
     */
    public function getTypeId()
    {
        return $this->_typeId;
    }

    /**
     * Specify type identifier
     *
     * @param $typeId
     * @return $this
     */
    public function setTypeId($typeId)
    {
        $this->_typeId = $typeId;
        return $this;
    }

    /**
     * @return $this
     */
    protected function _resetResponseData()
    {
        $this->_messages =
        $this->_errors = [];
        return $this;
    }

    /**
     * @return array
     */
    public function getErrors()
    {
        return $this->_errors;
    }

    /**
     * @param string|array $error
     * @return $this
     */
    protected function addErrors($error)
    {
        $this->_errors[] = $error;
        return $this;
    }

    /**
     * @return array
     */
    public function getMessages()
    {
        return $this->_messages;
    }

    /**
     * @param string|array $message
     * @return $this
     */
    protected function addMessages($message)
    {
        $this->_messages[] = $message;
        return $this;
    }

    /**
     * @return array
     */
    public function getResponse()
    {
        return $this->_response;
    }

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function addResponse($data, $key = null)
    {
        $key
            ? $this->_response[$key][] = $data
            : $this->_response[] = $data;
        return $this;
    }

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null)
    {
        $key
            ? $this->_response[$key] = $data
            : $this->_response = $data;
        return $this;
    }

    /**
     * @param $needle
     * @param $haystack
     * @param $columnName
     * @param bool $columnId
     * @return false|int|string
     */
    public function getSearchArrayMatch($needle, $haystack, $columnName, $columnId = false)
    {
        if (!is_array($haystack)) {
            return false;
        }

        if ($columnId) {
            return array_search($needle, array_column($haystack, $columnName, $columnId));
        }

        return array_search($needle, array_column($haystack, $columnName));
    }

    /**
     * @param array $params
     */
    public function scheduleProfile(array $params = array())
    {
        /*
        $profile = $this->getProfile();
        $schedule = Mage::getModel('plenty_core/profile_schedule');
        $jobCode = $profile->getEntityType().'_'.$profile->getDirection();

        $createdAt  = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i"), date("s"),
            date("m"), date("d"), date("Y")));
        $scheduledAt = strftime("%Y-%m-%d %H:%M:%S", mktime(date("H"), date("i") + 5, date("s"),
            date("m"), date("d"), date("Y")));

        $schedule->setProfileId($profile->getId())
            ->setJobCode($jobCode)
            ->setParams($this->serialize($params))
            ->setCreatedAt($createdAt)
            ->setScheduledAt($scheduledAt)
            ->setStatus(Plenty_Core_Model_Profile_Schedule::STATUS_PENDING);

        if (!$schedule->trySchedule($scheduledAt)) {
            return false;
        }

        try {
            $schedule->save();
        } catch (Exception $e) {
            Mage::throwException($e->getMessage());
        }

        return $schedule; */
    }

    /**
     * Retrieve store filter for profile
     *
     * @param Profile $profile
     * @return mixed
     */
    public function getStoreFilter(Profile $profile)
    {
        return $profile->getData(self::CACHE_KEY_STORE_FILTER);
    }

    /**
     * Set store filter for profile
     *
     * @param Profile $profile
     * @param $store
     * @return $this
     */
    public function setStoreFilter(Profile $profile, $store)
    {
        $profile->setData(self::CACHE_KEY_STORE_FILTER, $store);
        return $this;
    }

    /**
     * Setting specified profile type variables
     *
     * @param array $config
     * @return $this
     */
    public function setConfig($config)
    {
        $this->_config = $config;
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsScheduledEvent()
    {
        return $this->getData(self::CONFIG_IS_SCHEDULED_EVENT);
    }

    /**
     * @param $bool
     * @return $this
     */
    public function setIsScheduledEvent($bool)
    {
        $this->setData(self::CONFIG_IS_SCHEDULED_EVENT, $bool);
        return $this;
    }
}

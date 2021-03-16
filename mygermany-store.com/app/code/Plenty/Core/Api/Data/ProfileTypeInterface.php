<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Api\Data;

use Plenty\Core\Api\Data\Profile\HistoryInterface;
use Plenty\Core\Model\Profile;
use Magento\Framework\Data\Collection;

/**
 * Interface ProfileTypeInterface
 * @package Plenty\Core\Api\Data
 */
interface ProfileTypeInterface
{
    const MAGE_WEBSITE_CODE = 'mage_website_code';
    const MAGE_WEBSITE_ID = 'mage_website_id';
    const MAGE_STORE_CODE = 'mage_store_code';
    const MAGE_STORE_ID = 'mage_store_id';
    const IS_DEFAULT_STORE = 'is_default_store';
    const PLENTY_STORE = 'plenty_store';
    const MAGE_STORE = 'mage_store';

    const CACHE_KEY_STORE_FILTER = '_cache_instance_store_filter';

    // custom metadata
    const CONFIG_IS_SCHEDULED_EVENT = 'is_scheduled_event';
    const CONFIG_API_BEHAVIOUR = 'api_behaviour';
    const CONFIG_API_COLLECTION_SIZE = 'api_collection_size';
    const CONFIG_DEFAULT_LANG = 'default_lang';
    const CONFIG_IMPORT_FILTER = 'import_filter';
    const CONFIG_COLLECT_FILTER = 'collect_filter';

    /**
     * @return $this
     */
    public function execute();

    /**
     * @return array
     */
    public function getErrors();

    /**
     * @return array
     */
    public function getMessages();

    /**
     * @return array
     */
    public function getResponse();

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function addResponse($data, $key = null);

    /**
     * @param string|array $data
     * @param null|string $key
     * @return $this
     */
    public function setResponse($data, $key = null);

    /**
     * @return HistoryInterface
     */
    public function getHistory() : HistoryInterface;

    /**
     * @param HistoryInterface $history
     * @return $this
     */
    public function setHistory(HistoryInterface $history);

    /**
     * @param $actionCode
     * @param $status
     * @return $this
     */
    public function createHistory($actionCode, $status);

    /**
     * @return $this
     */
    public function handleHistory();

    /**
     * @return Profile
     */
    public function getProfile() : Profile;

    /**
     * @return array
     */
    public function getProcessStages();

    /**
     * @return bool
     */
    public function getIsScheduledEvent();

    /**
     * @param $bool
     */
    public function setIsScheduledEvent($bool);

    /**
     * @return string
     */
    public function getApiBehaviour();

    /**
     * @param $behaviour
     * @return $this
     */
    public function setApiBehaviour($behaviour);

    /**
     * @return int
     */
    public function getApiCollectionSize();

    /**
     * @param $size
     * @return $this
     */
    public function setApiCollectionSize($size);

    /**
     * @return Collection
     */
    public function getStoreMapping() : Collection;

    /**
     * @param array $data
     * @return $this
     */
    public function setStoreMapping(array $data);

    /**
     * @return bool|string
     */
    public function getDefaultLang();

    /**
     * @param $lang
     * @return $this
     */
    public function setDefaultLang($lang);
}

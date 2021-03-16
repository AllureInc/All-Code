<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Export\Entity;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;

use Plenty\Core\Model\Profile;
use Plenty\Item\Api\Data\Profile\CategoryExportInterface;
use Plenty\Item\Api\CategoryCollectManagementInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Category
 * @package Plenty\Item\Profile\Export\Entity
 */
class Category extends Profile\Type\AbstractType
    implements CategoryExportInterface
{
    /**
     * @var CategoryCollectManagementInterface
     */
    private $_categoryCollectManagement;

    /**
     * @var Collection
     */
    private $_storeMappingCollection;

    /**
     * @var CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var array
     */
    private $_exportResponse;

    /**
     * Category constructor.
     * @param CategoryCollectManagementInterface $categoryCollectManagement
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Profile\HistoryFactory $historyFactory
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        CategoryCollectManagementInterface $categoryCollectManagement,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Profile\HistoryFactory $historyFactory,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {

        $this->_categoryCollectManagement = $categoryCollectManagement;
        parent::__construct($eventManager, $storeManager, $historyFactory, $dateTime, $serializer, $data);
    }

    /**
     * @return $this|mixed
     * @throws \Exception
     */
    public function execute()
    {
        $this->setIsScheduledEvent(true)
            ->_resetResponseData()
            ->_initHistory();

        if (!$this->getHistory()->getId()) {
            throw new \Exception(__('Cannot find available profile task. [Profile: %1]', $this->getProfile()->getId()));
        }

        try {
            switch ($this->getHistory()->getActionCode()) {
                case self::STAGE_COLLECT_CATEGORY :
                    $this->collectCategories();
                    break;
                case self::STAGE_EXPORT_CATEGORY :
                    $this->exportProducts();
                    break;
                default : break;
            }
        } catch (\Exception $e) {
            $this->addErrors($e->getMessage());
        }

        $this->handleHistory();
        $this->_handleProfileSchedule();

        return $this;
    }

    /**
     * @return array
     */
    public function getProcessStages()
    {
        $stages = [self::STAGE_COLLECT_CATEGORY];
        if ($this->getIsActiveCategoryExport()) {
            array_push($stages, self::STAGE_EXPORT_CATEGORY);
        }

        return $stages;
    }

    /**
     * @return $this|ProductExportInterface
     * @throws \Exception
     */
    public function collectCategories()
    {
        $withFilter = 'details,clients';
        $lastUpdatedAt = null;
        if ($this->getApiBehaviour() === Profile\Config\Source\Behaviour::APPEND) {
            $lastUpdatedAt = $this->_categoryImportRepository->getLastUpdatedAt($this->getProfile()->getId());
        }

        $this->_categoryCollectManagement->setProfile($this->getProfile())
            ->setDefaultStoreId($this->getDefaultStoreMapping()->getData('mage_store_id'))
            ->setRootCategoryMapping($this->getRootCategoryMapping())
            ->execute(null, $lastUpdatedAt, $withFilter);

        if ($response = $this->_categoryCollectManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Categories have been collected.'), Status::SUCCESS);
        }

        return $this;
    }


    /**
     * @return string
     */
    public function getApiBehaviour()
    {
        return $this->_getConfig(self::API_BEHAVIOUR);
    }

    /**
     * @param $behaviour
     * @return $this
     */
    public function setApiBehaviour($behaviour)
    {
        $this->setData(self::CONFIG_API_BEHAVIOUR, $behaviour);
        return $this;
    }

    /**
     * @return int
     */
    public function getApiCollectionSize()
    {
        return $this->_getConfig(self::API_COLLECTION_SIZE);
    }

    /**
     * @param $size
     * @return $this
     */
    public function setApiCollectionSize($size)
    {
        $this->setData(self::CONFIG_API_COLLECTION_SIZE, $size);
        return $this;
    }

    /**
     * @return Collection
     * @throws \Exception
     */
    public function getStoreMapping() : Collection
    {
        if ($this->_storeMappingCollection) {
            return $this->_storeMappingCollection;
        }

        $this->_storeMappingCollection = $this->_dataCollectionFactory->create();
        if (!$values = $this->_getConfig(self::STORE_MAPPING)) {
            return $this->_storeMappingCollection;
        }

        $values = $this->_serializer->unserialize($values);
        if (isset($values['__empty'])) {
            unset($values['__empty']);
        }

        foreach ($values as $value) {
            if (!isset($value['mage_store']) || !isset($value['plenty_store'])) {
                continue;
            }

            $store = $this->_storeManager->getStore($value['mage_store']);
            $website = $this->_storeManager->getWebsite($store->getWebsiteId());

            $storeMapping = new DataObject(
                [
                    self::MAGE_WEBSITE_CODE => $website->getCode(),
                    self::MAGE_WEBSITE_ID => $website->getId(),
                    self::MAGE_STORE_CODE => $store->getCode(),
                    self::MAGE_STORE_ID =>  $store->getId(),
                    self::PLENTY_STORE => $value['plenty_store'],
                    self::IS_DEFAULT_STORE    => isset($value['is_default'])
                        ? $value['is_default']
                        : false
                ]
            );
            $this->_storeMappingCollection->addItem($storeMapping);
        }

        return $this->_storeMappingCollection;
    }

    /**
     * @param array $data
     * @return mixed|void
     */
    public function setStoreMapping(array $data)
    {
        // TODO: Implement setStoreMapping() method.
    }

    /**
     * @return DataObject
     * @throws \Exception
     */
    public function getDefaultStoreMapping() : DataObject
    {
        $mainStore = null;
        foreach ($this->getStoreMapping() as $store) {
            if ($mainStore = $store->getData(self::IS_DEFAULT_STORE)) {
                $mainStore = $store;
                break;
            }
        }

        if (null === $mainStore) {
            throw new \Exception(__('Default store is not set. [Trace: %1]', __METHOD__));
        }

        return $mainStore;
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getDefaultLang()
    {
        if (!$defaultStore = $this->getDefaultStoreMapping()) {
            return $defaultStore;
        }

        return $defaultStore->getData(self::PLENTY_STORE);
    }

    /**
     * @param $lang
     * @return $this|mixed
     */
    public function setDefaultLang($lang)
    {
        $this->setData(self::CONFIG_DEFAULT_LANG, $lang);
        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActiveCategoryExport()
    {
        return $this->_getConfig(self::IS_ACTIVE_CATEGORY_EXPORT);
    }

    /**
     * @return array
     */
    public function getRootCategoryMapping()
    {
        $rootCategories = [];
        if (!$values = $this->_getConfig(self::ROOT_CATEGORY_MAPPING)) {
            return $rootCategories;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_category']) || !isset($value['plenty_category'])) {
                continue;
            }
            $rootCategories[$value['mage_category']] = $value['plenty_category'];
        }

        return $rootCategories;
    }
}

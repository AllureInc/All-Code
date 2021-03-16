<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Profile\Import\Entity;

use Magento\Framework\Data\Collection;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

use Plenty\Core\Model\Profile;
use Plenty\Core\Model\Profile\Type\AbstractType;
use Plenty\Core\Model\Source\Status;
use Plenty\Stock\Api\StockImportRepositoryInterface;
use Plenty\Stock\Api\StockCollectManagementInterface;
use Plenty\Stock\Api\StockImportManagementInterface;
use Plenty\Stock\Api\Data\Profile\StockImportInterface;

/**
 * Class Inventory
 * @package Plenty\Stock\Profile\Import\Entity
 */
class Inventory extends AbstractType
    implements StockImportInterface
{
    /**
     * @var StockImportRepositoryInterface
     */
    private $_stockRepository;

    /**
     * @var StockCollectManagementInterface
     */
    private $_stockCollectManagement;

    /**
     * @var StockImportManagementInterface
     */
    private $_stockImportManagement;

    /**
     * Inventory constructor.
     * @param StockImportRepositoryInterface $stockImportRepository
     * @param StockCollectManagementInterface $stockCollectManagement
     * @param StockImportManagementInterface $stockImportManagement
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Profile\HistoryFactory $historyFactory
     * @param DateTime $dateTime
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        StockImportRepositoryInterface $stockImportRepository,
        StockCollectManagementInterface $stockCollectManagement,
        StockImportManagementInterface $stockImportManagement,
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Profile\HistoryFactory $historyFactory,
        DateTime $dateTime,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_stockRepository = $stockImportRepository;
        $this->_stockCollectManagement = $stockCollectManagement;
        $this->_stockImportManagement = $stockImportManagement;
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
            throw new \Exception(__('Cannot find available schedule task. [Profile: %1]',
                $this->getProfile()->getId()));
        }

        try {
            switch ($this->getHistory()->getActionCode()) {
                case self::STAGE_COLLECT_STOCK :
                    $this->collectStock();
                    break;
                case self::STAGE_IMPORT_STOCK :
                    $this->importStock();
                    break;
                default : break;
            }
        } catch (\Exception $e) {
            $this->addResponse($e->getMessage(), Status::ERROR);
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
        return [
            self::STAGE_COLLECT_STOCK,
            self::STAGE_IMPORT_STOCK
        ];
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function collectStock() {

        $lastUpdatedAt = null;
        if ($this->getApiBehaviour() === Profile\Config\Source\Behaviour::APPEND) {
            $lastUpdatedAt = $this->_stockRepository->getLastUpdatedAt($this->getProfile()->getId());
        }

        $this->_stockCollectManagement->setProfileEntity($this);
        if ($variationIds = $this->getCollectSearchCriteria()) {
            foreach ($variationIds as $variationId) {
                $this->_stockCollectManagement->execute($variationId, $this->getMainWarehouseId(), null);

                if ($response = $this->_stockCollectManagement->getResponse()) {
                    $this->addResponse(current($response), key($this->_stockCollectManagement->getResponse()));
                } else {
                    $this->addResponse(__('Inventory has been collected.'), Status::SUCCESS);
                }
            }

            return $this;
        }

        $this->_stockCollectManagement->execute(null, $this->getMainWarehouseId(), $lastUpdatedAt);

        if ($response = $this->_stockCollectManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Inventory has been collected.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function importStock()
    {
        if (!$this->getIsActiveStockImport()) {
            $this->addResponse(__('Stock import is disabled.'), Status::SUCCESS);
            return $this;
        }

        $this->_stockImportManagement->setProfileEntity($this)
            ->execute();

        if ($response = $this->_stockImportManagement->getResponse()) {
            $this->setResponse($response);
        } else {
            $this->addResponse(__('Inventory has been imported.'), Status::SUCCESS);
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActiveStockImport()
    {
        return $this->_getConfig(self::XML_PATH_IS_ACTIVE_STOCK_IMPORT);
    }

    /**
     * @return int
     */
    public function getImportBatchSize()
    {
        return $this->_getConfig(self::XML_PATH_IMPORT_BATCH_SIZE);
    }

    /**
     * @return bool
     */
    public function getIsActiveReindexAfter()
    {
        return $this->_getConfig(self::XML_PATH_REINDEX_AFTER);
    }

    /**
     * @return string
     */
    public function getApiBehaviour()
    {
        if ($this->hasData(self::CONFIG_API_BEHAVIOUR)) {
            return $this->getData(self::CONFIG_API_BEHAVIOUR);
        }
        return $this->_getConfig(self::XML_PATH_API_BEHAVIOUR);
    }

    /**
     * @param $behaviour
     * @return $this|mixed
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
        return $this->_getConfig(self::XML_PATH_API_COLLECTION_SIZE);
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

    public function getStoreMapping(): Collection
    {
        // TODO: Implement getStoreMapping() method.
    }

    public function setStoreMapping(array $data)
    {
        // TODO: Implement setStoreMapping() method.
    }

    /**
     * @return DataObject
     * @throws \Exception
     */
    public function getDefaultStoreMapping()
    {
        // TODO: Implement getDefaultStoreMapping() method.
    }

    /**
     * @return bool|string
     * @throws \Exception
     */
    public function getDefaultLang()
    {
        // TODO: Implement getDefaultLang() method.
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
     * @param null $storeId
     * @return int
     */
    public function getMainWarehouseId($storeId = null)
    {
        return $this->_getConfig(self::XML_PATH_MAIN_WAREHOUSE_ID, $storeId);
    }

    /**
     * @return null|array
     */
    public function getCollectSearchCriteria()
    {
        return $this->getData(self::CONFIG_COLLECT_SEARCH_CRITERIA);
    }

    /**
     * @return null|array
     */
    public function getCollectSearchCriteriaAllowedFields()
    {
        return ['variation_id'];
    }

    /**
     * @param array $searchCriteria
     * @return $this
     */
    public function setCollectSearchCriteria(array $searchCriteria)
    {
        $this->setData(self::CONFIG_COLLECT_SEARCH_CRITERIA, $searchCriteria);
        return $this;
    }

    /**
     * @return SearchCriteriaInterface|null
     */
    public function getImportSearchCriteria()
    {
        return $this->getData(self::CONFIG_IMPORT_SEARCH_CRITERIA);
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return $this
     */
    public function setImportSearchCriteria(SearchCriteriaInterface $searchCriteria)
    {
        $this->setData(self::CONFIG_IMPORT_SEARCH_CRITERIA, $searchCriteria);
        return $this;
    }
}
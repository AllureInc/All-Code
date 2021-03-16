<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Profile\Import\Entity;

use Magento\Framework\DataObject;
use Magento\Framework\Data\Collection;
use Magento\Framework\Data\CollectionFactory;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Sales\Model\ResourceModel\Order\Collection as SalesOrderCollection;
use Magento\Sales\Api\OrderRepositoryInterface as SalesOrderRepositoryInterface;
use Magento\Sales\Model\Order as SalesOrder;

use Plenty\Core\Model\Profile;
use Plenty\Order\Api\Data\Profile\OrderImportInterface;
use Plenty\Order\Api\OrderExportManagementInterface;
use Plenty\Order\Api\Data\Export\OrderInterface;
use Plenty\Core\Model\Source\Status;

/**
 * Class Order
 * @package Plenty\Order\Profile\Import\Entity
 */
class Order extends Profile\Type\AbstractType
    implements OrderImportInterface
{
    /**
     * @var SalesOrderRepositoryInterface
     */
    private $_salesOrderRepository;

    /**
     * @var OrderExportManagementInterface
     */
    private $_orderExportManagement;

    /**
     * @var SearchCriteriaBuilder
     */
    private $_searchCriteriaBuilder;


    private $_filterBuilder;

    /**
     * @var Collection
     */
    private $_storeMappingCollection;

    /**
     * @var \Magento\Framework\Data\CollectionFactory
     */
    protected $_dataCollectionFactory;

    /**
     * @var array
     */
    private $_exportResponse;

    /**
     * Order constructor.
     * @param ManagerInterface $eventManager
     * @param StoreManagerInterface $storeManager
     * @param Profile\HistoryFactory $historyFactory
     * @param DateTime $dateTime
     * @param CollectionFactory $dataCollectionFactory
     * @param SalesOrderRepositoryInterface $salesOrderRepository
     * @param OrderExportManagementInterface $orderExportManagement
     * @param FilterBuilder $filterBuilder
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param Json|null $serializer
     */
    public function __construct(
        ManagerInterface $eventManager,
        StoreManagerInterface $storeManager,
        Profile\HistoryFactory $historyFactory,
        DateTime $dateTime,
        CollectionFactory $dataCollectionFactory,
        SalesOrderRepositoryInterface $salesOrderRepository,
        OrderExportManagementInterface $orderExportManagement,
        FilterBuilder $filterBuilder,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        ?Json $serializer = null
    ) {
        $this->_dataCollectionFactory = $dataCollectionFactory;
        $this->_salesOrderRepository = $salesOrderRepository;
        $this->_orderExportManagement = $orderExportManagement;
        $this->_searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->_filterBuilder = $filterBuilder;
        parent::__construct($eventManager, $storeManager, $historyFactory, $dateTime, $serializer);
    }


    /** @todo implement order import */
    public function execute()
    {
        return $this;
    }

    /**
     * @return array
     */
    public function getProcessStages()
    {
        $stages = [self::STAGE_IMPORT_ORDER];
        return $stages;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function importOrders()
    {
        $this->addMessages(__('Order import is not implemented yet.'));

        return $this;
        if (!$this->getIsActiveOrderImport()) {
            $this->_messages[] = __('Order import is disabled.');
            return $this;
        }

        $collectionSize = $this->getImportBatchSize() ?? self::DEFAULT_IMPORT_BATCH_SIZE ;

        $searchCriteria = $this->_searchCriteriaBuilder
            ->addFilter(OrderInterface::PLENTY_ORDER_STATUS, Status::PENDING, 'eq')
            ->setPageSize((int) $collectionSize)
            ->create();

        /** @var SalesOrderCollection $salesOrderCollection */
        $salesOrderCollection = $this->_salesOrderRepository->getList($searchCriteria);

        if (!$salesOrderCollection->getSize()) {
            $this->_messages[] = __('Orders are up to date.');
            return $this;
        }

        /** @var SalesOrder $salesOrder */
        foreach ($salesOrderCollection as $salesOrder) {
            try {
                $this->_orderExportManagement->setProfileEntity($this);
                $this->_orderExportManagement->execute($salesOrder);
            } catch (\Exception $e) {
                $this->_errors[] = $e->getMessage();
                continue;
            }
        }

        return $this;
    }

    /**
     * @return bool
     */
    public function getIsActiveOrderImport()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_ORDER_IMPORT);
    }

    /**
     * @return int
     */
    public function getImportBatchSize()
    {
        return $this->_getConfig(self::DEFAULT_IMPORT_BATCH_SIZE);
    }

    /**
     * @return string
     */
    public function getApiBehaviour()
    {
        return $this->_getConfig(self::XML_PATH_API_BEHAVIOUR);
    }

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

    /**
     * @return string|null
     */
    public function getApiOrderSearchFilters()
    {
        return $this->_getConfig(self::XML_PATH_API_ORDER_SEARCH_FILTERS);
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
        if (!$values = $this->_getConfig(self::XML_PATH_STORE_MAPPING)) {
            return $this->_storeMappingCollection;
        }

        $values = $this->_serializer->unserialize($values);
        if (isset($values['__empty'])) {
            unset($values['__empty']);
        }

        foreach ($values as $value) {
            if (!isset($value[self::MAGE_STORE]) || !isset($value[self::PLENTY_STORE])) {
                continue;
            }

            $store = $this->_storeManager->getStore($value[self::MAGE_STORE]);
            $website = $this->_storeManager->getWebsite($store->getWebsiteId());

            $storeMapping = new DataObject(
                [
                    self::MAGE_WEBSITE_CODE => $website->getCode(),
                    self::MAGE_WEBSITE_ID => $website->getId(),
                    self::MAGE_STORE_CODE => $store->getCode(),
                    self::MAGE_STORE_ID =>  $store->getId(),
                    self::PLENTY_STORE => $value[self::PLENTY_STORE],
                    self::IS_DEFAULT_STORE    => isset($value['is_default'])
                        ? $value['is_default']
                        : false
                ]
            );
            $this->_storeMappingCollection->addItem($storeMapping);
        }

        return $this->_storeMappingCollection;
    }

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
        $mainStore = $this->getStoreMapping()
            ->addFilter(self::IS_DEFAULT_STORE, 1);

        if (!$mainStore->getSize()) {
            throw new \Exception(__('Main store is not set. [Profile: %1]',
                $this->getProfile()->getId()));
        }

        return $mainStore->getFirstItem();
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
     * @param null $storeId
     * @return int|null
     */
    public function getOrderReferrerId($storeId = null)
    {
        return $this->_getConfig(self::XML_PATH_ORDER_REFERER_ID, $storeId);
    }

    /**
     * @return array
     */
    public function getOrderStatusFilter()
    {
        if (!$statusFilter = $this->_getConfig(self::XML_PATH_STATUS_FILTER)) {
            return [];
        }
        return explode(',', $statusFilter);
    }

    /**
     * @return array
     */
    public function getOrderStatusMapping()
    {
        $statusMapping = [];
        if (!$values = $this->_getConfig(self::XML_PATH_STATUS_MAPPING)) {
            return $statusMapping;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_status']) || !isset($value['plenty_status'])) {
                continue;
            }
            $statusMapping[$value['mage_status']] = $value['plenty_status'];
        }

        return $statusMapping;
    }

    /**
     * @param $salesOrderStatus
     * @return int|null
     */
    public function getPlentyStatusIdByOrderStatusCode($salesOrderStatus)
    {
        $statusMapping = $this->getOrderStatusMapping();
        return isset($statusMapping[$salesOrderStatus])
            ? $statusMapping[$salesOrderStatus]
            : null;
    }

    /**
     * @return bool
     */
    public function getIsActiveImportPayment()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_PAYMENT_IMPORT);
    }

    /**
     * @return array
     */
    public function getPaymentMapping()
    {
        $paymentMapping = [];
        if (!$values = $this->_getConfig(self::XML_PATH_PAYMENT_METHOD_MAPPING)) {
            return $paymentMapping;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_payment']) || !isset($value['plenty_payment'])) {
                continue;
            }
            $paymentMapping[$value['mage_payment']] = $value['plenty_payment'];
        }

        return $paymentMapping;
    }

    /**
     * @param $paymentMethodCode
     * @return int|null
     */
    public function getMopIdByOrderPaymentMethodCode($paymentMethodCode)
    {
        $paymentMapping = $this->getPaymentMapping();
        return isset($paymentMapping[$paymentMethodCode])
            ? $paymentMapping[$paymentMethodCode]
            : null;
    }

    /**
     * @return bool
     */
    public function getIsActiveShipmentImport()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_SHIPPING_IMPORT);
    }

    /**
     * @return int|null
     */
    public function getDefaultShippingProfileId($store = null)
    {
        return $this->_getConfig(self::XML_PATH_DEFAULT_SHIPPING_PROFILE, $store);
    }

    /**
     * @return array
     */
    public function getShippingMapping()
    {
        $shippingMapping = [];
        if (!$values = $this->_getConfig(self::XML_PATH_SHIPPING_MAPPING)) {
            return $shippingMapping;
        }

        $values = $this->_serializer->unserialize($values);
        foreach ($values as $value) {
            if (!isset($value['mage_shipping']) || !isset($value['plenty_shipping'])) {
                continue;
            }
            $shippingMapping[$value['mage_shipping']] = $value['plenty_shipping'];
        }

        return $shippingMapping;
    }

    /**
     * @param $method
     * @param null $store
     * @return int|null
     */
    public function getShippingProfileId($method, $store = null)
    {
        $shippingProfileId = $this->getDefaultShippingProfileId($store);
        if (!$shippingProfileMapping = $this->getShippingMapping()) {
            return $shippingProfileId;
        }

        foreach ($shippingProfileMapping as $shippingProfile) {
            if (fnmatch($shippingProfile['mage_shipping'], $method)) {
                $shippingProfileId = $shippingProfile['plenty_shipping'];
                break;
            }
        }

        return $shippingProfileId;
    }

    /**
     * @return bool
     */
    public function getIsActiveCustomerImport()
    {
        return $this->_getConfig(self::XML_PATH_ENABLE_CUSTOMER_IMPORT);
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
     * @return bool
     * @throws \Exception
     */
    public function getIsMultiStore()
    {
        return $this->getStoreMapping()->getSize() > 1;
    }

    private function _initResponseData()
    {
        $this->_messages =
        $this->_errors = [];
    }
}


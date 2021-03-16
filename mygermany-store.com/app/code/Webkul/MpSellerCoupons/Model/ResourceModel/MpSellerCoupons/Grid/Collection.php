<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpSellerCoupons
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons\Grid;

use Magento\Framework\Api\Search\SearchResultInterface as ApiSearchResultInterface;
use Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons\Collection as MpSellerCouponsCollection;
use Magento\Framework\Search\AggregationInterface as SearchAggregationInterface;
use Magento\Framework\Data\Collection\EntityFactoryInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Data\Collection\Db\FetchStrategyInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\Event\ManagerInterface as EventManagerInterface;
use Magento\Framework\Model\ResourceModel\Db\AbstractDb as ResourceModelAbstractDb;

/**
 * Webkul\MpSellerCoupons\Model\ResourceModel\MpSellerCoupons\Grid\Collection Class
 * Collection for displaying grid of MpSellerCoupons MpSellerCoupons.
 */
class Collection extends MpSellerCouponsCollection implements ApiSearchResultInterface
{
    /**
     * @var SearchAggregationInterface
     */
    protected $aggregations;

    /**
     * @param EntityFactoryInterface                               $entityFactoryInterface
     * @param LoggerInterface                                      $loggerInterface
     * @param FetchStrategyInterface                               $fetchStrategyInterface
     * @param EventManagerInterface                                $eventManagerInterface
     * @param StoreManagerInterface                                $storeManagerInterface
     * @param mixed|null                                           $mainTable
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb $eventPrefix
     * @param mixed                                                $eventObject
     * @param mixed                                                $resourceModel
     * @param string                                               $model
     * @param null                                                 $connection
     * @param ResourceModelAbstractDb|null                         $resource
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        EntityFactoryInterface $entityFactoryInterface,
        LoggerInterface $loggerInterface,
        FetchStrategyInterface $fetchStrategyInterface,
        EventManagerInterface $eventManagerInterface,
        StoreManagerInterface $storeManagerInterface,
        $mainTable,
        $eventPrefix,
        $eventObject,
        $resourceModel,
        $model = 'Magento\Framework\View\Element\UiComponent\DataProvider\Document',
        $connection = null,
        ResourceModelAbstractDb $resource = null
    ) {
    
        parent::__construct(
            $entityFactoryInterface,
            $loggerInterface,
            $fetchStrategyInterface,
            $eventManagerInterface,
            $storeManagerInterface,
            $connection,
            $resource
        );
        $this->_eventPrefix = $eventPrefix;
        $this->_eventObject = $eventObject;
        $this->_init($model, $resourceModel);
        $this->setMainTable($mainTable);
    }

    /**
     * @return SearchAggregationInterface
     */
    public function getAggregations()
    {
        return $this->aggregations;
    }

    /**
     * @param SearchAggregationInterface $aggregationsData
     *
     * @return $this
     */
    public function setAggregations($aggregationsData)
    {
        $this->aggregations = $aggregationsData;
    }

    /**
     * Retrieve all ids for collection
     * Backward compatibility with EAV collection.
     *
     * @param int $limit
     * @param int $offset
     *
     * @return array
     */
    public function getAllIds($limit = null, $offset = null)
    {
        return $this->getConnection()->fetchCol(
            $this->_getAllIdsSelect($limit, $offset),
            $this->_bindParams
        );
    }

    /**
     * Get search criteria.
     *
     * @return \Magento\Framework\Api\SearchCriteriaInterface|null
     */
    public function getSearchCriteria()
    {
        return;
    }

    /**
     * Set search criteria.
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setSearchCriteria(
        \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria = null
    ) {
    
        return $this;
    }

    /**
     * Get total count.
     *
     * @return int
     */
    public function getTotalCount()
    {
        return $this->getSize();
    }

    /**
     * Set total count.
     *
     * @param int $totalCount
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setTotalCount($totalCount)
    {
        return $this;
    }

    /**
     * Set items list.
     *
     * @param \Magento\Framework\Api\ExtensibleDataInterface[] $items
     *
     * @return $this
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function setItems(array $items = null)
    {
        return $this;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $timezone = $objectManager->create('\Magento\Framework\Stdlib\DateTime\TimezoneInterface');
        $currDate = $timezone->date(new \DateTime(date("Y/m/d h:i:sa")))
                                                       ->format('Y/m/d h:i:s');
        $joinTable = $this->getTable('customer_grid_flat');
        $salesOrderTable = $this->getTable('sales_order');
        $originalQuery = clone $this;
        $this->getSelect()->joinleft(
            $salesOrderTable.' as so',
            'main_table.order_id = so.entity_id',
            [
                'increment_id'=>'increment_id'
            ]
        );
        $this->getSelect()->join(
            $joinTable.' as cgf',
            'main_table.seller_id = cgf.entity_id',
            [
                'name'=>'name'
            ]
        );
        $query = $originalQuery->getSelect()
                        ->reset('where')
                        ->reset('columns')
                        ->columns("main_table.entity_id")
                        ->columns("IF(main_table.expire_at<'".$currDate."' && main_table.status!='used', 'expired', main_table.status) AS custom_status");

        $query->__toString();
        $this->getSelect()->from($query)->where("t.entity_id = main_table.entity_id");
        parent::_renderFiltersBefore();
    }
}

<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\ResourceModel\Choice;

use Scommerce\CookiePopup\Api\Data\ChoiceInterface;
use Scommerce\CookiePopup\Api\Data\LinkInterface;
use Magento\Store\Model\Store;
use Magento\Cms\Model\ResourceModel\AbstractCollection;

/**
 * Class Collection
 * @package Scommerce\CookiePopup\Model\ResourceModel\Choice
 */
class Collection extends \Magento\Cms\Model\ResourceModel\AbstractCollection
{
    const LINK_ALIAS = 'link'; // Alias for linked Link table in sql

    /** @var string Identifier field name for collection items. Can be used by collections with items without defined */
    protected $_idFieldName = ChoiceInterface::CHOICE_ID;

    /** @var string Name prefix of events that are dispatched by model */
    protected $_eventPrefix = 'scommerce_cookie_popup_choice_collection';

    /** @var string Name of event parameter */
    protected $_eventObject = 'scommerce_cookie_popup_choice_collection';

    /** @var \Magento\Store\Model\StoreManagerInterface */
    protected $storeManager;

    /** @var bool Flag is link table was already linked */
    private $linked = false;

    /**
     * @param \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory
     * @param \Psr\Log\LoggerInterface $logger
     * @param \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param \Magento\Framework\DB\Adapter\AdapterInterface|null $connection
     * @param \Magento\Framework\Model\ResourceModel\Db\AbstractDb|null $resource
     */
    public function __construct(
        \Magento\Framework\Data\Collection\EntityFactoryInterface $entityFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Data\Collection\Db\FetchStrategyInterface $fetchStrategy,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        \Magento\Framework\EntityManager\MetadataPool $metadata,
        \Magento\Framework\DB\Adapter\AdapterInterface $connection = null,
        \Magento\Framework\Model\ResourceModel\Db\AbstractDb $resource = null
    ) {
        $this->storeManager = $storeManager;
        parent::__construct($entityFactory, $logger, $fetchStrategy, $eventManager, $storeManager, $metadata, $connection, $resource);
    }

    /**
     * @inheritdoc
     */
    protected function _construct()
    {
        $this->_init('Scommerce\CookiePopup\Model\Data\Choice', 'Scommerce\CookiePopup\Model\ResourceModel\Choice');
        $this->_map['fields']['store'] = 'store_table.store_id';
    }

    /**
     * Perform operations after collection load
     *
     * @return $this
     */
    protected function _afterLoad()
    {
        $this->performStoreLinkSave();

        $this->performAfterLoad(ChoiceInterface::TABLE_STORE, ChoiceInterface::CHOICE_ID);

        return parent::_afterLoad();
    }

    /**
     * Add filter by customer id
     *
     * @param int $id
     * @return $this
     */
    public function addCustomerFilter($id)
    {
        $this->link();
        $this->getSelect()->where(sprintf('%s.%s = %s', self::LINK_ALIAS, LinkInterface::CUSTOMER_ID, (int) $id));
        return $this;
    }

    /**
     * Add filter by store
     *
     * @param null|string|bool|int|Store $store
     * @return $this
     */
    public function addStoreFilter($store, $withAdmin = true)
    {
        $this->performAddStoreFilter($store, $withAdmin);

        return $this;
    }
    /*
    public function addStoreFilter($store = null, $withAdmin = true)
    {
        $this->link();
        $store = $this->storeManager->getStore($store);
        if ($store->getId() != Store::DEFAULT_STORE_ID) {
            $this->getSelect()->where(sprintf('%s.%s = %s', self::LINK_ALIAS, LinkInterface::STORE_ID, (int)$store->getId()));
        }
        return $this;
    }
    */

    /**
     * Add filter by status
     *
     * @param int $value
     * @return $this
     */
    public function addStatusFilter($value = 0)
    {
        $value = $value ? 1 : 0;
        $this->link();
        $this->getSelect()->where(sprintf('%s.%s = %s', self::LINK_ALIAS, LinkInterface::STATUS, $value));
        return $this;
    }

    /**
     * Add filter by accepted status
     *
     * @return $this
     */
    public function addStatusAcceptedFilter()
    {
        return $this->addStatusFilter(1);
    }

    /**
     * Add filter by declined filter
     *
     * @return $this
     */
    public function addStatusDeclinedFilter()
    {
        return $this->addStatusFilter(0);
    }

    /**
     * Add filter by created at
     *
     * @param string $value
     * @return $this
     */
    public function addCreatedFilter($value)
    {
        $this->link();
        $this->getSelect()->where(sprintf('%s.%s = %s', self::LINK_ALIAS, LinkInterface::CREATED_AT, $value));
        return $this;
    }

    /**
     * Add filter by updated at
     *
     * @param string $value
     * @return $this
     */
    public function addUpdatedFilter($value)
    {
        $this->link();
        $this->getSelect()->where(sprintf('%s.%s = %s', self::LINK_ALIAS, LinkInterface::UPDATED_AT, $value));
        return $this;
    }

    /**
     * Join link table if it was not already linked
     *
     * @return bool
     */
    private function link()
    {
        if (! $this->linked) {
            $this->joinLink();
            $this->linked = true;
        }
        return $this->linked;
    }

    /**
     * Join link table to main table
     *
     * @return void
     */
    private function joinLink()
    {
        $this->getSelect()
            ->join(
                [self::LINK_ALIAS => LinkInterface::TABLE],
                sprintf('%s.%s = main_table.%s', self::LINK_ALIAS, LinkInterface::CHOICE_ID, ChoiceInterface::CHOICE_ID),
                '*'
            )
        ;
    }

    /**
     * Join store relation table if there is store filter
     *
     * @return void
     */
    protected function _renderFiltersBefore()
    {
        $this->joinStoreRelationTable(ChoiceInterface::TABLE_STORE, 'choice_id');
    }

    /**
     * Set Store Id from the relation with customer to show in admin
     */
    private function performStoreLinkSave()
    {
        $items = $this->getItems();
        foreach ($items as $item) {
            $item->setData('store_link_id', $item->getStoreId());
        }
    }
}

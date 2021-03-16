<?php

namespace Solrbridge\Search\Framework\Search\Adapter\Mysql;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\DB\Adapter\AdapterInterface;
use Magento\Framework\DB\Ddl\Table;
use Magento\Framework\DB\Select;

class TemporaryStorage extends \Magento\Framework\Search\Adapter\Mysql\TemporaryStorage
{
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    private $resource;

    /**
     * @var DeploymentConfig
     */
    private $config;
    
    protected $registry;

    /**
     * @param \Magento\Framework\App\ResourceConnection $resource
     * @param DeploymentConfig|null $config
     */
    public function __construct(
        \Magento\Framework\App\ResourceConnection $resource,
        DeploymentConfig $config = null,
        \Magento\Framework\Registry $registry
    ) {
        $this->resource = $resource;
        $this->config = $config !== null ? $config : ObjectManager::getInstance()->get(DeploymentConfig::class);
        $this->registry = $registry;
        parent::__construct($resource, $config);
    }
    
    /**
     * Store select results in temporary table.
     *
     * @param Select $select
     * @return Table
     * @throws \Zend_Db_Exception
     */
    public function storeDocumentsFromSelect(Select $select)
    {
        $searchResult = $this->registry->registry('solrbridge_search_result');
        if (!$searchResult) {
            return parent::storeDocumentsFromSelect($select);
        }
        $table = $this->createTemporaryTable();
        return $table;
    }
    
    /**
     * Create temporary table for search select results.
     *
     * @return Table
     * @throws \Zend_Db_Exception
     */
    private function createTemporaryTable()
    {
        $connection = $this->getConnection();
        $tableName = $this->resource->getTableName(str_replace('.', '_', uniqid(self::TEMPORARY_TABLE_PREFIX, true)));
        $table = $connection->newTable($tableName);
        if ($this->config->get('db/connection/indexer/persistent')) {
            $connection->dropTemporaryTable($table->getName());
        }
        $table->addColumn(
            self::FIELD_ENTITY_ID,
            Table::TYPE_INTEGER,
            10,
            ['unsigned' => true, 'nullable' => false, 'primary' => true],
            'Entity ID'
        );
        $table->addColumn(
            self::FIELD_SCORE,
            Table::TYPE_DECIMAL,
            [32, 16],
            ['unsigned' => true, 'nullable' => false],
            'Score'
        );
        $table->setOption('type', 'memory');
        $connection->createTemporaryTable($table);
        return $table;
    }
    
    /**
     * Get connection.
     *
     * @return false|AdapterInterface
     */
    private function getConnection()
    {
        return $this->resource->getConnection();
    }
}
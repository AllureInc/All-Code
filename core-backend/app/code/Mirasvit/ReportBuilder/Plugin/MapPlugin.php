<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-report-builder
 * @version   1.0.11
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\ReportBuilder\Plugin;

use Magento\Framework\App\DeploymentConfig;
use Magento\Framework\ObjectManagerInterface;
use Mirasvit\ReportApi\Api\Config\TableInterface;
use Mirasvit\ReportApi\Api\Config\TypeInterface;
use Mirasvit\ReportApi\Api\SchemaInterface;
use Mirasvit\ReportApi\Api\Service\TableServiceInterface;
use Mirasvit\ReportApi\Config\Entity\Column;
use Mirasvit\ReportApi\Config\Entity\Relation;
use Mirasvit\ReportApi\Config\Entity\Table;
use Mirasvit\ReportApi\Config\Loader\Map;

class MapPlugin
{
    /**
     * @var TableServiceInterface
     */
    private $tableService;

    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var SchemaInterface
     */
    private $schema;

    /**
     * @var DeploymentConfig
     */
    private $deploymentConfig;

    public function __construct(
        TableServiceInterface $tableService,
        SchemaInterface $schema,
        ObjectManagerInterface $objectManager,
        DeploymentConfig $deploymentConfig
    )
    {
        $this->tableService = $tableService;
        $this->schema = $schema;
        $this->objectManager = $objectManager;
        $this->deploymentConfig = $deploymentConfig;
    }

    public function afterLoad(Map $map, $result)
    {
        $tablePrefix = $this->deploymentConfig->get('db/table_prefix');

        // add tables
        foreach ($this->tableService->getTables() as $tableName) {

            //remove table prefix
            $cnt = 0;
            $tableName = str_replace($tablePrefix, '', $tableName, $cnt);

            if (strpos($tableName, 'admin_') === 0
                || strpos($tableName, 'authorization_') === 0
                || strpos($tableName, '_datetime') > 0
                || strpos($tableName, '_decimal') > 0
                || strpos($tableName, '_int') > 0
                || strpos($tableName, '_text') > 0
                || strpos($tableName, '_varchar') > 0
                || strpos($tableName, '_gallery') > 0
                || strpos($tableName, '_tmp') > 0
                || strpos($tableName, '_idx') > 0
                || strpos($tableName, '_index') > 0
                || strpos($tableName, '_replica') > 0
                || strpos($tableName, '_log') > 0
                || strpos($tableName, 'url_rewrite') > 0
                || strpos($tableName, 'fulltext') > 0
                || strpos($tableName, 'aggregated') > 0
                || strpos($tableName, 'sequence') === 0
                || strpos($tableName, 'cache') === 0
                || strpos($tableName, 'eav') === 0
                || strpos($tableName, 'weee') === 0
                || strpos($tableName, '_selection') > 0
                || strpos($tableName, 'alert') > 0
                || strpos($tableName, '_option') > 0
                || strpos($tableName, 'vault') === 0
                || strpos($tableName, 'paypal') === 0
            ) {
                continue;
            }

            if (!$this->schema->hasTable($tableName)) {
                /** @var Table $table */
                $table = $this->objectManager->create(Table::class, [
                    'name'  => $tableName,
                    'label' => $tableName,
                ]);

                $this->schema->addTable($table);
            }

            $table = $this->schema->getTable($tableName);
            $this->initColumns($table);
        }

        // add relations
        foreach ($this->schema->getTables() as $table) {
            $foreignKeys = $this->tableService->getForeignKeys($table);
            foreach ($foreignKeys as $fk) {
                if (!$this->schema->hasTable($fk['TABLE_NAME'])
                    || !$this->schema->hasTable($fk['REF_TABLE_NAME'])) {
                    continue;
                }

                $leftTable = $this->schema->getTable($fk['TABLE_NAME']);
                $rightTable = $this->schema->getTable($fk['REF_TABLE_NAME']);
                $data = [
                    'leftTable'  => $leftTable,
                    'leftField'  => $leftTable->getField($fk['COLUMN_NAME']),
                    'rightTable' => $rightTable,
                    'rightField' => $rightTable->getField($fk['REF_COLUMN_NAME']),
                    'type'       => '1' . 'n',
                ];

                $relation = $this->objectManager->create(Relation::class, $data);

                $this->schema->addRelation($relation);
            }
        }

        return $result;
    }

    private function initColumns(TableInterface $table)
    {
        // add columns
        $description = $this->tableService->describeTable($table);
        $foreignKeys = $this->tableService->getForeignKeys($table);

        foreach ($description as $info) {
            if ($info['IDENTITY']) {
                $type = 'pk';
            } else {
                $isFk = false;
                foreach ($foreignKeys as $key) {
                    if ($key['COLUMN_NAME'] == $info['COLUMN_NAME']) {
                        $isFk = true;
                    }
                }

                if ($isFk) {
                    $type = 'fk';
                } else {
                    if ($info['DATA_TYPE'] == 'varchar'
                        || $info['DATA_TYPE'] == 'tinytext'
                    ) {
                        $type = 'string';
                    } elseif ($info['DATA_TYPE'] == 'int'
                        || $info['DATA_TYPE'] == 'smallint'
                        || $info['DATA_TYPE'] == 'float'
                        || $info['DATA_TYPE'] == 'bigint'
                        || $info['DATA_TYPE'] == 'tinyint'
                        || $info['DATA_TYPE'] == 'double') {
                        $type = 'number';
                    } elseif ($info['DATA_TYPE'] == 'timestamp' || $info['DATA_TYPE'] == 'datetime' || $info['DATA_TYPE'] == 'date') {
                        $type = 'date';
                    } elseif ($info['DATA_TYPE'] == 'text'
                        || $info['DATA_TYPE'] == 'mediumblob'
                        || $info['DATA_TYPE'] == 'mediumtext'
                        || $info['DATA_TYPE'] == 'longtext'
                        || $info['DATA_TYPE'] == 'longblob'
                        || $info['DATA_TYPE'] == 'blob') {
                        $type = 'html';
                    } elseif ($info['DATA_TYPE'] == 'decimal') {
                        $type = 'money';
                    } else {
                        $type = 'string';
                    }
                }
            }

            $type = $this->objectManager->create($this->schema->getType($type));

            foreach ($type->getAggregators() as $aggregatorName) {
                $aggregator = $this->schema->getAggregator($aggregatorName);
                $aggregator = $this->objectManager->create($aggregator);

                $name = $info['COLUMN_NAME'];

                $columnName = $name . ($aggregatorName !== 'none' ? "__$aggregatorName" : '');

                $data = [
                    'name'       => $columnName,
                    'type'       => $type,
                    'aggregator' => $aggregator,
                    'data'       => [
                        'label'    => $info['COLUMN_NAME'],
                        'internal' => true,
                        'table'    => $table,
                        'fields'   => [$name],
                    ],
                ];

                if (!isset($table->getColumns()[$columnName])) {
                    $this->objectManager->create(Column::class, $data);
                }
            }
        }
    }
}
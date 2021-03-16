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



namespace Mirasvit\ReportBuilder\Ui\Form;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Ui\Component\AbstractComponent;
use Mirasvit\ReportApi\Api\SchemaInterface;

class Schema extends AbstractComponent
{
    private $schema;

    public function __construct(
        SchemaInterface $schema,
        ContextInterface $context,
        $components = [],
        array $data = []
    ) {
        $this->schema = $schema;

        parent::__construct($context, $components, $data);
    }

    public function getComponentName()
    {
        return 'schema';
    }

    public function prepare()
    {
        $config = $this->getData('config');

        foreach ($this->schema->getTables() as $table) {
            $tableData = [
                'name'      => $table->getName(),
                'label'     => $table->getLabel(),
                'columns'   => [],
                'relations' => [],
            ];

            foreach ($table->getColumns() as $column) {
                $tableData['columns'][] = [
                    'name'       => $column->getName(),
                    'label'      => $column->getLabel(),
                    'identifier' => $column->getIdentifier(),
                    'type'       => $column->getType()->getType(),
                    'aggregator' => $column->getAggregator()->getType(),
                ];
            }

            foreach ($this->schema->getRelations() as $relation) {
                if ($relation->getOppositeTable($table)
                    && $relation->getRightField()) {
                    $field = $relation->getLeftField();
                    if ($relation->getLeftField()->getTable() !== $table) {
                        $field = $relation->getRightField();
                    }
                    $tableData['relations'][] = [
                        'leftTable'  => $table->getName(),
                        'rightTable' => $relation->getOppositeTable($table)->getName(),
                        'leftField'  => $field->getName(),
                        'rightField' => $relation->getOppositeField($field)->getName(),
                        'type'       => $relation->getType($relation->getOppositeTable($table)),
                    ];
                }
            }

            $config['tables'][] = $tableData;
        }


        $this->setData('config', $config);

        parent::prepare();
    }
}
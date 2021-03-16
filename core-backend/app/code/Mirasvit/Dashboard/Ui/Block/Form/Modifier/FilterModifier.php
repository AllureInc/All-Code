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
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Ui\Block\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Mirasvit\Dashboard\Ui\ComponentTrait;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Ui\Context;
use Mirasvit\ReportApi\Api\SchemaInterface;

class FilterModifier implements ModifierInterface
{
    use ComponentTrait;

    const DEFAULT_FILTERS = [
        'sales_order|status'
    ];

    /**
     * @var ArrayManager
     */
    private $arrayManager;
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;
    /**
     * @var SchemaInterface
     */
    private $schema;
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Context $context,
        SchemaInterface $schema,
        ReportRepositoryInterface $reportRepository,
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->reportRepository = $reportRepository;
        $this->schema = $schema;
        $this->context = $context;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $meta = $this->arrayManager->set(
            'filter/children/filter.data/children/record/children/column/arguments/data',
            $meta,
            [
                'config' => [
                    'columns' => $this->getColumns()
                ],
            ]
        );

        return $meta;
    }

    /**
     * @return array
     */
    private function getColumns()
    {
        $options = [];
        $tables  = [];
        $reports = [];

        // collect all columns for tables and group all reports by their table name
        foreach($this->reportRepository->getList() as $report) {
            $report->init();

            $allReportColumns = $report->getAllColumns();
            $activeColumns = $this->context->getColumnManager()->getActiveColumns($report);

            // add default filters, even if the column is not active itself
            array_map(function($col) use (&$activeColumns, $allReportColumns) {
                if (in_array($col, $allReportColumns, true)) {
                    $activeColumns[] = $col;
                }
            }, self::DEFAULT_FILTERS);

            if (isset($tables[$report->getTable()])) {
                $activeColumns = array_unique(array_merge($tables[$report->getTable()], $activeColumns));
            }

            $tables[$report->getTable()] = $activeColumns;

            if (!isset($reports[$report->getTable()])) {
                $reports[$report->getTable()] = [$report->getIdentifier()];
            } else {
                $reports[$report->getTable()][] = $report->getIdentifier();
            }
        }


        // create options for collected columns
        foreach ($tables as $table => $tableColumns) {
            $ids     = $reports[$table];
            $ids[]   = $table;
            $columns = [];

            foreach ($tableColumns as $columnName) {
                $column = $this->schema->getColumn($columnName);
                $columns[] = [
                    'value' => $columnName,
                    'label' => $column->getLabel()
                ];
            }

            $options[] = [
                'reports' => $ids,
                'columns' => $columns
            ];
        }

        return $options;
    }
}

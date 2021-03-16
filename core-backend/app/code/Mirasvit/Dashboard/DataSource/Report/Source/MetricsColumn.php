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



namespace Mirasvit\Dashboard\DataSource\Report\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\Report\Ui\Context;
use Mirasvit\ReportApi\Api\SchemaInterface;

class MetricsColumn implements ArrayInterface
{
    private $schema;

    private $reportRepository;
    /**
     * @var Context
     */
    private $context;

    public function __construct(
        Context $context,
        SchemaInterface $schema,
        ReportRepositoryInterface $reportRepository
    ) {
        $this->schema = $schema;
        $this->reportRepository = $reportRepository;
        $this->context = $context;
    }

    /**
     * Columns by Reports, only unique columns are visible.
     *
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        $columns = [];

        foreach ($this->reportRepository->getList() as $report) {
            $report->init();
            if (!$report->getName()) {
                continue;
            }

            $reportColumns = $this->context->getColumnManager()->getActiveColumns($report);
            foreach ($reportColumns as $columnIdentifier) {
                $column = $this->schema->getColumn($columnIdentifier);

                if (!$column->getLabel()
                    || $column->isInternal()
                    || in_array($column->getIdentifier(), $columns, true)
                    || !$this->schema->isComplexColumn($column)
                ) {
                    continue;
                }

                if (!isset($options[$report->getIdentifier()])) {
                    $options[$report->getIdentifier()] = [
                        'label'    => $report->getName(),
                        'value'    => $report->getIdentifier(),
                        'optgroup' => [],
                    ];
                }

                $columns[] = $column->getIdentifier();
                $options[$report->getIdentifier()]['optgroup'][] = [
                    'label' => (string)$column->getLabel(),
                    'value' => $column->getIdentifier(),
                ];
            }
        }

        return $options;
    }
}

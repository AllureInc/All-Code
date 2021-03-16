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

class ReportsColumn implements ArrayInterface
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
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];

        // Columns by Reports
        foreach ($this->reportRepository->getList() as $report) {
            $report->init();
            if (!$report->getName()) {
                continue;
            }

            $columns = [];
            $reportColumns = $this->context->getColumnManager()->getActiveColumns($report);
            foreach ($reportColumns as $columnIdentifier) {
                $column = $this->schema->getColumn($columnIdentifier);

                if (!$column->getLabel() || $column->isInternal()) {
                    continue;
                }

                $columns[] = [
                    'label' => (string)$column->getLabel(),
                    'value' => $column->getIdentifier(),
                ];
            }

            $options[$report->getIdentifier()] = $columns;
        }

        return $options;
    }
}

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
use Mirasvit\ReportApi\Api\SchemaInterface;

class Reports implements ArrayInterface
{
    private $schema;

    private $reportRepository;

    public function __construct(
        SchemaInterface $schema,
        ReportRepositoryInterface $reportRepository
    ) {
        $this->schema = $schema;
        $this->reportRepository = $reportRepository;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $options = [];
        foreach ($this->reportRepository->getList() as $report) {
            if (!$report->getName()) {
                continue;
            }

            $options[] = [
                'label' => (string)$report->getName(),
                'value' => $report->getIdentifier(),
            ];
        }

        return $options;
    }
}

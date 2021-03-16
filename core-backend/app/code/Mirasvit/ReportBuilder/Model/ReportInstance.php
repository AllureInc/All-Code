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



namespace Mirasvit\ReportBuilder\Model;

use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;

class ReportInstance extends AbstractReport
{
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    /**
     * @var string
     */
    private $name;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        Context $context
    ) {
        $this->reportRepository = $reportRepository;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return $this->name;
    }

    public function setName($value)
    {
        $this->name = $value;

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getIdentifier()
    {
        return 'report_builder_' . $this->getId();
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $report = $this->reportRepository->get($this->getId());

        $config = $report->getConfig();

        $this->setTable(
            $this->normalize($config['table'])
        )->setDefaultColumns(
            $this->normalize($config['default_columns'], true)
        )->setDefaultDimension(
            $this->normalize($config['default_dimension'])
        )->setDimensions(
            $this->normalize($config['dimensions'], true)
        )->setFastFilters(
            $this->normalize($config['fast_filters'], true)
        )->addAvailableFilters(
            $this->normalize($config['available_filters'], true)
        )->setColumns([]);

        $this->getChartConfig()
            ->setType($this->normalize($config['chart_type']))
            ->setDefaultColumns($this->normalize($config['chart_columns'], true));

        return $this;
    }

    private function normalize($value, $isArray = false)
    {
        if ($isArray) {
            $result = [];

            $value = explode(',', $value);
            foreach ($value as $val) {
                $val = explode(PHP_EOL, $val);

                foreach ($val as $v) {
                    $result[] = trim($v);
                }
            }

            return array_filter($result);
        } else {
            return trim($value);
        }
    }
}

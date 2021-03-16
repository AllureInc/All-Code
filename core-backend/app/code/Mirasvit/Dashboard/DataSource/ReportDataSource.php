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



namespace Mirasvit\Dashboard\DataSource;

use Magento\Framework\Webapi\ServiceOutputProcessor;
use Mirasvit\Dashboard\Api\Data\BlockInterface;
use Mirasvit\Dashboard\Api\Data\DataSourceInterface;
use Mirasvit\Dashboard\Renderer\SingleRenderer;
use Mirasvit\Dashboard\Renderer\TableRenderer;
use Mirasvit\Report\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportApi\Api\Processor\ResponseItemInterface;
use Mirasvit\ReportApi\Api\RequestBuilderInterface;
use Mirasvit\ReportApi\Api\RequestInterface;
use Mirasvit\ReportApi\Api\ResponseInterface;
use Mirasvit\ReportApi\Processor\ResponseItem;
use Mirasvit\ReportApi\Api\SchemaInterface;

class ReportDataSource implements DataSourceInterface
{
    const DEFAULT_DATE_COLUMN = 'created_at';

    const DATE_FILTER = 'date_filter';

    private $requestBuilder;

    private $serviceOutputProcessor;

    private $context;
    /**
     * @var SchemaInterface
     */
    private $schema;
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        SchemaInterface $schema,
        RequestBuilderInterface $requestBuilder,
        ServiceOutputProcessor $serviceOutputProcessor,
        Context $context
    ) {
        $this->requestBuilder = $requestBuilder;
        $this->serviceOutputProcessor = $serviceOutputProcessor;
        $this->context = $context;
        $this->schema = $schema;
        $this->reportRepository = $reportRepository;
    }

    public function getIdentifier()
    {
        return 'report';
    }

    public function getName()
    {
        return 'Mirasvit Reports';
    }

    public function getData(BlockInterface $block)
    {
        if ($block->getData('renderer') === SingleRenderer::ID) {
            $outputData = $this->getColumnData($block);
        } else {
            $outputData = $this->getTableData($block);
        }

        $outputData['type'] = ResponseInterface::class;

        return $outputData;
    }

    public function getColumnData(BlockInterface $block)
    {
        $columnName = $block->getData('metric/data') ?: $block->getData('report/data');
        if (is_array($columnName)) {
            $columnName = array_shift($columnName);
        }

        list($tableName,) = explode('|', $columnName);

        $dimension = $this->getOptimalDimensionColumn($block, $tableName);
        $response = $this->getResponse($block, $tableName, $dimension, [$columnName]);

        return $this->prepareResponse($response, $columnName, $dimension);
    }

    public function getTableData(BlockInterface $block)
    {
        $reportId  = $block->getData('report/data/report');
        $columns   = $block->getData('report/data/columns');

        $report = $this->reportRepository->get($reportId);

        $dimension = $report->getDefaultDimension();
        $tableName = $report->getTable();

        // set column for date filter
        foreach ($report->getFastFilters() as $filter) {
            if (stripos($filter, 'created_at') !== false) {
                $block->setData(self::DATE_FILTER, $filter);
                break;
            }
        }

        $response = $this->getResponse($block, $tableName, $dimension, $columns);

        return $response->toArray();
    }

    /**
     * Get response for table columns.
     *
     * @param BlockInterface $block
     * @param string         $tableName
     * @param string         $dimension
     * @param string[]       $columns
     *
     * @return ResponseInterface
     */
    public function getResponse(BlockInterface $block, $tableName, $dimension, array $columns)
    {
        $request = $this->requestBuilder->create()
            ->setTable($tableName)
            ->setColumns($columns)
            ->setDimension($dimension)
            ->addColumn($dimension);

        $this->addDateFilter($block, $request)
            ->sortAndLimit($block, $request)
            ->filter($block, $request);

        return $request->process();

    }

    /**
     * Get optimal dimension column name.
     *
     * @param BlockInterface $block
     * @param string         $tableName
     *
     * @return string
     */
    public function getOptimalDimensionColumn(BlockInterface $block, $tableName)
    {
        $dateRange = $this->context->getDateRange($block);
        $from = $dateRange->getFrom()->toString(\Zend_Date::W3C);
        $to = $dateRange->getTo()->toString(\Zend_Date::W3C);

        $optimalIntervalDimension = $this->context->dateService->getOptimalDimension($from, $to);

        return "$tableName|created_at__$optimalIntervalDimension";
    }

    private function prepareResponse(ResponseInterface $response, $valueColumn, $labelColumn)
    {
        $data = $response->toArray();

        $data['totals'] = $this->prepareItem($response->getTotals(), $valueColumn, $labelColumn);
        $data['items'] = [];

        foreach ($response->getItems() as $item) {
            $data['items'][] = $this->prepareItem($item, $valueColumn, $labelColumn);
        }

        return $data;
    }

    private function prepareItem(ResponseItemInterface $item, $valueColumn, $labelColumn)
    {
        $data = [
            ResponseItem::DATA           => [
                'label' => $item->getData($labelColumn),
                'value' => $item->getData($valueColumn),
            ],
            ResponseItem::FORMATTED_DATA => [
                'label' => $item->getFormattedData($labelColumn),
                'value' => $item->getFormattedData($valueColumn),
            ],
        ];

        if ($item->getData("c|$valueColumn")) {
            $data[ResponseItem::DATA]['c|label'] = $item->getData("c|$labelColumn");
            $data[ResponseItem::DATA]['c|value'] = $item->getData("c|$valueColumn");
            $data[ResponseItem::FORMATTED_DATA]['c|label'] = $item->getFormattedData("c|$labelColumn");
            $data[ResponseItem::FORMATTED_DATA]['c|value'] = $item->getFormattedData("c|$valueColumn");
        }

        return $data;
    }

    /**
     * @param BlockInterface   $block
     * @param RequestInterface $request
     *
     * @return $this
     */
    private function addDateFilter(BlockInterface $block, RequestInterface $request)
    {
        $dateColumn = $block->getData(self::DATE_FILTER) ?: self::DEFAULT_DATE_COLUMN;
        $dateRange  = $this->context->getDateRange($block);
        $from       = $dateRange->getFrom()->toString('YYYY-MM-dd HH:mm:ss');
        $to         = $dateRange->getTo()->toString('YYYY-MM-dd HH:mm:ss');

        $request->addFilter($dateColumn, $from, 'gteq', 'A')
            ->addFilter($dateColumn, $to, 'lteq', 'A');

        $dateRange = $this->context->getComparisonDateRange($block);
        if ($dateRange && $block->getData('renderer') !== TableRenderer::ID) { // disable comparison for tables
            $cFrom = $dateRange->getFrom()->toString('YYYY-MM-dd HH:mm:ss');
            $cTo = $dateRange->getTo()->toString('YYYY-MM-dd HH:mm:ss');

            $request->addFilter($dateColumn, $cFrom, 'gteq', 'c')
                ->addFilter($dateColumn, $cTo, 'lteq', 'c');
        }

        return $this;
    }

    /**
     * @param BlockInterface   $block
     * @param RequestInterface $request
     *
     * @return $this
     */
    private function sortAndLimit(BlockInterface $block, RequestInterface $request)
    {
        if ($block->getData('filter/order')) {
            $dir = $block->getData('filter/dir') ?: 'asc';
            $request->addSortOrder($block->getData('filter/order'), $dir);
        }

        if ($block->getData('filter/limit')) {
            $request->setPageSize($block->getData('filter/limit'));
        }

        return $this;
    }

    /**
     * @param BlockInterface   $block
     * @param RequestInterface $request
     *
     * @return $this
     */
    private function filter(BlockInterface $block, RequestInterface $request)
    {
        $isCompared = false;
        foreach ($request->getFilters() as $filter) {
            if ($filter->getGroup() === 'c') {
                $isCompared = true;
            }
        }

        if ($block->getData('filter/data')) {
            $conditions = $block->getData('filter/data');
            foreach ($conditions as $condition) {
                if (!isset($condition['column'], $condition['value'])) {
                    continue;
                }

                foreach (['A', 'c'] as $group) {
                    // do not filter 'c' collection if comparing disabled
                    if ($group === 'c' && !$isCompared) {
                        continue;
                    }

                    $request->addFilter(
                        $condition['column'],
                        $condition['value'],
                        isset($condition['operator']) ? $condition['operator'] : 'eq',
                        $group
                    );
                }
            }
        }

        return $this;
    }
}
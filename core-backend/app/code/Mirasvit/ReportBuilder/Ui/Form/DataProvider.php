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

use Magento\Ui\DataProvider\AbstractDataProvider;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;

class DataProvider extends AbstractDataProvider
{
    /**
     * @var ReportRepositoryInterface
     */
    private $reportRepository;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        $name,
        $primaryFieldName,
        $requestFieldName,
        array $meta = [],
        array $data = []
    ) {
        $this->reportRepository = $reportRepository;
        $this->collection = $reportRepository->getCollection();

        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * {@inheritdoc}
     */
    public function getData()
    {
        $data = [];

        foreach ($this->collection as $report) {
            $data[$report->getId()] = [
                ReportInterface::ID     => $report->getId(),
                ReportInterface::TITLE  => $report->getTitle(),
                ReportInterface::CONFIG => $report->getConfig(),
            ];
        }

        return $data;
    }
}

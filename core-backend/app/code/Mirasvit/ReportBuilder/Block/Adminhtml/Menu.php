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



namespace Mirasvit\ReportBuilder\Block\Adminhtml;

use Magento\Framework\DataObject;
use Magento\Backend\Block\Template;
use Magento\Backend\Block\Template\Context;
use Mirasvit\Core\Block\Adminhtml\AbstractMenu;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Api\Data\ConfigInterface;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportBuilder\Api\Repository\ConfigRepositoryInterface;

class Menu extends AbstractMenu
{
    /**
     * @var ConfigRepositoryInterface
     */
    protected $reportRepository;
    /**
     * @var ConfigRepositoryInterface
     */
    private $configRepository;

    public function __construct(
        ReportRepositoryInterface $reportRepository,
        ConfigRepositoryInterface $configRepository,
        Context $context
    ) {
        $this->reportRepository = $reportRepository;
        $this->configRepository = $configRepository;

        $this->visibleAt(['reportbuilder']);

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    protected function buildMenu()
    {
        $this->addItem([
            'id'       => 'report',
            'resource' => 'Mirasvit_ReportBuilder::reportBuilder',
            'title'    => __('Report Builder'),
            'url'      => $this->urlBuilder->getUrl('reportbuilder/report'),
        ])->addItem([
            'id'       => 'config',
            'resource' => 'Mirasvit_ReportBuilder::configBuilder',
            'title'    => __('Config Builder'),
            'url'      => $this->urlBuilder->getUrl('reportbuilder/config'),
        ])->addItem([
            'resource' => 'Mirasvit_ReportBuilder::reportBuilder',
            'title'    => __('Build New Report'),
            'url'      => $this->urlBuilder->getUrl('reportbuilder/report/new'),
        ], 'report')
        ->addItem([
            'resource' => 'Mirasvit_ReportBuilder::configBuilder',
            'title'    => __('Add New Config'),
            'url'      => $this->urlBuilder->getUrl('reportbuilder/config/new'),
        ], 'config');

        foreach ($this->reportRepository->getCollection() as $report) {
            $this->addItem([
                'resource' => 'Mirasvit_ReportBuilder::reportBuilder',
                'title'    => $report->getTitle(),
                'url'      => $this->urlBuilder->getUrl('reportbuilder/report/edit', [
                    ReportInterface::ID => $report->getId()
                ]),
            ], 'report');
        }

        foreach ($this->configRepository->getCollection() as $config) {
            $this->addItem([
                'resource' => 'Mirasvit_ReportBuilder::configBuilder',
                'title'    => $config->getTitle(),
                'url'      => $this->urlBuilder->getUrl('reportbuilder/config/edit', [
                    ConfigInterface::ID => $config->getId()
                ]),
            ], 'config');
        }

        return $this;
    }
}
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



namespace Mirasvit\ReportBuilder\Controller\Adminhtml\Report;

use Magento\Framework\Controller\ResultFactory;
use Mirasvit\ReportBuilder\Controller\Adminhtml\Report;

class Render extends Report
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        try {
            $report = $this->initModel();

            $config = $this->getRequest()->getParam('config', []);

            $report->setConfig($config)
                ->setTitle($this->getRequest()->getParam('title'))
                ->setUserId($this->reportRepository->getUserId());

            $this->reportRepository->save($report);

            $instance = $this->builderService->getReportInstance($report);

            $instance->init();

            $data = [
                'success' => true,
                'html'    => $this->builderService->createUiComponent($this->getRequest(), $instance),
            ];
        } catch (\Exception $e) {
            $data = [
                'success' => false,
                'message' => $e->getMessage(),
            ];
        }

        return $this->resultFactory->create(ResultFactory::TYPE_JSON)
            ->setData($data);
    }
}

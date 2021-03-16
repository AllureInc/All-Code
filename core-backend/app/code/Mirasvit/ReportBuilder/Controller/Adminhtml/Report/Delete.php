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


use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Controller\Adminhtml\Report;

class Delete extends Report
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $report = $this->initModel();

        $resultRedirect = $this->resultRedirectFactory->create();

        if ($report->getId()) {
            try {
                $this->reportRepository->delete($report);

                $this->messageManager->addSuccessMessage(__('The report has been deleted.'));

                return $resultRedirect->setPath('*/*/');
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [ReportInterface::ID => $report->getId()]);
            }
        } else {
            $this->messageManager->addErrorMessage(__('This report no longer exists.'));

            return $resultRedirect->setPath('*/*/');
        }
    }
}

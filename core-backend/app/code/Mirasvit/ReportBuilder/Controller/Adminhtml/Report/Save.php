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
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Controller\Adminhtml\Report;

class Save extends Report
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();

        if ($this->getRequest()->getParams()) {
            $report = $this->initModel();

            $config = $this->getRequest()->getParam('config', []);

            $report->setConfig($config)
                ->setTitle($this->getRequest()->getParam('title'))
                ->setUserId($this->reportRepository->getUserId());

            try {
                $this->reportRepository->save($report);

                $this->messageManager->addSuccessMessage(__('You saved the report.'));

                return $resultRedirect->setPath('*/*/edit', [ReportInterface::ID => $report->getId()]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage($e->getMessage());

                return $resultRedirect->setPath('*/*/edit', [ReportInterface::ID => $report->getId()]);
            }
        } else {
            $resultRedirect->setPath('*/*/');
            $this->messageManager->addErrorMessage('No data to save.');

            return $resultRedirect;
        }
    }
}

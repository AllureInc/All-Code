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

class Index extends Report
{
    /**
     * {@inheritdoc}
     */
    public function execute()
    {
        /** @var \Magento\Backend\Model\View\Result\Page\Interceptor $resultPage */
        $resultPage = $this->resultFactory->create(ResultFactory::TYPE_PAGE);

        $emptyReports = $this->reportRepository->getCollection()
            ->addFieldToFilter(ReportInterface::TITLE, "");

        foreach ($emptyReports as $report) {
            $this->reportRepository->delete($report);
        }

        $this->initPage($resultPage);

        return $resultPage;
    }
}

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



namespace Mirasvit\ReportBuilder\Controller\Adminhtml;

use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Api\Repository\ReportRepositoryInterface;
use Mirasvit\ReportBuilder\Service\BuilderService;

abstract class Report extends Action
{
    /**
     * @var ReportRepositoryInterface
     */
    protected $reportRepository;

    /**
     * @var BuilderService
     */
    protected $builderService;

    /**
     * @var Context
     */
    protected $context;


    public function __construct(
        ReportRepositoryInterface $reportRepository,
        BuilderService $builderService,
        Context $context
    ) {
        $this->reportRepository = $reportRepository;
        $this->builderService = $builderService;
        $this->context = $context;

        parent::__construct($context);
    }

    /**
     * @param \Magento\Backend\Model\View\Result\Page $resultPage
     * @return \Magento\Backend\Model\View\Result\Page
     */
    protected function initPage($resultPage)
    {
        $resultPage->getConfig()->getTitle()->prepend(__('Report Builder'));

        return $resultPage;
    }

    /**
     * @return ReportInterface
     */
    public function initModel()
    {
        $model = $this->reportRepository->create();

        if ($this->getRequest()->getParam(ReportInterface::ID)) {
            $model = $this->reportRepository->get($this->getRequest()->getParam(ReportInterface::ID));
        }

        return $model;
    }

    /**
     * {@inheritdoc}
     */
    protected function _isAllowed()
    {
        return $this->context->getAuthorization()->isAllowed('Mirasvit_ReportBuilder::reportBuilder');
    }
}

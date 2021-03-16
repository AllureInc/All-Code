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



namespace Mirasvit\ReportBuilder\Service;

use Magento\Framework\App\RequestInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Framework\View\Element\UiComponentInterface;
use Mirasvit\ReportBuilder\Api\Data\ReportInterface;
use Mirasvit\ReportBuilder\Model\ReportInstanceFactory;
use Mirasvit\ReportBuilder\Model\ReportInstance;

class BuilderService
{
    private $reportInstanceFactory;

    private $registry;

    private $uiComponentFactory;

    public function __construct(
        ReportInstanceFactory $reportInstanceFactory,
        Registry $registry,
        UiComponentFactory $uiComponentFactory
    ) {
        $this->reportInstanceFactory = $reportInstanceFactory;
        $this->registry = $registry;
        $this->uiComponentFactory = $uiComponentFactory;
    }

    public function getReportInstance(ReportInterface $report)
    {
        $instance = $this->reportInstanceFactory->create();
        $instance->setId($report->getId())
            ->setName($report->getTitle());

        return $instance;
    }

    public function createUiComponent(RequestInterface $request, ReportInstance $instance)
    {
        /** @var \Magento\Framework\App\Request\Http $request */
        $headers = (new \Zend\Http\Headers())
            ->addHeaderLine('Accept', 'html');
        $request->setHeaders($headers);

        $this->registry->register('current_report', $instance);

        $component = $this->uiComponentFactory->create('mst_report');
        $this->prepareComponent($component);

        $component = (string)$component->render();

        return $component;
    }

    /**
     * Call prepare method in the component UI
     *
     * @param UiComponentInterface $component
     * @return void
     */
    private function prepareComponent(UiComponentInterface $component)
    {
        foreach ($component->getChildComponents() as $child) {
            $this->prepareComponent($child);
        }

        $component->prepare();
    }
}
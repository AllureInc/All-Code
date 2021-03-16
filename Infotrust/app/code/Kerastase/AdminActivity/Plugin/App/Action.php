<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Plugin\App;

/**
 * Class Action
 * @package Kerastase\AdminActivity\Plugin\App
 */
class Action
{
    /**
     * @var \Kerastase\AdminActivity\Model\Processor
     */
    public $processor;

    /**
     * @var \Kerastase\AdminActivity\Helper\Benchmark
     */
    public $benchmark;

    /**
     * Action constructor.
     * @param \Kerastase\AdminActivity\Model\Processor $processor
     * @param \Kerastase\AdminActivity\Helper\Benchmark $benchmark
     */
    public function __construct(
        \Kerastase\AdminActivity\Model\Processor $processor,
        \Kerastase\AdminActivity\Helper\Benchmark $benchmark
    ) {
        $this->processor = $processor;
        $this->benchmark = $benchmark;
    }

    /**
     * Get before dispatch data
     * @param \Magento\Framework\Interception\InterceptorInterface $controller
     * @return void
     */
    public function beforeDispatch(\Magento\Framework\Interception\InterceptorInterface $controller)
    {
        $this->benchmark->start(__METHOD__);
        $actionName = $controller->getRequest()->getActionName();
        $fullActionName = $controller->getRequest()->getFullActionName();

        $this->processor->init($fullActionName, $actionName);
        $this->processor->addPageVisitLog($controller->getRequest()->getModuleName());
        $this->benchmark->end(__METHOD__);
    }
}

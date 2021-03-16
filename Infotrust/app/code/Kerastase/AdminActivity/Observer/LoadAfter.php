<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Observer;

use Magento\Framework\Event\ObserverInterface;

/**
 * Class LoadAfter
 * @package Kerastase\AdminActivity\Observer
 */
class LoadAfter implements ObserverInterface
{
    /**
     * @var \Kerastase\AdminActivity\Model\Processor
     */
    private $processor;

    /**
     * @var \Kerastase\AdminActivity\Helper\Data
     */
    public $helper;

    /**
     * @var \Kerastase\AdminActivity\Helper\Benchmark
     */
    public $benchmark;

    /**
     * LoadAfter constructor.
     * @param \Kerastase\AdminActivity\Model\Processor $processor
     * @param \Kerastase\AdminActivity\Helper\Data $helper
     * @param \Kerastase\AdminActivity\Helper\Benchmark $benchmark
     */
    public function __construct(
        \Kerastase\AdminActivity\Model\Processor $processor,
        \Kerastase\AdminActivity\Helper\Data $helper,
        \Kerastase\AdminActivity\Helper\Benchmark $benchmark
    ) {
        $this->processor = $processor;
        $this->helper = $helper;
        $this->benchmark = $benchmark;
    }

    /**
     * Delete after
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->benchmark->start(__METHOD__);
        if (!$this->helper->isEnable()) {
            return $observer;
        }
        $object = $observer->getEvent()->getObject();
        $this->processor->modelLoadAfter($object);
        $this->benchmark->end(__METHOD__);
    }
}

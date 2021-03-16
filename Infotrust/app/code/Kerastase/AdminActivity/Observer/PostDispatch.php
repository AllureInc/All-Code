<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Kerastase\AdminActivity\Helper\Data as Helper;

/**
 * Class PostDispatch
 * @package Kerastase\AdminActivity\Observer
 */
class PostDispatch implements ObserverInterface
{
    /**
     * @var \Kerastase\AdminActivity\Model\Processor
     */
    private $processor;

    /**
     * @var Helper
     */
    public $helper;

    /**
     * @var \Kerastase\AdminActivity\Helper\Benchmark
     */
    public $benchmark;

    /**
     * PostDispatch constructor.
     * @param \Kerastase\AdminActivity\Model\Processor $processor
     * @param Helper $helper
     * @param \Kerastase\AdminActivity\Helper\Benchmark $benchmark
     */
    public function __construct(
        \Kerastase\AdminActivity\Model\Processor $processor,
        Helper $helper,
        \Kerastase\AdminActivity\Helper\Benchmark $benchmark
    ) {
        $this->processor = $processor;
        $this->helper = $helper;
        $this->benchmark = $benchmark;
    }

    /**
     * Post dispatch
     * @param \Magento\Framework\Event\Observer $observer
     * @return \Magento\Framework\Event\Observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $this->benchmark->start(__METHOD__);
        if (!$this->helper->isEnable()) {
            return $observer;
        }
        $this->processor->saveLogs();
        $this->benchmark->end(__METHOD__);
    }
}

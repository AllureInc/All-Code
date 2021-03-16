<?php
/**
 * Kerastase
 * @category   Kerastase
 * @package    Kerastase_AdminActivity
  */
namespace Kerastase\AdminActivity\Observer;

use Magento\Framework\Event\ObserverInterface;
use \Kerastase\AdminActivity\Helper\Data as Helper;
use \Kerastase\AdminActivity\Api\ActivityRepositoryInterface;

/**
 * Class DeleteAfter
 * @package Kerastase\AdminActivity\Observer
 */
class DeleteAfter implements ObserverInterface
{
    /**
     * @var string
     */
    const SYSTEM_CONFIG = 'adminhtml_system_config_save';

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
     * DeleteAfter constructor.
     * @param \Kerastase\AdminActivity\Model\Processor $processor
     * @param Helper $helper
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
        if ($this->processor->validate($object) && ($this->processor->initAction==self::SYSTEM_CONFIG)) {
            $this->processor->modelEditAfter($object);
        }
        $this->processor->modelDeleteAfter($object);
        $this->benchmark->end(__METHOD__);
    }
}

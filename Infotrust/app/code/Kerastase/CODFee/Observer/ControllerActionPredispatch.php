<?php
namespace Kerastase\CODFee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class ControllerActionPredispatch implements ObserverInterface
{
    /**
     * @var \Kerastase\CODFee\Helper\Data
     */
    protected $_codHelper;


    public function __construct(
        \Kerastase\CODFee\Helper\Data $codHelper
    ) {
        $this->_codHelper          = $codHelper;
    }

    public function execute(Observer $observer)
    {
        $this->_codHelper->log(__METHOD__, true);
        $this->_codHelper->log($observer->getEvent()->getName() . ' > Helper DI');
    }
}
<?php
namespace Kerastase\CODFee\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesModelServiceQuoteSubmitBefore implements ObserverInterface
{
    /**
     * @var \Kerastase\CODFee\Helper\Data
     */
    protected $_helper;

    public function __construct(
        \Kerastase\CODFee\Helper\Data $helper
    ) {
        $this->_helper = $helper;
    }

    /**
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        // die('*********************');
        $this->_helper->log(__METHOD__, true);

        $order = $observer->getEvent()->getOrder();
        $quote = $observer->getEvent()->getQuote();
        $order->setBaseCodFee($quote->getBaseCodFee());
        $order->setCodFee($quote->getCodFee());
        // print_r('getBaseCodFee: '.$quote->getBaseCodFee());
        // print_r('getCodFee: '.$quote->getCodFee());
        // die('***');
        $this->_helper->log('$quote > getCodFee() => ' . $quote->getCodFee());
        $this->_helper->log('$quote > getBaseCodFee() => ' . $quote->getBaseCodFee());
        $this->_helper->log('$order > getCodFee() => ' . $order->getCodFee());
        $this->_helper->log('$order > getBaseCodFee() => ' . $order->getBaseCodFee());
    }
}
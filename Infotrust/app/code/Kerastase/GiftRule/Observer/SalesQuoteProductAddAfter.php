<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class SalesQuoteProductAddAfter implements ObserverInterface
{
    /**
     * @var \ Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;

    /**
     * @param \Kerastase\GiftRule\Helper\Data $giftruleHelper
     */
    public function __construct(
        \Kerastase\GiftRule\Helper\Data $giftruleHelper
    ) {
        $this->_giftruleHelper          = $giftruleHelper;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_giftruleHelper->isEnabled()) {
            return;
        }
        $this->_giftruleHelper->log(__METHOD__, true);
        $this->_giftruleHelper->log($observer->getEvent()->getName());
        foreach ($observer->getEvent()->getItems() as $item) {
            $item->setIsFreeProduct($item->getProduct()->getIsFreeProduct());
            $item->setFreeGiftLabel($item->getProduct()->getFreeGiftLabel());
            $item->setPrice(0);
            $item->setFinalPrice(0);
        }
    }
}

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

class CatalogProductTypePrepareFullOptions implements ObserverInterface
{
    /**
     * @var \Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;

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
        if ($observer->getData('is_free_product')) {
            $observer->getProduct()->setIsFreeProduct(true);
            $observer->getProduct()->setFreeGiftLabel(
                $observer->getData('free_gift_label')
            );
        }
    }
}

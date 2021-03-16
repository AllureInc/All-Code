<?php
/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class OnProductView implements ObserverInterface
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface   $objectManager
     * @param \Magento\Framework\Stdlib\DateTime\DateTime $date
     * @param \Magento\Framework\Stdlib\DateTime          $dateTime
     * @param CollectionFactory                           $collectionFactory
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {

        $this->_objectManager = $objectManager;
    }

    /**
     * Observer to disable product based on vacation status.
     */
    public function execute(Observer $observer)
    {
        $productId = $observer->getProduct()->getId();
        $status = $this->_objectManager
            ->create('Webkul\MpSellerVacation\Helper\Data')
            ->updateProductVacationStatus($productId);
    }
}

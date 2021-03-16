<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class AfterInvoiceGeneration implements ObserverInterface
{
    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesOrder;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_magentoSalesOrderItem;
    
    /**
     * @var \Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory
     */
    protected $_adsPurchaseDetail;
    
     /**
      * @var \Magento\Framework\Message\ManagerInterface
      */
    protected $_messageManager;

    
    /**
     * Constructor
     *
     * @param \Magento\Sales\Model\Order                                    $salesOrder
     * @param \Magento\Sales\Model\Order\ItemFactory                        $magentoSalesOrderItem
     * @param \Magento\Framework\Message\ManagerInterface                   $messageManager
     * @param \Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory $adsPurchaseDetail
     */
    public function __construct(
        \Magento\Sales\Model\Order $salesOrder,
        \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory $adsPurchaseDetail
    ) {
        $this->_salesOrder = $salesOrder;
        $this->_magentoSalesOrderItem = $magentoSalesOrderItem;
        $this->_messageManager = $messageManager;
        $this->_adsPurchaseDetail = $adsPurchaseDetail;
    }

/**
 * This is the method that fires when the event runs.
 *
 * @param Observer $observer
 */
    public function execute(Observer $observer)
    {
        $invoice = $observer->getEvent()->getInvoice();
        $oids = $invoice->getOrderId();
        $order = $this->_salesOrder->load($oids);
        $data = [];
        foreach ($invoice->getAllItems() as $item) {
            if ($item->getSku() == "wk_mp_ads_plan") {
                try {
                    $model = $this->_adsPurchaseDetail->create()->load($item->getOrderItemId(), 'item_id');
                    if (count($model->getData())) {
                        $model->setInvoiceGenerated(1);
                        $model->save();
                    }
                } catch (\Exception $e) {
                    $this->_messageManager->addError(__($e->getMessage()));
                }
            }
        }
    }
}

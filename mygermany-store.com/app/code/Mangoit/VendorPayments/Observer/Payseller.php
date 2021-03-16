<?php
namespace Mangoit\VendorPayments\Observer;

class Payseller implements \Magento\Framework\Event\ObserverInterface
{

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $mageOrderModel;

    /**
     * @var \Webkul\Marketplace\Model\Sellertransaction
     */
    protected $transactionModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    public function __construct(
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Sales\Model\Order $mageOrderModel,
        \Webkul\Marketplace\Model\Sellertransaction $transactionModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date
    ) {
        $this->invoiceModel = $invoiceModel;
        $this->mageOrderModel = $mageOrderModel;
        $this->transactionModel = $transactionModel;
        $this->date = $date;

    }
	/**
     * Sales order save commmit after on order complete state event handler.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $eventData = $observer->getEvent()->getData();
        $orderId = isset($eventData[0]) ? $eventData[0]['id'] : 0;
        $sellerId = isset($eventData[0]) ? $eventData[0]['seller_id'] : 0;

        if($orderId && $sellerId) {
            $invoiceColl = $this->invoiceModel->getCollection()
                ->addFieldToFilter('seller_id', ['eq' => $sellerId])
                // ->addFieldToFilter('invoice_status', ['eq' => 0])
                ->addFieldToFilter('order_ids', ['finset' => [$orderId]]);
            $invoiceColl->walk([$this, 'updateInvoiceStatus_callback'], [$eventData]);
        }
    }

    public function updateInvoiceStatus_callback($item, $eventData){

        $eligibleOrders = $this->mageOrderModel->getCollection()
                ->addAttributeToFilter('entity_id',  ['in' => explode(',', $item->getOrderIds())])
                ->addAttributeToFilter('status', ['in' => ['complete','order_verified']]);
        $eligibleOrders->walk([$this, 'updateOrderStatus_callback']);

        $transId = isset($eventData[0]) ? $eventData[0]['trans_id'] : 0;
        $transRowId = isset($eventData[0]) ? $eventData[0]['mp_trans_row_id'] : 0;

        $transData = $this->transactionModel->load($transRowId);

        $item->setPayoutDate($this->date->gmtDate());
        $item->setTransactionId($transId);
        $item->setCustomNote($transData->getCustomNote());
        $item->setInvoiceStatus(2);
        $item->save();
    }
    public function updateOrderStatus_callback($item){
        $item->setStatus('closed');
        $item->save();
    }
}
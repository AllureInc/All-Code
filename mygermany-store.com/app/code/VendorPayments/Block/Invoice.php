<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\VendorPayments\Block;

use Mangoit\VendorPayments\Model\Paymentfees;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Invoice extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->invoiceModel = $invoiceModel;
        $this->date = $date;
        parent::__construct($context, $data);
    }

    public function canShowInvoiceButton()
    {
        $sellerData = $this->marketplaceHelper->getSeller();
        $sellerId = $this->marketplaceHelper->getCustomerId();
        $invoiceCollection = $this->invoiceModel->getCollection();
        $invoiceCollection->addFieldToFilter('seller_id', $sellerId);
        $invoiceCollection->setOrder('created_at', 'DESC');

        $lastInvoicedDate = $invoiceCollection->getFirstItem()->getCreatedAt();
        $lastInvoicedDate = $this->date->gmtDate('Y-m-d', strtotime($lastInvoicedDate));
        $vendorInvoiceSetting = $sellerData['generate_invoice'];

        $returnArr = [];
        $returnArr['status'] = false;
        if($vendorInvoiceSetting == 'weekly') {
            $afterDate = $this->date->gmtDate('Y-m-d', strtotime("-7 days"));
            $returnArr['status'] = ($lastInvoicedDate < $afterDate);
        } elseif($vendorInvoiceSetting == 'monthly') {
            $monthlyAfterDate = $this->date->gmtDate('Y-m-d', strtotime("-30 days"));
            $returnArr['status'] = ($lastInvoicedDate < $monthlyAfterDate);
        }
        $returnArr['last'] = $lastInvoicedDate;
        return $returnArr;
    }
}

<?php
/**
 * Mangoit Software.
 * Last changes 28-Jan-2019
 * Last bkup 29-jan-2019
 */

namespace Mangoit\VendorPayments\Block;

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
     * @var \Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices\Collection
     */
    protected $vendorInvoiceColl;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $customerSession;

    /**
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $salesListModel;

    /**
     * @var \Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $loadedInvoices;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $loadedSalesListItems;

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
        \Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices\Collection $vendorInvoiceColl,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\Marketplace\Model\Saleslist $salesListModel,
        \Mangoit\VendorPayments\Helper\Data $helper,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->invoiceModel = $invoiceModel;
        $this->date = $date;
        $this->vendorInvoiceColl = $vendorInvoiceColl;
        $this->customerSession = $customerSession;
        $this->salesListModel = $salesListModel;
        $this->helper = $helper;
        parent::__construct($context, $data);
    }


    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getAllGeneratedInvoices()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'vendor.invoices.list.pager'
            )->setCollection(
                $this->getAllGeneratedInvoices()
            );
            $this->setChild('pager', $pager);
        }

        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    /**
     * @return string
     */
    public function getHelper()
    {
        return $this->helper;
    }

    public function getMageRealOrderIdByOrderId($orderId = 0)
    {
        $invcData = $this->salesListModel->load($orderId, 'order_id');
        return $invcData->getMagerealorderId();
    }

    /**
     * getAllGeneratedInvoices
     * @return array
     */
    public function getAllGeneratedInvoices()
    {
        $sellerId = $this->marketplaceHelper->getCustomerId();
        
        if (!($sellerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->loadedInvoices) {
            // $this->loadedInvoices = $this->mappedProductRepository->getByAccountId($sellerId)->setOrder('entity_id', 'desc');
            $this->loadedInvoices = $this->vendorInvoiceColl->load()
                ->addFieldToFilter('seller_id', $sellerId)
                ->setOrder('entity_id', 'desc');

            $filter = $this->getRequest()->getParams();
            if (isset($filter['s'])) {
                $this->loadedInvoices = $this->loadedInvoices->addFieldToFilter('name', ['like'=>'%'.$filter['s'].'%']);
            }
        }
        return $this->loadedInvoices;
    }

    public function getInvoiceDetail($orderItemIds)
    {
        if (!$this->loadedSalesListItems) {
            $this->loadedSalesListItems = $this->salesListModel->getCollection();
        }

        $invcData = $this->loadedSalesListItems->addFieldToFilter('total_amount', ['neq' => 0])
                    ->addFieldToFilter('order_item_id',  array('in' => $orderItemIds));

        $sellerPriceArray = $this->helper->getSellerPriceArray($invcData);

        $totalAmountArray= [];
        $totalFeeArray= [];
        $realOrderIds= [];
        $dhlFeesArray= [];
        $vendorToMyGermanyCost = [];
        foreach ($invcData as $value) {
            $realOrderIds[] = $value['magerealorder_id'];
            $totalAmountArray[] = number_format($value['total_amount'], 2, '.', '');
            $totalFeeArray[] = number_format($value['total_commission'], 2, '.', '');
            $totalFeeArray[] = number_format($value['mits_payment_fee_amount'], 2, '.', '');
            $totalFeeArray[] = number_format($value['mits_exchange_rate_amount'], 2, '.', '');
            /*dhl work 24-jan-2019*/
            if (isset($value['dhl_fees']) && ($value['dhl_fees'] > 0)) {
                $totalFeeArray[] = number_format($value['dhl_fees'], 2, '.', '');
            } else {
                $totalAmountArray[] = number_format((float)$sellerPriceArray[$value['magerealorder_id']], 2, '.', ' ');

            }
            /*dhl work ends 24-jan-2019*/

            $sellerPriceArray[$value['magerealorder_id']] = '';
        }
        /*DHL Work 24-jan-2019 ends */ 

        $netTotal = (array_sum($totalAmountArray) - array_sum($totalFeeArray));
        $totalInclVat = number_format(((array_sum($totalAmountArray)-array_sum($totalFeeArray))*19)/100, 2, '.', '');
        $totalToBePaid = number_format($netTotal + $totalInclVat, 2, '.', '');
        $feeVAT = (array_sum($totalFeeArray) * 19) / 100;
        $totalFee = (array_sum($totalFeeArray) + $feeVAT);

        $invoiceData = [];
        $invoiceData['real_order_ids'] = implode(', ', $realOrderIds);
        $invoiceData['amnt_net'] = $netTotal;
        /*DHL Work 24-jan-2019*/        
        $invoiceData['amnt_incl_vat'] = $totalToBePaid; 
        /*DHL Work 24-jan-2019 ends */
        $invoiceData['vat_amnt'] = $totalInclVat;
        /*DHL Work 24-jan-2019*/
        $invoiceData['fees_net'] = array_sum($totalFeeArray) + array_sum($dhlFeesArray);
        /*DHL Work 24-jan-2019 ends */

        $invoiceData['fee_incl_vat'] = $totalFee;
        $invoiceData['vat_fee_ttl'] = $feeVAT;

        $this->loadedSalesListItems->clear()->getSelect()->reset('where');

        return $invoiceData;
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
        $returnArr['status'] = true;
        if($vendorInvoiceSetting == 'weekly') {
            $afterDate = $this->date->gmtDate('Y-m-d', strtotime("-7 days"));
            // $returnArr['status'] = ($lastInvoicedDate < $afterDate);
        } elseif($vendorInvoiceSetting == 'monthly') {
            $monthlyAfterDate = $this->date->gmtDate('Y-m-d', strtotime("-30 days"));
            // $returnArr['status'] = ($lastInvoicedDate < $monthlyAfterDate);
        }
        $returnArr['last'] = $lastInvoicedDate;
        return $returnArr;
    }
}

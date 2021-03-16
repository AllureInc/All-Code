<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\VendorPayments\Block\Invoice;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class View extends \Magento\Framework\View\Element\Template
{

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

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
     * @var \Magento\Framework\Registry
     */
    protected $registryObject;

    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollection;

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
        \Magento\Framework\Registry $registryObject,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        array $data = []
    ) {
        $this->marketplaceHelper = $marketplaceHelper;
        $this->invoiceModel = $invoiceModel;
        $this->date = $date;
        $this->vendorInvoiceColl = $vendorInvoiceColl;
        $this->customerSession = $customerSession;
        $this->salesListModel = $salesListModel;
        $this->helper = $helper;
        $this->registryObject = $registryObject;
        $this->_countryCollection = $countryCollection;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getHelper()
    {
        return $this->helper;
    }

    public function getCanceledOrder($orderId = 0)
    {
        return $this->salesListModel->load($orderId, 'order_id');
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getInvoiceItems()
    {
        $currentInvoiceOrders = $this->getCurrentInvoiceData()->getSaleslistItemIds();
        $itemIds = explode(',', $currentInvoiceOrders);
        $salesListItems = $this->salesListModel->getCollection()
            ->addFieldToFilter('total_amount', ['neq' => 0])
            ->addFieldToFilter('return_request_by_customer', ['neq' => 1])
            // ->addFieldToFilter('is_item_invoiced', ['eq' => 0])
            ->addFieldToFilter('order_item_id',  array('in' => $itemIds));

        return $salesListItems->getData();
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getReturnRequestedItems()
    {
        $returnArray = [];
        $currentInvoiceOrders = $this->getCurrentInvoiceData()->getSaleslistItemIds();
        $itemIds = explode(',', $currentInvoiceOrders);
        $salesListItems = $this->salesListModel->getCollection()
            ->addFieldToFilter('total_amount', ['neq' => 0])
            ->addFieldToFilter('return_request_by_customer', ['eq' => 1])
            ->addFieldToFilter('order_item_id',  array('in' => $itemIds));
        if (count($salesListItems->getData()) > 0) {
            foreach ($salesListItems as $order) {
                $returnArray[] = $order['magerealorder_id'];
            }
        }

        return $returnArray;
    }

    /**
     * Returns marketplace_saleslist entity_ids array
     *
     * @return array
     */
    public function getSalesListItemEntIds($orderItemIds = [])
    {
        $salesListItems = $this->salesListModel->getCollection()
            ->addFieldToFilter('order_item_id',  array('in' => $orderItemIds));

        $saleslistItemIds = array_column($salesListItems->getData(), 'entity_id');
        return $saleslistItemIds;
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getCurrentInvoiceData()
    {
        return $this->registryObject->registry('vendorpayments_invoices_view_vendor');
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getCountries()
    {
        if (!$this->countries) {
            $this->countries = $this->_countryCollection->create()
                ->loadData()
                ->toOptionArray(false);
        }

        return $this->countries;
    }
}

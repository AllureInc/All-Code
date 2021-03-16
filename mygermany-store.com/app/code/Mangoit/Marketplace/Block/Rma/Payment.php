<?php

namespace Mangoit\Marketplace\Block\Rma;

/**
 * Mangoit Marketplace Sellerlist Block.
 */
class Payment extends \Magento\Framework\View\Element\Template
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
     * Core registry.
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

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
        \Webkul\Marketplace\Model\Saleslist $salesListModel,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->marketplaceHelper = $marketplaceHelper;
        $this->invoiceModel = $invoiceModel;
        $this->date = $date;
        $this->vendorInvoiceColl = $vendorInvoiceColl;
        $this->salesListModel = $salesListModel;
        $this->helper = $helper;
        $this->_coreRegistry = $coreRegistry;
        $this->_objectManager = $objectManager;
        $this->_customerSession = $customerSession;
        $this->_storeManager = $storeManager;
        parent::__construct($context, $data);
    }


    /**
     * @return $this
     */
    // protected function _prepareLayout()
    // {
    //     parent::_prepareLayout();

    //     return $this;
    // }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()
        );
    }

    /**
     * Retrieve current order model instance.
     */
    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
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

    /**
     * @return string
     */
    public function getOrderCancellationCharge()
    {
        $order = $this->getOrder();
        $sellerId = $this->getCustomerId();
        $collectionselect = $this->_objectManager->create(
            'Webkul\Marketplace\Model\Saleperpartner'
        )->getCollection()
        ->addFieldToFilter(
            'seller_id',
            $sellerId
        );
        $fixedAmount = $this->getConfigValue('marketplace/general_settings/cancel_order_chrg_fxd');
        $percent = $this->getConfigValue('marketplace/general_settings/cancel_order_chrg_in_p');
        
        if ($collectionselect->getSize() == 1) {
            foreach ($collectionselect as $verifyrow) {
                $autoid = $verifyrow->getEntityId();
            }

            $collectionupdate = $this->_objectManager->get(
                'Webkul\Marketplace\Model\Saleperpartner'
            )->load($autoid);

            $cancelOrderChrgData = json_decode($collectionupdate->getCancelOrderChrgData(), true);
            if (json_last_error() === JSON_ERROR_NONE) {
                $fixedAmount = ($cancelOrderChrgData['fixed'] != '') ? $cancelOrderChrgData['fixed'] : $fixedAmount;
                $percent = ($cancelOrderChrgData['percent'] != '') ? $cancelOrderChrgData['percent'] : $percent;
            }
        }

        // $percentOfTotal = ($order->getBaseGrandTotal() * $percent) / 100;
        $percentOfTotal = ($order->getBaseSubtotalInclTax() * $percent) / 100;
        $totalCharge = $percentOfTotal + $fixedAmount;
        return $totalCharge;
    }

    public function getCustomerId()
    {
        return $this->_customerSession->getCustomerId();
    }
}

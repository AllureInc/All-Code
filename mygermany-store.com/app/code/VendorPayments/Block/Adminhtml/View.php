<?php

namespace Mangoit\VendorPayments\Block\Adminhtml;

use Mangoit\VendorPayments\Model\Paymentfees;
use Mangoit\VendorPayments\Model\Exchangefees;

class View extends \Magento\Backend\Block\Widget\Container
{
	/**
	 * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
	 */
	protected $_countryCollection;

    /**
      * @var \Mangoit\VendorPayments\Model\Paymentfees
      */
    protected $_paymentFeesModel;

    /**
      * @var \Mangoit\VendorPayments\Model\Exchangefees
      */
    protected $_exchangeFeesModel;

    /**
     * @var array
     */
    private $countries;

    /**
     * @var array
     */
    private $exchangeCharges;



    /**
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $salesListModel;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registryObject;

    /**
     * @var Mangoit\VendorPayments\Helper\Data
     */
    protected $helper;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

    public function __construct(
    	\Magento\Backend\Block\Widget\Context $context,
    	\Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        Paymentfees $paymentFeesModel,
        Exchangefees $exchangeFeesModel,

        \Webkul\Marketplace\Model\Saleslist $salesListModel,
        \Magento\Framework\Registry $registryObject,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
    	array $data = []
    ) {
    	$this->_countryCollection = $countryCollection;
        $this->_paymentFeesModel = $paymentFeesModel;
        $this->_exchangeFeesModel = $exchangeFeesModel;

        $this->salesListModel = $salesListModel;
        $this->registryObject = $registryObject;
        $this->helper = $helper;
        $this->marketplaceHelper = $marketplaceHelper;
        parent::__construct($context, $data);
    }

    public function getHelper(){
        return $this->helper;
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
            // ->addFieldToFilter('is_item_invoiced', ['eq' => 0])
            ->addFieldToFilter('order_item_id',  array('in' => $itemIds));

        return $salesListItems->getData();
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getCurrentInvoiceData()
    {
        // echo "<pre>";
        // print_r($this->registryObject->registry('vendorpayments_invoices_view')->getData());
        // // print_r(get_class_methods($this->registryObject));
        // die();
        // $salesListItems = $this->salesListModel->getCollection()
        //     ->addFieldToFilter('total_amount', ['neq' => 0])
        //     // ->addFieldToFilter('is_item_invoiced', ['eq' => 0])
        //     ->addFieldToFilter('order_id',  array('in' => $eligibleOrderIds));

        // $listItemsData = $salesListItems->getData();
        return $this->registryObject->registry('vendorpayments_invoices_view');
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

    /**
     * Returns Fees Collection
     *
     * @return array
     */
    public function getFeesCollectionArray()
    {
        $collection = $this->_paymentFeesModel->getCollection();
        $returnArr = $this->_returnArr;
        foreach ($collection as $pymnt) {
            $paymentTyp = $pymnt->getPaymentMethod();
            if ($paymentTyp == 'paypal' || $paymentTyp == 'credit_card'){
                $origin = ($pymnt->getCounrtyGroup()) ? $pymnt->getCounrtyGroup() : $pymnt->getCardType();

                $returnArr[$paymentTyp][$origin]['cost_per_t']['fixed'] = number_format($pymnt->getCostPerTans(), 2, '.', '');
                $returnArr[$paymentTyp][$origin]['cost_per_t']['in_percent'] = number_format($pymnt->getPercentOfTotalPerTans(), 2, '.', '');

                if($paymentTyp == 'paypal') {
                    $returnArr[$paymentTyp][$origin]['effective_countries'] = explode(',', $pymnt->getEffectiveCountries());
                }
            } elseif ($paymentTyp == 'crypto' || $paymentTyp == 'other') {
                $returnArr[$paymentTyp]['cost_per_t']['in_percent'] = number_format($pymnt->getPercentOfTotalPerTans(), 2, '.', '');
            }
        }
        return $returnArr;
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getExchangeCharges()
    {
        if (!$this->exchangeCharges) {
            $rateArr = $this->_exchangeFeesModel->getCollection()->toArray();
            $this->exchangeCharges = isset($rateArr['items']) ? $rateArr['items'] : [];
        }
        return $this->exchangeCharges;
    }
}

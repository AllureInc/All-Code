<?php

namespace Mangoit\VendorPayments\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\App\ObjectManager;

use MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;


class CustomerInvoice extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    protected $marketplaceHelper;

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * @var SellerProduct
     */
    protected $_sellerProductCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * @var AdapterCsv
     */
    protected $_writer;

    /**
     * @var \Magento\MediaStorage\Model\File\UploaderFactory
     */
    protected $_fileUploader;

    /**
     * @var \Magento\Framework\File\Csv
     */
    protected $_csvReader;


    /**
     * @var \Webkul\Marketplace\Model\Saleslist
     */
    protected $salesListModel;

    /**
     * @var \Webkul\Marketplace\Model\Orders
     */
    protected $mrktplcOrders;

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $mageOrderModel;

    /**
     * @var \Magento\Catalog\Model\Product
     */
    protected $productloader;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTime
     */
    public $date;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $messageManager;

    /**
     * @var \Magento\Sales\Model\Order\Invoice
     */
    protected $mageInvoiceModel;

    /**
     * @var \Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices\Collection
     */
    protected $vendorInvoiceColl;

    /**
     * @var \Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices\Collection
     */
    protected $dataHelper;
    
    /**
     * @var Country defines debitor customer numbers as below
     */
    protected $debitorNumbers = [
            'Abkhazia' => '100000',
            'AF' => '100001',
            'African Union' => '100002',
            'EG' => '100003',
            'AL' => '100004',
            'DZ' => '100005',
            'AD' => '100006',
            'AO' => '100007',
            'AG' => '100008',
            'yyyyyy' => '100009',
            'AR' => '100010',
            'AM' => '100011',
            'AZ' => '100012',
            'ET' => '100013',
            'AU' => '100014',
            'BS' => '100500',
            'BH' => '100501',
            'BD' => '100502',
            'BB' => '100503',
            'BE' => '100504',
            'BZ' => '100505',
            'BJ' => '100506',
            'Nagorno-Karabakh Republic' => '100507',
            'BT' => '100508',
            'BO' => '100509',
            'BA' => '100510',
            'BW' => '100511',
            'BR' => '100512',
            'BN' => '100513',
            'BG' => '100514',
            'BF' => '100515',
            'BI' => '100516',
            'CL' => '200000',
            'CK' => '200001',
            'CR' => '200002',
            'DK' => '200500',
            'DE' => '200501',
            'DM' => '200502',
            'DO' => '200503',
            'DJ' => '200504',
            'EC' => '200800',
            'SV' => '200801',
            'CI' => '200802',
            'Earth' => '200803',
            'ER' => '200804',
            'EE' => '200805',
            'European Union' => '200806',
            'FJ' => '300000',
            'FI' => '300001',
            'FR' => '300002',
            'GA' => '300200',
            'GM' => '300201',
            'GE' => '300202',
            'GH' => '300203',
            'GD' => '300204',
            'GR' => '300205',
            'GT' => '300206',
            'GQ' => '300207',
            'GW' => '300208',
            'GY' => '300209',
            'HT' => '300400',
            'HN' => '300401',
            'HK' => '300402',
            'IN' => '300600',
            'ID' => '300601',
            'IQ' => '300602',
            'IR' => '300603',
            'IE' => '300604',
            'IS' => '300605',
            'IL' => '300606',
            'IT' => '300607',
            'JM' => '300800',
            'JP' => '300801',
            'YE' => '300802',
            'JO' => '300803',
            'KH' => '400000',
            'CM' => '400001',
            'CA' => '400002',
            'CV' => '400003',
            'KZ' => '400004',
            'QA' => '400005',
            'KE' => '400006',
            'KG' => '400007',
            'KI' => '400008',
            'CO' => '400009',
            'KM' => '400010',
            'CG' => '400011',
            'KP' => '400012',
            'KR' => '400013',
            'XK' => '400014',
            'HR' => '400015',
            'CU' => '400016',
            'KW' => '400017',
            'LA' => '400200',
            'LS' => '400201',
            'LV' => '400202',
            'ew' => '400203',
            'LR' => '400204',
            'LY' => '400205',
            'LI' => '400206',
            'LT' => '400207',
            'LU' => '400208',
            'MG' => '400400',
            'MW' => '400401',
            'MY' => '400402',
            'MV' => '400403',
            'ML' => '400404',
            'MT' => '400405',
            'MA' => '400406',
            'MH' => '400407',
            'MR' => '400408',
            'MU' => '400409',
            'MK' => '400410',
            'MX' => '400411',
            'FM' => '400412',
            'MD' => '400413',
            'MC' => '400414',
            'MN' => '400415',
            'ME' => '400416',
            'MZ' => '400417',
            'MM' => '400418',
            'NA' => '400600',
            'NR' => '400601',
            'NP' => '400602',
            'NZ' => '400603',
            'NI' => '400604',
            'NL' => '400605',
            'NE' => '400606',
            'NG' => '400607',
            'NU' => '400608',
            'CY' => '400609',
            'NO' => '400610',
            'OM' => '400800',
            'Organization of American States' => '400801',
            'AT' => '400802',
            'TL' => '400803',
            'PK' => '500000',
            'PS' => '500001',
            'PW' => '500002',
            'PA' => '500003',
            'PG' => '500004',
            'PY' => '500005',
            'PE' => '500006',
            'PH' => '500007',
            'PL' => '500008',
            'PT' => '500009',
            'yyyyy' => '500100',
            'TW' => '500200',
            'RW' => '500201',
            'RO' => '500202',
            'RU' => '500203',
            'SB' => '500400',
            'ZM' => '500401',
            'WS' => '500402',
            'SM' => '500403',
            'ST' => '500404',
            'SA' => '500405',
            'SE' => '500406',
            'CH' => '500407',
            'SN' => '500408',
            'RS' => '500409',
            'SC' => '500410',
            'SL' => '500411',
            'ZW' => '500412',
            'SG' => '500413',
            'SK' => '500414',
            'SI' => '500415',
            'SO' => '500416',
            'Somaliland' => '500417',
            'ES' => '500418',
            'LK' => '500419',
            'KN' => '500420',
            'LC' => '500421',
            'VC' => '500422',
            'ZA' => '500423',
            'SD' => '500424',
            'South Ossetia' => '500425',
            'SS' => '500426',
            'SR' => '500427',
            'SZ' => '500428',
            'SY' => '500429',
            'TJ' => '500600',
            'TZ' => '500601',
            'TH' => '500602',
            'TG' => '500603',
            'TO' => '500604',
            'Transnistria' => '500605',
            'TT' => '500606',
            'TD' => '500607',
            'CZ' => '500608',
            'TN' => '500609',
            'TR' => '500610',
            'TM' => '500611',
            'TV' => '500612',
            'UG' => '500800',
            'UA' => '500801',
            'HU' => '500802',
            'UY' => '500803',
            'UZ' => '500804',
            'VU' => '600000',
            'VA' => '600001',
            'VE' => '600002',
            'Association of Southeast Asian Nations' => '600003',
            'AE' => '600004',
            'US' => '600005',
            'GB' => '600006',
            'VN' => '600007',
            'CN' => '600008',
            'BY' => '600200',
            'EH' => '600201',
            'CF' => '600400',
            'yyyyy' => '600401'
        ];

    /**
     *
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Webkul\Marketplace\Helper\Data $marketplaceHelper
     * @param CollectionFactory $productCollectionFactory
     * @param SellerProduct     $sellerProductCollectionFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param AdapterCsv $writer
     * @param \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory
     * @param \Magento\Framework\File\Csv $csvReader
     *
     */

    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        // CollectionFactory $productCollectionFactory,
        // SellerProduct $sellerProductCollectionFactory,
        // \Magento\Store\Model\StoreManagerInterface $storeManager,
        // \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        // \Magento\Framework\File\Csv $csvReader,

        // \Webkul\Marketplace\Model\Orders $mrktplcOrders,
        // \Magento\Sales\Model\Order $mageOrderModel,
        // \Magento\Framework\Stdlib\DateTime\DateTime $date,
        // \Magento\Catalog\Model\ProductFactory $_productloader,
        // \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        // \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Webkul\Marketplace\Model\Saleslist $salesListModel,
        AdapterCsv $writer,
        Data $dataHelper,
        \Magento\Sales\Model\Order\Invoice $mageInvoiceModel,
        \Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices\Collection $vendorInvoiceColl
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        // $this->_productCollectionFactory = $productCollectionFactory;
        // $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        // $this->_storeManager = $storeManager;
        // $this->_fileUploader = $fileUploaderFactory;
        // $this->_csvReader = $csvReader;

        // $this->mrktplcOrders = $mrktplcOrders;
        // $this->mageOrderModel = $mageOrderModel;
        // $this->date = $date;
        // $this->productloader = $_productloader;
        // $this->invoiceModel = $invoiceModel;
        // $this->messageManager = $messageManager;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->salesListModel = $salesListModel;
        $this->_writer = $writer;
        $this->dataHelper = $dataHelper;
        $this->mageInvoiceModel = $mageInvoiceModel;
        $this->vendorInvoiceColl = $vendorInvoiceColl;
        parent::__construct($context);
    }

    public function getCustomerCSVContent($dateFrom = '', $dateTo = ''){
        $fromDate = date('Y-m-d \0\0\:\0\0\:\0\0', strtotime($dateFrom));
        $toDate = date('Y-m-d \2\3\:\5\9\:\5\9', strtotime($dateTo));

        $mageInvoiceColl = $this->mageInvoiceModel->getCollection()
            ->addFieldToFilter('created_at', ['from' => $fromDate, 'to' => $toDate]);
        $csvData[] = $this->prepareFileHeader();
        foreach ($mageInvoiceColl as $invoice) {
            $order = $invoice->getOrder();
            $buyerName = $order->getCustomerName();
            $buyerEmail = $order->getCustomerEmail();
            $buyerBilling = $order->getAddressById($order->getBillingAddressId());

            $isFirstItemOfOrder = true;
            foreach ($order->getAllVisibleItems() as $key => $value) {

                // 21 = (300*?)/ 100; -- ? = (21*100)/300
                // $taxMultiplied = ($mageInvoice->getBaseTaxAmount() * 100);
                // $itemTaxPercent = ($taxMultiplied > 0) ? round($taxMultiplied / $mageInvoice->getBaseRowTotal()) : 0;
                $itemTaxPercent = (int)$value->getTaxPercent();
                $invoicePrefixNum = $this->getInvoicePrefixNum($invoice->getIncrementId(), $itemTaxPercent);

                $invoiceData = [];
                $invoiceData['Date'] = $invoice->getCreatedAt();
                $invoiceData['Invoice Number'] = '#'.$invoice->getIncrementId();
                $invoiceData['Erlöskonto Ware'] = $invoicePrefixNum;
                $invoiceData['Erlöskonto Versand'] = $this->getRevenueAccountShipping($invoicePrefixNum);
                $invoiceData['Order-Name'] = 'Magento-Sell';
                $invoiceData['Order-Nummer'] = '#'.$order->getIncrementId();
                $invoiceData['Customer Name'] = $buyerName;
                $invoiceData['Customer eMail ID'] = $buyerEmail;
                $invoiceData['Customer Name & eMail ID & Order Number'] = (string)__('%1 %2 (%3)', $buyerName, $buyerEmail, $order->getIncrementId());
                $invoiceData['Customer Number'] = $order->getCustomerId();
                $invoiceData['Country'] = $buyerBilling->getCountryId();
                $invoiceData['Country defines debitor customer numbers as below'] = $this->getDebitorNumber($buyerBilling->getCountryId());
                $invoiceData['VAT %'] = $itemTaxPercent.'%';
                $discountAmount = 0.00;
                if ($isFirstItemOfOrder) {
                    $discountAmount = ($order->getBaseDiscountAmount() > 0) ? $order->getBaseDiscountAmount() : $order->getDiscountAmount();
                    $invoiceData['Turnover Total'] = $value->getBaseRowTotalInclTax() - $discountAmount;
                } else {
                    $invoiceData['Turnover Total'] = $value->getBaseRowTotalInclTax();
                    $discountAmount = 0.00;
                }
                $invoiceData['VAT Amount'] = $value->getBaseTaxAmount();
                $invoiceData['Turnover Net'] = ($value->getBaseRowTotal() - $discountAmount);
                $invoiceData['Item incl VAT'] = $value->getBaseRowTotalInclTax();
                $invoiceData['Shippin incl VAT'] = ($isFirstItemOfOrder) ? $order->getBaseShippingInclTax() : 0;
                $invoiceData['Discount/Surchagre incl VAT'] = $discountAmount;
                // $invoiceData['Discount/Surchagre incl VAT'] = $value->getBaseDiscountAmount();
                $invoiceData['Transaction Code'] = '-';
                $invoiceData['interne Reference-Nummer 1'] = '-';
                $invoiceData['interne Reference-Nummer 2'] = '-';
                
                $csvData[1][] = $invoiceData;
                $isFirstItemOfOrder = false;
            }
        }
        return $this->getCsvFileData($csvData);
    }

    public function getVendorCSVContent($dateFrom = '', $dateTo = ''){
        $fromDate = date('Y-m-d \0\0\:\0\0\:\0\0', strtotime($dateFrom));
        $toDate = date('Y-m-d \2\3\:\5\9\:\5\9', strtotime($dateTo));

        $collection = $this->vendorInvoiceColl->load()
                ->addFieldToFilter('created_at', ['from' => $fromDate, 'to' => $toDate]);

        $salesListItems = $this->salesListModel->getCollection();
        $csvData = [];
        $csvData[] = $this->prepareFileHeader('vendor');
        foreach ($collection->getData() as $invoice) {

            $invcData = $salesListItems->addFieldToFilter('total_amount', ['neq' => 0])
                    ->addFieldToFilter('order_item_id',  array('in' => $invoice['saleslist_item_ids']));
            $sellerPriceArray = $this->dataHelper->getSellerPriceArray($invcData);

            foreach ($invcData as $value) {
                $totalFee = $netTotal = $totalAmount = 0;
                $totalAmount = ($value['total_amount'] + (float)$sellerPriceArray[$value['magerealorder_id']]);
                $sellerPriceArray[$value['magerealorder_id']] = 0;
                $totalFee = $value['total_commission'] + $value['mits_payment_fee_amount'] + $value['mits_exchange_rate_amount'];
                $netTotal = ($totalAmount - $totalFee);
                $totalInclVat = ($netTotal * 19) / 100;

                // 21 = (300*?)/ 100; -- ? = (21*100)/300
                $itemTaxPercent = ($value['total_tax'] * 100) / $value['total_amount'];

                $totalToBePaid = number_format($netTotal + $totalInclVat, 2, '.', '');
                $feeVAT = ($totalFee * 19) / 100;
                $totalFeeWithVAT = ($totalFee + $feeVAT);

                $sellerData = $this->marketplaceHelper->getSellerDataBySellerId($invoice['seller_id'])->getData();
                $sellerData = isset($sellerData[0]) ? $sellerData[0] : $sellerData;

                $invoiceData = [];
                $invoiceData['Date'] = $invoice['created_at'];
                $invoiceData['Invoice / Credit Number'] = $invoice['invoice_number'];
                $invoiceData['Vendor Costs Kostenkonto'] = $this->getVendorCostsKostenkonto($itemTaxPercent);
                $invoiceData['Erlöskonto'] = 45790;
                $invoiceData['Order-Name'] = 'Magento-Sell';
                $invoiceData['Order-Nummer'] = $value['magerealorder_id'];//$invoice->getOrderIds();
                $invoiceData['Vendor Name'] = isset($sellerData['name']) ? $sellerData['name'] : 'NA';
                $invoiceData['Vendor / Creditor Number'] = $invoice['seller_id'];
                $invoiceData['Country'] = isset($sellerData['billing_country_id'])?$sellerData['billing_country_id']:'NA';
                $invoiceData['Amount net'] = $netTotal;
                $invoiceData['Amount incl. VAT'] = $totalToBePaid;
                $invoiceData['VAT Amount'] = $totalInclVat;
                $invoiceData['Fees net'] = $totalFee;
                $invoiceData['Fees Total (cumulated) incl. VAT'] = $totalFeeWithVAT;
                $invoiceData['VAT of Fees Total'] = $feeVAT;
                $invoiceData['VAT %'] = $itemTaxPercent.'%';
                $invoiceData['Transaktionscode'] = '-';
                $invoiceData['interne Reference-Nummer 1'] = '-';
                $invoiceData['interne Reference-Nummer 2'] = '-';

                $csvData[1][] = $invoiceData;

                $salesListItems->clear()->getSelect()->reset('where');
            }

        }
        return $this->getCsvFileData($csvData);
    }

    private function getCsvFileData($dataArray = [])
    {
        if (count($dataArray)) {
            $writer = $this->_writer;
            $writer->setHeaderCols($dataArray[0]);
            if(isset($dataArray[1])) {
                foreach ($dataArray[1] as $dataRow) {
                    if (!empty($dataRow)) {
                        $writer->writeRow($dataRow);
                    }
                }
            }
            return $writer->getContents();
        }
    }

    /**
     * Prepare File CSV Header
     *
     * @return array
     */
    public function prepareFileHeader($headerTyp = 'customer')
    {
        $wholeData = [];
        if($headerTyp == 'customer'){
            $wholeData[] = 'Date';
            $wholeData[] = 'Invoice Number';
            $wholeData[] = 'Erlöskonto Ware';
            $wholeData[] = 'Erlöskonto Versand';
            $wholeData[] = 'Order-Name';
            $wholeData[] = 'Order-Nummer';
            $wholeData[] = 'Customer Name';
            $wholeData[] = 'Customer eMail ID';
            $wholeData[] = 'Customer Name & eMail ID & Order Number';
            $wholeData[] = 'Customer Number';
            $wholeData[] = 'Country';
            $wholeData[] = 'Country defines debitor customer numbers as below';
            $wholeData[] = 'VAT %';
            $wholeData[] = 'Turnover Total';
            $wholeData[] = 'VAT Amount';
            $wholeData[] = 'Turnover Net';
            $wholeData[] = 'Item incl VAT';
            $wholeData[] = 'Shippin incl VAT';
            $wholeData[] = 'Discount/Surchagre incl VAT';
            $wholeData[] = 'Transaction Code';
            $wholeData[] = 'interne Reference-Nummer 1';
            $wholeData[] = 'interne Reference-Nummer 2';
        } elseif($headerTyp == 'vendor') {
            $wholeData[] = 'Date';
            $wholeData[] = 'Invoice / Credit Number';
            $wholeData[] = 'Vendor Costs Kostenkonto';
            $wholeData[] = 'Erlöskonto';
            $wholeData[] = 'Order-Name';
            $wholeData[] = 'Order-Nummer';
            $wholeData[] = 'Vendor Name';
            $wholeData[] = 'Vendor / Creditor Number';
            $wholeData[] = 'Country';
            $wholeData[] = 'Amount net';
            $wholeData[] = 'Amount incl. VAT';
            $wholeData[] = 'VAT Amount';
            $wholeData[] = 'Fees net';
            $wholeData[] = 'Fees Total (cumulated) incl. VAT';
            $wholeData[] = 'VAT of Fees Total';
            $wholeData[] = 'VAT %';
            $wholeData[] = 'Transaktionscode';
            $wholeData[] = 'interne Reference-Nummer 1';
            $wholeData[] = 'interne Reference-Nummer 2';
        }

        return $wholeData;
    }

    /**
     * Prepare Invoice Prefix Number by Increment ID
     *
     * @param string $incrementId
     *
     * @return string
     */
    public function getInvoicePrefixNum($incrementId = 0, $taxPercent = '19')
    {
        // For invoice opCD_1000000000
        $opCDregex = "/^opCD_[0-9]{0,}$/";

        // For invoice opCE_1000000000
        $opCEregex = "/^opCE_[0-9]{0,}$/";

        // For invoice ogCD_1000000000
        $ogCDregex = "/^ogCD_[0-9]{0,}$/";

        // For invoice ogCE_1000000000
        $ogCEregex = "/^ogCE_[0-9]{0,}$/";

        /*
         * opCD = 41201
         * opCE19% = 43154
         * opCE7% = 43104
         * ogCD = 41207
         * ogCE oVAT = 41250
         */

        if (preg_match($opCDregex, $incrementId)) {
            return "41201";
        }

        if (preg_match($opCEregex, $incrementId)) {
            // Check tax percentage as well
            if ($taxPercent == '7') {
                return "43104";
            } else {
                return "43154";
            }
        }

        if (preg_match($ogCDregex, $incrementId)) {
            return "41207";
        }

        if (preg_match($ogCEregex, $incrementId)) {
            return "41250";
        }
    }

    /**
     * Get Erlöskonto Versand invoice prefix number by Prefix Number
     *
     * @param string $prefixNumer
     *
     * @return string
     */
    public function getRevenueAccountShipping($prefixNumer = 0)
    {
        /*
         * 41201 => 41503
         * 43154 => 44006
         * 43104 => does not exist,0
         * 41207 => 43389
         * 41250 => 43392
         */

        $columnDee = [
                '41201' => '41503',
                '43154' => '44006',
                '43104' => 'does not exist,0',
                '41207' => '43389',
                '41250' => '43392'
            ];

        return (isset($columnDee[$prefixNumer])) ? $columnDee[$prefixNumer] : '0';
    }

    /**
     * Get Vendor Costs Kostenkonto by Percentage
     *
     * @param string $prefixNumer
     *
     * @return string
     */
    public function getVendorCostsKostenkonto($percentage = 0)
    {
        /*
         * 19% => 54000
         * 7% => 53000
         * 0% => 52000
         */

        $vendorCosts = [
            '19' => '54000',
            '7' => '53000',
            '0' => '52000'
        ];

        return (isset($vendorCosts[$percentage])) ? $vendorCosts[$percentage] : '0';
    }

    /**
     * Get debitor customer number
     *
     * @param string $prefixNumer
     *
     * @return string
     */
    public function getDebitorNumber($countryCode = '--')
    {
        return (isset($this->debitorNumbers[$countryCode])) ? $this->debitorNumbers[$countryCode] : $countryCode;
    }
}

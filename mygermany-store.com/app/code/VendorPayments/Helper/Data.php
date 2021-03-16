<?php

namespace Mangoit\VendorPayments\Helper;

use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Product\CollectionFactory as SellerProduct;
use Magento\ImportExport\Model\Export\Adapter\Csv as AdapterCsv;
use Magento\Framework\App\ObjectManager;

use MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface as ContainerInterface;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
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
     * @var \MangoIt\EmailAttachments\Model\AttachmentFactory
     */
    protected $attachmentFactory;

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
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        CollectionFactory $productCollectionFactory,
        SellerProduct $sellerProductCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        AdapterCsv $writer,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        \Magento\Framework\File\Csv $csvReader,

        \Webkul\Marketplace\Model\Saleslist $salesListModel,
        \Webkul\Marketplace\Model\Orders $mrktplcOrders,
        \Magento\Sales\Model\Order $mageOrderModel,
        \Magento\Framework\Stdlib\DateTime\DateTime $date,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \MangoIt\EmailAttachments\Model\AttachmentFactory $attachmentFactory
    ) {
        $this->scopeConfig = $context->getScopeConfig();
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_productCollectionFactory = $productCollectionFactory;
        $this->_sellerProductCollectionFactory = $sellerProductCollectionFactory;
        $this->_storeManager = $storeManager;
        $this->_writer = $writer;
        $this->_fileUploader = $fileUploaderFactory;
        $this->_csvReader = $csvReader;

        $this->salesListModel = $salesListModel;
        $this->mrktplcOrders = $mrktplcOrders;
        $this->mageOrderModel = $mageOrderModel;
        $this->date = $date;
        $this->productloader = $_productloader;
        $this->invoiceModel = $invoiceModel;
        $this->messageManager = $messageManager;
        $this->attachmentFactory = $attachmentFactory;
        parent::__construct($context);
    }

    /**
     * Return store configuration value.
     *
     * @param string $path
     * @param int    $storeId
     *
     * @return mixed
     */
    public function getConfigValue($path, $storeId)
    {
        return $this->scopeConfig->getValue(
            $path,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Return store.
     *
     * @return Store
     */
    public function getStore()
    {
        return $this->_storeManager->getStore();
    }

    /**
     * Return template id.
     *
     * @return mixed
     */
    public function getTemplateId($xmlPath)
    {
        return $this->getConfigValue($xmlPath, $this->getStore()->getStoreId());
    }

    /**
     * Obtain a brand constant from a PAN 
     *
     * @param type $pan               Credit card number
     * @param type $include_sub_types Include detection of sub visa brands
     * @return string
     */
    public static function getCardBrand($pan, $include_sub_types = false)
    {
        //maximum length is not fixed now, there are growing number of CCs has more numbers in length, limiting can give false negatives atm

        //these regexps accept not whole cc numbers too
        //visa        
        $visa_regex = "/^4[0-9]{0,}$/";
        $vpreca_regex = "/^428485[0-9]{0,}$/";
        $postepay_regex = "/^(402360|402361|403035|417631|529948){0,}$/";
        $cartasi_regex = "/^(432917|432930|453998)[0-9]{0,}$/";
        $entropay_regex = "/^(406742|410162|431380|459061|533844|522093)[0-9]{0,}$/";
        $o2money_regex = "/^(422793|475743)[0-9]{0,}$/";

        // MasterCard
        $mastercard_regex = "/^(5[1-5]|222[1-9]|22[3-9]|2[3-6]|27[01]|2720)[0-9]{0,}$/";
        $maestro_regex = "/^(5[06789]|6)[0-9]{0,}$/"; 
        $kukuruza_regex = "/^525477[0-9]{0,}$/";
        $yunacard_regex = "/^541275[0-9]{0,}$/";

        // American Express
        $amex_regex = "/^3[47][0-9]{0,}$/";

        // Diners Club
        $diners_regex = "/^3(?:0[0-59]{1}|[689])[0-9]{0,}$/";

        //Discover
        $discover_regex = "/^(6011|65|64[4-9]|62212[6-9]|6221[3-9]|622[2-8]|6229[01]|62292[0-5])[0-9]{0,}$/";

        //JCB
        $jcb_regex = "/^(?:2131|1800|35)[0-9]{0,}$/";

        //ordering matter in detection, otherwise can give false results in rare cases
        if (preg_match($jcb_regex, $pan)) {
            return "jcb";
        }

        if (preg_match($amex_regex, $pan)) {
            return "amex";
        }

        if (preg_match($diners_regex, $pan)) {
            return "diners";
        }

        //sub visa/mastercard cards
        if ($include_sub_types) {
            if (preg_match($vpreca_regex, $pan)) {
                return "v-preca";
            }
            if (preg_match($postepay_regex, $pan)) {
                return "postepay";
            }
            if (preg_match($cartasi_regex, $pan)) {
                return "cartasi";
            }
            if (preg_match($entropay_regex, $pan)) {
                return "entropay";
            }
            if (preg_match($o2money_regex, $pan)) {
                return "o2money";
            }
            if (preg_match($kukuruza_regex, $pan)) {
                return "kukuruza";
            }
            if (preg_match($yunacard_regex, $pan)) {
                return "yunacard";
            }
        }

        if (preg_match($visa_regex, $pan)) {
            return "visa";
        }

        if (preg_match($mastercard_regex, $pan)) {
            /*
             * Returning maestro typ for mastercard as well,
             * as mastercard typ was not included in the context.
             */
            return "maestro";   
            // return "mastercard";
        }

        if (preg_match($discover_regex, $pan)) {
            return "discover";
        }

        if (preg_match($maestro_regex, $pan)) {
            if ($pan[0] == '5') { //started 5 must be mastercard
                /*
                 * Returning maestro typ for mastercard as well,
                 * as mastercard typ was not included in the context.
                 */
                return "maestro";
                // return "mastercard";
            }

            return "maestro"; //maestro is all 60-69 which is not something else, thats why this condition in the end
        }

        return "unknown"; //unknown for this system
    }

    public function attachContent($content, $pdfFilename, $mimeType, ContainerInterface $attachmentContainer)
    {
        $attachment = $this->attachmentFactory->create(
            [
                'content'  => $content,
                'mimeType' => $mimeType,
                'fileName' => $pdfFilename
            ]
        );
        $attachmentContainer->addAttachment($attachment);
    }

    public function attachPdf($pdfString, $pdfFilename, ContainerInterface $attachmentContainer)
    {
        $this->attachContent($pdfString, $pdfFilename, 'application/pdf', $attachmentContainer);
    }

    public function attachCsv($csvString, $csvFilename, ContainerInterface $attachmentContainer)
    {
        $this->attachContent($csvString, $csvFilename, 'text/csv', $attachmentContainer);
    }

    public function getSellerPriceArray($dataArray = [])
    {
        $productLoaderObj = $this->productloader->create();
        $sellerPriceArray = array();
        foreach ($dataArray as $perRow){
           // $productObj = $this->_productloader->create()->load($item->getProductId());
            $product = $productLoaderObj->load($perRow['mageproduct_id']);
            // $mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id');

           $sellerId = $perRow['magerealorder_id'];
           // $sellerId = $this->marketplaceHelper->getCustomerId();
           $shippingCost = $product->getShippingPriceToMygmbh();
           if (array_key_exists($sellerId,$sellerPriceArray))
            {
                if ($sellerPriceArray[$sellerId] > $shippingCost || ($sellerPriceArray[$sellerId] == $shippingCost)) {
                    continue;
                } else if($sellerPriceArray[$sellerId] < $shippingCost) {
                    $sellerPriceArray[$sellerId] = $shippingCost;
                } 
            } else {
                $sellerPriceArray[$sellerId] = $shippingCost;
            }
        }
        return $sellerPriceArray;
    }

    public function getOrderPdfHtmlContent(){

        $invoiceItemsData = $this->getSellerOrdersData();
        $invoicedOrderItemIds = array_column($invoiceItemsData, 'order_item_id');
        // print_r($invoiceItemsData);
        // print_r($this->getPdfHtml($invoiceItemsData));
        // die();
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($this->getPdfHtml($invoiceItemsData));
        // $mpdf->SetDisplayMode('fullpage');
        // $mpdf->output('/var/www/html/mygermany/testggggg.pdf', "F");
        $pdfString = $mpdf->output('', "S");

        // return ['str' => $pdfString, 'invoiced_order_ids' => $invoicedOrderItemIds];
        return ['str' => $pdfString, 'invoiced_order_item_ids' => $invoicedOrderItemIds];
    }

    public function getSellerOrdersData(){
        // echo "<pre>";
        if($this->marketplaceHelper->isSeller()) {

            try {
                $sellerId = $this->marketplaceHelper->getCustomerId();
                $allOrders = $this->mrktplcOrders->getCollection()
                    // ->addFieldToFilter('order_id', $lastOrderId)
                    ->addFieldToSelect('order_id')
                    ->addFieldToFilter('seller_id', $sellerId);

                $mageOrders = array_column($allOrders->getData(), 'order_id');
                if(!empty($mageOrders)) {
                    $daysAllwInvcIn = $this->getConfigValue(
                            'marketplace/general_settings/allow_invoice_in',
                            $this->getStore()->getStoreId()
                        );

                    // $afterDate = date('Y-m-d', strtotime("-$daysAllwInvcIn days"));
                    $afterDate = $this->date->gmtDate('Y-m-d', strtotime("-$daysAllwInvcIn days"));
                    // print_r($afterDate);
                    $eligibleOrders = $this->mageOrderModel->getCollection()
                        // ->addAttributeToFilter('customer_id', $customer_id)
                        // ->addFieldToSelect('entity_id')
                        ->addAttributeToFilter('entity_id',  ['in' => $mageOrders])
                        ->addAttributeToFilter('state', ['in' => ['complete','order_verified','received','closed']])
                        ->addAttributeToFilter('created_at', ['gteq'  => $afterDate]);

                    // print_r($eligibleOrders->getData());
                    $eligibleOrderIds = array_column($eligibleOrders->getData(), 'entity_id');
                    
                    $salesListItems = $this->salesListModel->getCollection()
                        ->addFieldToFilter('total_amount', ['neq' => 0])
                        ->addFieldToFilter('is_item_invoiced', ['eq' => 0])
                        ->addFieldToFilter('order_id',  array('in' => $eligibleOrderIds))
                        ->addFieldToFilter('seller_id', $sellerId);

                    $listItemsData = $salesListItems->getData();

                    $saleslistItemIds = array_column($listItemsData, 'order_item_id');

                    // $saleslistItemIds = array_column($listItemsData, 'entity_id');
                    $orderIds = array_unique(array_column($listItemsData, 'order_id'));

                    // print_r(get_class_methods($salesListItems));
                    // print_r($salesListItems->getData());
                    // print_r($saleslistItemIds);
                    if(!empty($saleslistItemIds)) {
                        $model = $this->invoiceModel;
                        $model->setSellerId($sellerId);
                        $model->setSaleslistItemIds(implode(',', $saleslistItemIds));
                        $model->setOrderIds(implode(',', $orderIds));
                        $invoiceNumber = $this->date->gmtDate('y').'-'.$sellerId;
                        $model->setInvoiceNumber($invoiceNumber);
                        $model->setCreatedAt($this->date->gmtDate());
                        $id = $model->save()->getId();

                        if($id){
                            $model = $this->invoiceModel->load($id);
                            $invoiceNumber = $this->date->gmtDate('y').'-'.$sellerId.'-'.$id;
                            $model->setInvoiceNumber($invoiceNumber);
                            $model->save();
                            $salesListItems->walk([$this, 'updateIsInvoiced_callback']);
                        }
                    }
                    // print_r($listItemsData);die;
                    return $listItemsData;
                }
            } catch (\Exception $e) {
                // print_r($e->getMessage());
                // die('hhhh');
                $this->messageManager->addError($e->getMessage());
            }

        }
        return [];
    }

    public function updateIsInvoiced_callback($item){
        $item->setIsItemInvoiced(1);
        $item->save();
    }

    private function getPdfHtml($dataArray = [], $sellerId = '')
    {
        $om = ObjectManager::getInstance();
        $imageObj = $om->get('\Magento\Theme\Block\Html\Header\Logo');
        $image = $imageObj->getLogoSrc();

        $tableHeads = '';
        $tableContent = '';
        $headings = [
                '&nbsp;' => 3,
                'Betrag/Amount' => 2,
                'Gebühren/Fees' => 3
            ];
        $subHeadings = [
                'Bestelldatum/ Order Date',
                'Artikelname/ Product Name',
                'Bestellnummer/ Order ID',
                'Bestellung/ Order',
                'Versandkosten/ Shipping Costs',
                'Verkaufsgebühr/ Commission',
                'Bezahldienstleister/ Payment Provider',
                'Währungsumrechnung/ Exchange Fee'
            ];

        $tableHeads .= '<tr>';
        foreach ($headings as $headLable => $colSpan) {
            $tableHeads .= '<th colspan="'.$colSpan.'"><span>'.__($headLable).'</span></th>';
        }
        $tableHeads .= '</tr><tr>';
        foreach ($subHeadings as $headColumn) {
            $tableHeads .= '<th><span>'.__($headColumn).'</span></th>';
        }
        $tableHeads .= '</tr>';

        $sellerPriceArray = $this->getSellerPriceArray($dataArray);

        if($sellerId == '') {
            $sellerData = $this->marketplaceHelper->getSeller();
            $customerObj = $this->marketplaceHelper->getCustomer();
            $sellerName = $customerObj->getName();
        } else {
            $sellerData = $this->marketplaceHelper->getSellerDataBySellerId($sellerId)->getData();
            $sellerData = $sellerData[0];
            $sellerName = $sellerData['name'];
        }
        $currency = $this->getStore()->getCurrentCurrencyCode();

        $totalAmountArray = $totalFeeArray = [];
        foreach ($dataArray as $perRow) {
            // $rowData = array_merge($perRow, array_values($dummyColumns));
            $tableContent .= '<tr>';
            $tableContent .= '<td>'.$this->date->gmtDate('d.m.Y', $perRow['created_at']).'</td>';
            $tableContent .= '<td>'.$perRow['magepro_name'].'</td>';
            $tableContent .= '<td>'.$perRow['magerealorder_id'].'</td>';
            $tableContent .= '<td>'.number_format($perRow['total_amount'], 2, '.', '').' '.$currency.'</td>';
            // var_dump((float)$sellerPriceArray[$perRow['magerealorder_id']]);
            $tableContent .= '<td>'.number_format((float)$sellerPriceArray[$perRow['magerealorder_id']], 2, '.', ' ').' '.$currency.'</td>';
            $tableContent .= '<td>'.number_format($perRow['total_commission'], 2, '.', '').' '.$currency.'</td>';
            $tableContent .= '<td>'.number_format($perRow['mits_payment_fee_amount'], 2, '.', '').' '.$currency.'</td>';
            $tableContent .= '<td>'.number_format($perRow['mits_exchange_rate_amount'], 2, '.', '').' '.$currency.'</td>';

            $sellerPriceArray[$perRow['magerealorder_id']] = '';

            $totalAmountArray[] = number_format($perRow['total_amount'], 2, '.', '');
            $totalAmountArray[] = number_format((float)$sellerPriceArray[$perRow['magerealorder_id']], 2, '.', ' ');
            $totalFeeArray[] = number_format($perRow['total_commission'], 2, '.', '');
            $totalFeeArray[] = number_format($perRow['mits_payment_fee_amount'], 2, '.', '');
            $totalFeeArray[] = number_format($perRow['mits_exchange_rate_amount'], 2, '.', '');
            $tableContent .= '</tr>';
        }


        $model = $this->invoiceModel->load(implode(',', array_column($dataArray, 'magerealorder_id')), 'order_ids');
        $invoiceNumber = $model->getInvoiceNumber();
        $createdAt = $model->getCreatedAt();

        $netTotal = (array_sum($totalAmountArray) - array_sum($totalFeeArray));
        $totalInclVat = number_format(((array_sum($totalAmountArray)-array_sum($totalFeeArray))*19)/100, 2, '.', '');
        $totalToBePaid = number_format($netTotal + $totalInclVat, 2, '.', '');

        $date = $this->date->gmtDate('d.m.Y', $createdAt);

        $finalHtml = '
            <!DOCTYPE html>
            <html>
            <head>
            <title>'.__('Credit Invoice').'</title>
            <meta name="viewport" content="width=device-width, initial-scale=1">
            <meta http-equiv="content-type" content="text/html; charset=utf-8">
            <meta name="keywords" content="">
            </head>
            <body style="margin: auto; background:#FFF;font-family:Arial">
            <table width="1280px" height="100%" border="2px" 
                cellspacing="5" cellpadding="0" align="center" style="margin:auto; background:#fff;">
                <tr>
                    <td style="width: 100%">
                        <table style="width: 95%;margin:  auto;">
                            <tr>
                                <td>
                                    <table style="width: 100%">
                                        <tbody>
                                            <tr>
                                                <td width="50%">&nbsp;</td>
                                                <td width="50%" style="text-align: right;">
                                                    <span style="width:300px;display:inline-block;">
                                                       <img src = "' . $image . '" width="300px"/>
                                                    </span><br/>
                                                </td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                            <tr>
                               <td><br/></td>
                            </tr>
                            <tr>
                                <td>
                                    <table style="width: 100%">
                                        <tbody>
                                            <tr>
                                                <td style="text-align: left;">
                                                    <div style="color: grey;margin-bottom: 20px;">Gutschriftadresse</div>
                                                    <br/> 
                                                    <div>
                                                        <div>
                                                            <span>
                                                                <strong>'.$sellerData['shop_title'].'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.$sellerName.'</span></div>
                                                        <div><span>'.$sellerData['company_locality'].'</span></div>
                                                        <div><span>'.__('Deutschland').'</span></div>
                                                        <br/>
                                                        <div><span>'.__('SteuerNr %1', $sellerData['taxvat']).'</span></div>
                                                        <div><span>'.__('USTNr %1', $sellerData['taxvat']).'</span></div>
                                                        <div><span>'.__('Regisitrierungsnummer %1', $sellerData['seller_id']).'</span></div>
                                                        <div><span>'.__('Telefonnummer %1', $sellerData['contact_number']).'</span></div>
                                                    </div>
                                                </td>
                                                <td style="text-align: right;">
                                                    <div>
                                                        <div>
                                                            <span>
                                                                <strong>'.__('myGermany GmbH').'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.__('Nordstrasse 5').'</span></div>
                                                        <div><span>'.__('99427 Weimar').'</span></div>
                                                        <div><span>'.__('Deutschland').'</span></div>
                                                        <br/>
                                                        <div>
                                                            <span>
                                                                <strong>'.__('Steuernummer').'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.__('162/114/05590').'</span></div>
                                                        <br/>
                                                        <div>
                                                            <span>
                                                                <strong>'.__('Umsatzsteuernummer').'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.__('DE293153410').'</span></div>
                                                        <div style="width: 100%;display: inline-block;">
                                                            <span style="
                                                                width: 180px;
                                                                text-align:  right;
                                                                float:  right;
                                                            ">.......................................................</span>
                                                        </div>
                                                        <br/>
                                                        <div>
                                                            <span>
                                                                <strong>'.__('Rechnungsnummer').'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.$invoiceNumber.'</span></div>
                                                        <br/>
                                                        <div>
                                                            <span>
                                                                <strong>'.__('Kundennummer').'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.$sellerData['seller_id'].'</span></div>
                                                        <br/>
                                                        <div>
                                                            <span>
                                                                <strong>'.__('Ausstellungsdatum').'</strong>
                                                            </span>
                                                        </div>
                                                        <div><span>'.$date.'</span></div>
                                                    </div>
                                                </td>
                                            </tr>
                                            <tr>
                                                <td width="50%">
                                                    <div style="width:95%;margin:30px auto;">
                                                        <span style="font-size: 20px;">
                                                            <strong>'.__('GUTSCHRIFT').'</strong>
                                                        </span>
                                                    </div>
                                                </td>
                                                <td width="50%"></td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </td>
                            </tr>
                        </table>
                        <br/>
                        <table style="width:95%;margin:auto;" cellpadding="5" cellspacing="0" border="1">
                            <thead>
                                '.$tableHeads.'
                            </thead>
                            <tbody>'.$tableContent.'</tbody>
                        </table>
                        <table style="width:95%;margin:25px auto;">
                            <tbody>
                                <tr>
                                    <td width="70%">&nbsp;</td>
                                    <td width="30%" style="float: right;">
                                        <table style="width: 95%">
                                            <tbody>
                                                <tr>
                                                    <td style="text-align: left;">'.__('Gesamt Betrag / Total Amount').'</td>
                                                    <td style="text-align: right;">'.array_sum($totalAmountArray).' '.$currency.'</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: left;">'.__('Gesamt Gebühren / Total Fees').'</td>
                                                    <td style="text-align: right;">'.array_sum($totalFeeArray).' '.$currency.'</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: left;">'.__('Gesamtsumme (netto) / Total (net)').'</td>
                                                    <td style="text-align: right;">'.$netTotal.' '.$currency.'</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: left;">'.__('zzgl. 19% USt. / incl. VAT').'</td>
                                                    <td style="text-align: right;">'.$totalInclVat.' '.$currency.'</td>
                                                </tr>
                                                <tr>
                                                    <td style="text-align: left;">'.__('Gesamtsumme / Total').'</td>
                                                    <td style="text-align: right;">'.$totalToBePaid.' '.$currency.'</td>
                                                </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                        <table style="width:95%;margin:25px auto;">
                            <tbody>
                                <tr>
                                    <td width="70%">
                                        <div>
                                            <span>Die Gebühren wurden von Ihrem Rechnungsbetrag abgezogen und am '.$date.' auf Ihr Konto überwiesen. Einwände gegen diese Abrechnung bitte innerhalb von 14 Tagen an haendler@mygermany.com</span>
                                        </div>
                                        <div>&nbsp;</div>
                                        <div>
                                            <span>Bei Fragen kontaktieren Sie bitte unser Kundencenter +49 162 435 20 81.</span>
                                        </div>
                                        <div>&nbsp;</div>
                                        <div>
                                            <span>Vielen Dank und mir freundlichen Grüssen,</span>
                                            <div><strong>myGermany GmbH.</strong></div>
                                        </div></td>
                                    <td width="30%" style="float: right;">
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </td>
                </tr>
            </table>
            </body>
            </html>';

        return $finalHtml;
    }

    // public function getPdfDownload(){
    //     $mpdf = new \Mpdf\Mpdf();
    //     $mpdf->WriteHTML($this->getPdfHtml($this->getSellerOrdersData(true)));
    //     $mpdf->output('credit_invoice.pdf', "D");
    // }

    public function downloadPdfAction($itemIds = [], $sellerId = 0){
        $mpdf = new \Mpdf\Mpdf();
        $mpdf->WriteHTML($this->getPdfHtml($this->getInvoiceItemsByOrderIds($itemIds), $sellerId));
        $mpdf->output('credit_invoice.pdf', "D");
    }

    public function getInvoiceItemsByOrderIds($itemIds)
    {
        $salesListItems = $this->salesListModel->getCollection()
            ->addFieldToFilter('total_amount', ['neq' => 0])
            // ->addFieldToFilter('is_item_invoiced', ['eq' => 0])
            ->addFieldToFilter('order_item_id',  array('in' => $itemIds));

        return $salesListItems->getData();
    }

    public function getInvoiceNumber($itemIds)
    {
        $salesListItems = $this->salesListModel->getCollection()
            ->addFieldToFilter('total_amount', ['neq' => 0])
            // ->addFieldToFilter('is_item_invoiced', ['eq' => 0])
            ->addFieldToFilter('order_item_id',  array('in' => $itemIds));

        return $salesListItems->getData();
    }
}

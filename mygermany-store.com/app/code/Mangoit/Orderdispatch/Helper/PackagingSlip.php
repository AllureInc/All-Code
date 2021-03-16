<?php
/**
 * Copyright © 2018 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Orderdispatch\Helper;

use Magento\Framework\App\Filesystem\DirectoryList;

class PackagingSlip extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $_customer;
    protected $_scopeConfig;
    protected $attachmentContainer;
    protected $_transportBuilder;
    protected $_helper;
    protected $logger;
    protected $_mediaDirectory;
    protected $reader;
    protected $_marketPlaceHelper;
    protected $_orderHelper;
    protected $_misHelper;
    protected $_printslip;
    protected $_downloader;
    protected $_urlInterface;
    protected $barCodeHelper;

    const XML_PATH_EMAIL_RECIPIENT = 'marketplace/email/package_slip_notification';
    
    public function __construct (
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \MangoIt\EmailAttachments\Model\Api\AttachmentContainerInterface $attachmentContainer,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Webkul\Marketplace\Helper\Data $marketPlaceHelper,
        \Webkul\Marketplace\Helper\Orders $orderHelper,
        \Mangoit\Orderdispatch\Helper\Data $misHelper,
        \Mangoit\Orderdispatch\Block\Printslip $printslip,
        \Magento\Framework\App\Response\Http\FileFactory $fileFactory,
        \Magento\Framework\UrlInterface $urlInterface, 
        \Magento\Framework\App\Helper\Context $context,
        \Mangoit\Orderdispatch\Helper\BarCodes $barCodeHelper

    ) {   
        $this->_mediaDirectory = $filesystem->getDirectoryWrite(\Magento\Framework\App\Filesystem\DirectoryList::MEDIA);
        $this->_customer = $customerRepositoryInterface;
        $this->_scopeConfig = $scopeConfig;
        $this->attachmentContainer = $attachmentContainer;
        $this->_transportBuilder = $transportBuilder;
        $this->_helper = $helper;
        $this->logger = $logger;
        $this->reader = $reader;
        $this->_marketPlaceHelper = $marketPlaceHelper;
        $this->_orderHelper = $orderHelper;
        $this->_misHelper = $misHelper;
        $this->_printslip = $printslip;
        $this->_downloader =  $fileFactory;
        $this->_urlInterface = $urlInterface;
        $this->_barCodeHelper = $barCodeHelper;
        parent::__construct($context);
    }

    public function getAdminDetails()
    {
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $data['email'] = $this->_scopeConfig->getValue('trans_email/ident_general/email', $storeScope);
        $data['name'] = $this->_scopeConfig->getValue('trans_email/ident_general/name', $storeScope);
        return $data;
    }

    public function getEmailTemplate($store_id, $method)
    {
        /*$emailTemplate = $this->_helper->getTemplateId('marketplace/email/package_slip_notification', $store_id);*/
        /*$emailTemplate = $this->_scopeConfig->getValue('marketplace/email/package_slip_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);*/
        if ($method == 'DHL') {
            $emailTemplate = $this->_scopeConfig->getValue('marketplace/email/dhl_package_slip_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
        } else {
            $emailTemplate = $this->_scopeConfig->getValue('marketplace/email/package_slip_notification', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store_id);
        }
        
        return $emailTemplate;
    }

    public function generatePdfSlip($data, $method)
    {
        $html = $data['html'];
        $vendor_name = $data['vendor_name'];
        $orderid = $data['orderid'];
        $vendorId = $data['vendorId'];
        $tracking_id = $data['tracking_id'];

        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($vendorId);
        $store_id = $customer->getData('store_id');
        $directory = $objectManager->get('\Magento\Framework\Filesystem\DirectoryList');
        $rootPath  =  $directory->getRoot('media');
        $mpdf = new \Mpdf\Mpdf(['tempDir' => $rootPath.'/pub/media/packageSlip/'.$method.'/']);
        $mpdf->WriteHTML($html);
        /*$mpdf->output('PackagingSlip.pdf', "S");*/
        $filename = str_replace(" ","_",$method.'_Packaging_Slip_'.$vendor_name.'_'.$orderid.'.pdf');
        $mpdf->output($rootPath.'/pub/media/packageSlip/'.$method.'/'.$filename, "F");

        $this->logger->info('File Name: '.$rootPath.'/pub/media/packageSlip/'.$method.'/'.$filename);

        if ($method == 'DHL') {
            return $filename;
        } else {
            try {

                $sender = $this->getAdminDetails();
                $this->logger->info('sender: '.json_encode($sender));
                $receiver = ['name'=> $vendor_name, 'email'=> $customer->getData('email')];
                $this->logger->info('receiver: '.json_encode($receiver));

                $postObjectData = [];
                $postObjectData['name'] = $vendor_name;
                $postObjectData['orderid'] = $orderid;
                $postObjectData['tracking_id'] = $tracking_id;
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($postObjectData);

                $emailTemplate = $this->getEmailTemplate($store_id, 'OWN');

                $this->logger->info('emailTemplate: '.$emailTemplate);

                $transport = $this->_transportBuilder
                    ->setTemplateIdentifier($emailTemplate)
                    ->setTemplateOptions(
                        [
                            'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                            'store' => $store_id
                            
                        ]
                    )
                    ->setTemplateVars(['data' => $postObject])
                    ->setFrom($sender)
                    ->addTo($receiver['email']);

                $packageslip =  $this->_mediaDirectory->getAbsolutePath('packageSlip/'.$method.'/'.$filename);            
                $filesContents = $this->reader->fileGetContents($packageslip);
                $transport->addAttachment($filesContents, $filename, 'application/pdf');
                $transport->getTransport()->sendMessage();
                $this->logger->info('## Packagin Slip has been sent ##');            
                $this->logger->info('## packageslip: '.$this->_urlInterface->getBaseUrl().'pub/media/packageSlip/'.$method.'/'.$filename);     
                /*return $this->_downloader->create(
                    $filename,
                    file_get_contents($this->_urlInterface->getBaseUrl().'pub/media/packageSlip/'.$filename), DirectoryList::MEDIA, 'application/pdf');*/
            } catch (Exception $e) {
                $this->logger->info('## Packagin Slip has erro: '.$e->getMessage()); 
            }

            return $filename;
        }

        
    }

    public function getPdfHtmlContent($order_id, $tracking_id, $method)
    {
        $data = $this->getPdfContent($this->_printslip, $order_id, $tracking_id);
        $packageslipFile = $this->generatePdfSlip($data, $method);
        return $packageslipFile;
    }

    public function getPdfContent($block, $order_id, $tracking_id)
    {
        $order = $block->order->load($order_id);
        // $this->logger->info(print_r($order->getData(), true)); $order->getIncrementId();
        $orderStatusLabel = $order->getStatusLabel();
        $this->logger->info('## orderStatusLabel : '.$orderStatusLabel);
        $paymentCode = '';
        $payment_method = '';
        if($order->getPayment()){
            $paymentCode = $order->getPayment()->getMethod();
            $payment_method = $order->getPayment()->getMethodInstance()->getTitle();
            $this->logger->info('## paymentCode : '.$paymentCode);
            $this->logger->info('## payment_method : '.$payment_method);
        }

        $marketplace_orders = $block->getSellerOrderInfo($order_id);
        $orderHelper = $this->_orderHelper;
        $tracking = $orderHelper->getOrderinfo($order_id);

        if($tracking!="" && $paymentCode == 'mpcashondelivery'){
            $codcharges=$tracking->getCodCharges();
            $this->logger->info('## codcharges : '.$codcharges);
        }

        /*$is_canceled = $tracking->getIsCanceled();*/

        /*if($is_canceled){
            $orderStatusLabel='Canceled';
        }*/


        $misHelper = $this->_misHelper;

        $vendorData =  $misHelper->getVendorDetails();

       /* echo "<pre>";
        print_r($vendorData->getData());
        die();*/


        /*$vendorName = "";*/
        $vendorName = $misHelper->getVendorName()->getFirstname();
        $this->logger->info('## vendorName : '.$vendorName);
        

       /* $vendorContactNumber = "";*/
        $vendorContactNumber = $vendorData->getContactNumber();
        $this->logger->info('## vendorContactNumber : '.$vendorContactNumber);

        /*$vendorCompanyLocality = "";*/
        $vendorCompanyLocality = $vendorData->getCompanyLocality();
        $this->logger->info('## vendorCompanyLocality : '.$vendorCompanyLocality);

        /*$vendorId = "";*/
        $vendorId = $vendorData->getSellerId();
        $this->logger->info('## vendorId : '.$vendorId);

        /*$vendorShippingDescription = "";*/
        $vendorShippingDescription = $block->escapeHtml($order->getShippingDescription());
        $this->logger->info('## vendorShippingDescription : '.$vendorShippingDescription);

        /*$paymentMethod = "";*/
        $paymentMethod = __('COD Charges');

       /* die(" Before html ");*/

        $html = '<!DOCTYPE html>
                    <html>
                    <head>
                        <title> PDF testing</title>
                        <meta name="viewport" content="width=device-width, initial-scale=1">
                        <meta http-equiv="content-type" content="text/html; charset=utf-8">
                        <meta name="keywords" content="">
                        <style>
                        table {
                          font-family: arial, sans-serif;
                          border-collapse: collapse;
                          width: 100%;
                        }

                        td, th {
                          border: 1px solid #dddddd;
                          text-align: left;
                          padding: 8px;
                        }

                        tr:nth-child(even) {
                          background-color: #dddddd;
                        }
                        .box {
                            width: 100%;
                            box-sizing: border-box;
                        }
                        .block-order-details-view .block-content .box {
                            margin-bottom: 30px;
                        }
                        .enclose_div{ margin-top: 20px; margin-bottom: 20px;}
                        .buyer_info{ margin-top: 20px; margin-bottom: 20px;}
                        #wk_mp_print_order .order-details-items {  padding:0px;  border:none; border-spacing: 0;
                            border-collapse: collapse;}
                        .box-title {
                            display: block;
                        }
                        .product-item-name, .product.name a {
                            font-weight: 400;
                        }
                        .abs-margin-for-blocks-and-widgets-desktop, .page-main .block, .customer-review .product-details {
                            margin-bottom: 0 !important;
                        }
                        .block .block-title {
                            color: #222;
                        }
                        .block .block-title {
                            margin: 5px 0 10px;
                            line-height: 1.2;
                            font-size: 16px;
                        }
                        .block-order-details-view .block-title {
                            padding: 0 10px;
                        }
                         ul {
                          list-style-type: none;
                         }
                        .table-caption{ margin-bottom: 10px;}
                        .block-order-details-view .box .box-content { box-sizing: border-box; }
                        </style>
                    </head>
                    <body style="margin: auto; background:#FFF;font-family:Arial">
                        <div id="mis_delivery_note">
                            <h1 class="page-title">
                                <span data-ui-id="page-title-wrapper" class="base">
                                    '.  __("#%1", $order->getIncrementId()) .'
                                </span>
                            </h1>
                        </div>
                        <div>
                            <p>
                                '. __("Date: ").'
                                <span>'.date("d.m.Y").'</span>
                            </p>
                        </div>
                        <div class="block block-order-details-view">
                            <div class="block-content">
                                <div class="box box-order-shipping-method">
                                    <strong class="box-title">
                                        <span>
                                            '. __("Vendor Details") .'
                                        </span>
                                    </strong>
                                    <div class="box-content">
                                        <div class="mis_seller_info">
                                            <div class="mis_vendor_contact">
                                                <label>
                                                    '. __("Name: ") .'
                                                </label>
                                                <span>
                                                    '. $vendorName .'
                                                </span>
                                            </div>
                                            <div class="mis_vendor_contact">
                                                <label>
                                                    '. __("Contact Number: ") .'
                                                </label>
                                                <span>
                                                    '. $vendorContactNumber .'
                                                </span>
                                            </div>
                                            <div class="mis_vendor_contact">
                                                <label>
                                                    '. __("Address: ") .'
                                                </label>
                                                <span>
                                                    '. $vendorCompanyLocality .'
                                                </span>
                                            </div>
                                            <div class="mis_vendor_contact">
                                                <label>
                                                    '. __("Seller Id: ") .'
                                                </label>
                                                <span>
                                                    '. $vendorId .'
                                                </span>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <div class="box box-order-shipping-method">
                                    <strong class="box-title">
                                        <span>
                                            '. __('Shipping Method') .'
                                        </span>
                                    </strong>
                                    <div class="box-content">
                                        '.$vendorShippingDescription.'
                                    </div>
                                </div>
                                <div class="box box-order-shipping-method">
                                    <strong class="box-title">
                                        <span>
                                            '. __('Order Details') .'
                                        </span>
                                    </strong>
                                    <div class="box-content">
                                        <div class="mis_vendor_contact">
                                            <label>
                                                '. __("Tracking ID: ") .'
                                            </label>
                                            <span>
                                                '.$tracking_id. '
                                            </span>
                                        </div>
                                        <div class="mis_vendor_contact">
                                            <label>
                                                '. __("Order ID: ") .'
                                            </label>
                                            <span>
                                                '. $order->getIncrementId() .'
                                            </span>
                                        </div>
                                        <div class="mis_vendor_contact">
                                            <label>
                                                '. __("Internal Order ID: ") .'
                                            </label>
                                            <span>
                                                '. 'life-ray-'.$order->getLiferayOrderId() .'
                                            </span>
                                        </div>
                                        </br>
                                        <div class="mis_vendor_contact">
                                            <label>
                                                '. __("Barcode: ") .'
                                            </label>
                                            <span class= "order_barcode" style="margin-top: 10px">
                                            <img src="data:image/png;base64,' . $this->_barCodeHelper->barCodeForPackagingSlip($order->getIncrementId()) . '">
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="wk_mp_design">
                            <div class="fieldset wk_mp_fieldset" id="wk_mp_print_order">
                                <div class="order-details-items ordered">
                                    <div class="table-wrapper order-items">
                                        <table>
                                            <caption class="table-caption">
                                                '.__('Items Ordered').'
                                            </caption>
                                            <thead>
                                                <tr>
                                                    <th class="col name">
                                                        '. __('Product Name') .'
                                                    </th>
                                                    <th class="col sku">
                                                        '. __('SKU') .'
                                                    </th>

                                                    <th class="col dimension" >
                                                        '. __('Dimension') .'
                                                    </th>
                                                    <th class="col weight">
                                                        '. __('Weight') .'
                                                    </th>
                                                    <th class="col price">
                                                        '. __('Price') .'
                                                    </th>
                                                    <th class="col qty">
                                                        '. __('Qty') .'
                                                    </th>
                                                    <th class="col price">
                                                        '. __('Total Price').'
                                                    </th>
                                                    <th class="col price">                                
                                                        '. $paymentMethod .'
                                                    </th>
                                                </tr>
                                            </thead>
                                            '.$this->getTableRows($order, $block, $order_id, $paymentCode, $misHelper).'
                                        </table>
                                    </div>
                                </div>
                                <div class="block buyer_info">
                                    <div class="block-title">
                                        <strong>
                                            '.__('Buyer Information').'
                                        </strong>
                                    </div>
                                    <div class="block-content">
                                        <div class="box-content">
                                            <div class="box">
                                                <div class="wk_row">
                                                    <span class="label">
                                                        '.__('Customer Name').'
                                                    </span>: 
                                                    <span class="value">
                                                        '.$order->getCustomerName().'
                                                    </span>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                <hr>
                                    <div class="enclose_div">
                                        <span style="display: inherit;">
                                            '. __('I hereby confirm that I enclose the packing slip with the package to myGermany.').'
                                        </span>
                                        <span style="display: inherit;">
                                            '. __('I hereby confirm that the package contains exactly the items that were ordered.In case of wrongly delivered goods, additional costs may be incurred (AGB).') .'
                                        </span>
                                    </div>
                                    <div class="signature_div">
                                        <span>
                                            '. __('----------------------------------------------------------------------------  ').'
                                        </span>
                                        <span class="sign-text">
                                            '. __('Signature') .'
                                        </span>
                                    </div>
                                </div>
                            </div>
                        </div>    
                    </body>';

        return ['html'=> $html, 'vendor_name'=> $vendorName, 'orderid'=> $order->getIncrementId(), 'vendorId'=> $vendorId, 'tracking_id'=> $tracking_id];

    }

    public function sendDhlLabel($vendorId, $vendor_name, $orderid, $tracking_id, $pdf, $dhlPackagingSlip)
    {
        $this->logger->info('## DHL Packaging Slip ##');
        $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $customer = $objectManager->create('Magento\Customer\Model\Customer')->load($vendorId);
        $store_id = $customer->getData('store_id');

        $postObjectData = [];
        $postObjectData['name'] = $vendor_name;
        $postObjectData['orderid'] = $orderid;
        $postObjectData['tracking_id'] = $tracking_id;
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($postObjectData);

        try {

            $receiver = ['name'=> $vendor_name, 'email'=> $customer->getData('email')];

            $sender = $this->getAdminDetails();
            $this->logger->info('sender: '.json_encode($sender));
            $receiver = ['name'=> $vendor_name, 'email'=> $customer->getData('email')];
            $this->logger->info('receiver: '.json_encode($receiver));

            $emailTemplate = $this->getEmailTemplate($store_id, 'DHL');
            $this->logger->info('DHL emailTemplate: '.$emailTemplate);

            $dhlPackageslipFile =  $this->_mediaDirectory->getAbsolutePath('packageSlip/DHL/'.$dhlPackagingSlip);            
            $dhlFilesContents = $this->reader->fileGetContents($dhlPackageslipFile);
            

            $transport = $this->_transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => $store_id                        
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($receiver['email']);
            
            /*$filesContents = $this->reader->fileGetContents($packageslip);*/
            $filename = 'DHL_Label_'.$vendor_name.'_'.$orderid.'.pdf';
            $transport->addAttachment($pdf, $filename, 'application/pdf'); /*DHL Label*/
            $transport->addAttachment($dhlFilesContents, $dhlPackagingSlip, 'application/pdf'); /* DHL Packaging SLip */
            $transport->getTransport()->sendMessage();
            $this->logger->info('## Packagin Slip has been sent ##');   
            
        } catch (Exception $e) {
            $this->logger->info('## DHL Exception : '.$e->getMessage());
        }
    }

    public function getTableRows($order, $block, $order_id, $paymentCode, $misHelper)
    {
        $this->logger->info('## getTableRows ##');
        $html_tbody = '<tbody>';

        $_items = $order->getItemsCollection();
        $i = 0;
        $_count = $_items->count();
        $subtotal = 0;
        $vendor_subtotal =0;
        $totaltax = 0;
        $admin_subtotal =0;
        $shippingamount = 0;
        $couponamount = 0;
        $codcharges_total = 0;
        foreach ($_items as $_item){
            $this->logger->info('## getTableRows ##');
            if ($_item->getParentItem()) {
                continue;
            }
            $row_total = 0;
            $available_seller_item = 0;
            $shippingcharges = 0;
            $couponcharges = 0;
            $itemPrice = 0;
            $seller_item_cost = 0;
            $totaltax_peritem = 0;
            $codcharges_peritem = 0;
            $seller_item_commission = 0;
            $orderid = $order_id;
            $seller_orderslist = $block->getSellerOrdersList($orderid,$_item->getProductId(),$_item->getItemId());
            foreach($seller_orderslist as $seller_item){
                $parentitem_falg = 0;
                $available_seller_item = 1;
                $totalamount = $seller_item->getTotalAmount();
                $seller_item_cost = $seller_item->getActualSellerAmount();
                $seller_item_commission = $seller_item->getTotalCommission();
                $shippingcharges = $seller_item->getShippingCharges();
                $couponcharges = $seller_item->getAppliedCouponAmount();
                $itemPrice = $seller_item->getMageproPrice();
                $totaltax_peritem = $seller_item->getTotalTax();
                if($paymentCode=='mpcashondelivery'){
                    $codcharges_peritem = $seller_item->getCodCharges();
                }
            }
            if($available_seller_item == 1){
                $i++;
                $seller_item_qty = $_item->getQtyOrdered();
                $row_total=$itemPrice*$seller_item_qty;

                $vendor_subtotal=$vendor_subtotal+$seller_item_cost;
                $subtotal=$subtotal+$row_total;
                $admin_subtotal = $admin_subtotal +$seller_item_commission;
                $totaltax=$totaltax+$totaltax_peritem;
                $codcharges_total=$codcharges_total+$codcharges_peritem;
                $shippingamount = $shippingamount+$shippingcharges;
                $couponamount = $couponamount+$couponcharges;

                $result = array();
                if ($options = $_item->getProductOptions()) {
                    if (isset($options['options'])) {
                        $result = array_merge($result, $options['options']);
                    }
                    if (isset($options['additional_options'])) {
                        $result = array_merge($result, $options['additional_options']);
                    }
                    if (isset($options['attributes_info'])) {
                        $result = array_merge($result, $options['attributes_info']);
                    }
                }
                // for bundle product
                $bundleitems = array_merge(array($_item), $_item->getChildrenItems());
                $_count = count ($bundleitems);
                $_index = 0;
                $_prevOptionId = '';

                $dimension = $misHelper->getProductDimension($_item->getProductId());
                if($_item->getProductType()!='bundle'){
                    $html_tbody .= '<tr class="border">';
                    if($_item->getProductType() != 'downloadable'){
                        $html_tbody .=  '<td class="col name">';
                        $html_tbody .=  '<strong class="product name product-item-name">'.$_item->getName().'</strong>';
                        $html_tbody .= '<dl class="item-options">';
                        if($_options = $result){
                            foreach ($_options as $_option){
                                $html_tbody .= '<dt>'.$_option['label'].'</dt>';
                            }
                        }
                        $html_tbody .= '</dl>';
                        $html_tbody .= '</td>';
                        $html_tbody .= '<td class="col sku">'.$_item->getSku().'</td>';
                        $html_tbody .= '<td class="col sku">'.$dimension.'</td>';
                        $html_tbody .= '<td class="col sku">'.$_item->getWeight().'</td>';
                        $html_tbody .= '<td class="col sku"><span class="price-excluding-tax"><span class="cart-price">'.$order->formatPrice($_item->getPrice()).'</span></span></td>';
                        $html_tbody .= '<td class="col qty"><ul class="items-qty" style="list-style: none;"><li class="item"><span class="title">'.__('Ordered').'</span>: <span class="content">'. __($_item->getQtyOrdered()*1).'</span></li></ul>';
                        $html_tbody .= '<td class="col price">'.$order->formatPrice($block->getOrderedPricebyorder($order, $row_total)).'</td>';
                        $html_tbody .= '<td class="col subtotal">'. $order->formatPrice($block->getOrderedPricebyorder($order, $row_total)).'</td></tr>';

                    }
                }

            }
        }

        $html_tbody .= '</tbody>';
        return $html_tbody;
    }
}
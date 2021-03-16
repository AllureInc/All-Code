<?php
/**
 * Copyright © 2017 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Mangoit\Marketplace\Helper;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Store\Model\StoreRepository;
use Webkul\Marketplace\Model\Sellertransaction;


class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    protected $transport;
    protected $_objectManager;
    protected $scopeConfig; // for email
    protected $_request;    
    protected $_storeManager;    
    protected $_cart;    
    protected $_productloader;  
    protected $_sellerProducts;  
    protected $_customerSession;  
    protected $_customerRepositoryInterface;  
    protected $_addRepositoryInterface;  
    protected $_inlineTranslation; // for Email
    protected $transportBuilder; //for email
    protected $_checkoutSession;
    protected $_storeRepository;
    protected $salesList;
    protected $productFaq;
    protected $saleperpartner;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var Sellertransaction
     */
    protected $sellertransaction;
    /**
     * @var \Magento\Eav\Model\Entity\Attribute
     */ 
    protected $_entityAttribute;

    /** @var \Magento\Sales\Api\Data\OrderInterface $order **/
    protected $order;

    /** @var \Magento\Sales\Api\Data\OrderInterface $order **/
    protected $_marketplaceEmail;

    protected $moduleManager;
    protected $_messageManager;
    protected $saleslistFactory;
    
    public function __construct (
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\View\Result\PageFactory $resultPageFactory,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Framework\Filesystem $filesystem,
        \Magento\MediaStorage\Model\File\UploaderFactory $fileUploaderFactory,
        TransportBuilder $transportBuilder,
        \Magento\Framework\Translate\Inline\StateInterface $inlineTranslation,
        \Magento\Framework\Controller\Result\Redirect $resultRedirect,
        \Magento\Framework\Filesystem\Driver\File $reader,
        \Magento\Framework\Filesystem\Io\File $ioAdapter,
        \Magento\Framework\App\Request\Http $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Checkout\Model\Cart $cart ,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Webkul\Marketplace\Model\Product $_sellerProducts,
        \Magento\Customer\Model\Session $_customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $_customerRepositoryInterface,
        \Magento\Customer\Api\AddressRepositoryInterface $_addRepositoryInterface,
        \Magento\Checkout\Model\Session $checkoutSession,
        StoreRepository $storeRepository,
        Sellertransaction $sellertransaction,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Webkul\Marketplace\Model\Saleslist $salesList,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslistFactory,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        \Magento\Eav\Model\Entity\Attribute $entityAttribute,
        \Magento\Sales\Api\Data\OrderInterface $order,
        \Webkul\Marketplace\Model\Saleperpartner $saleperpartner,
        \Mangoit\VendorPayments\Helper\Data $helper,
        \Mangoit\Marketplace\Helper\MarketplaceEmail $marketplaceEmail,
        \Magento\Framework\Module\Manager $moduleManager,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        array $data = []
    ) {   
        $this->saleperpartner = $saleperpartner;
        $this->_request = $request;
        $this->_storeManager = $storeManager;   
        $this->_objectManager = $objectmanager;
        $this->_cart = $cart;  
        $this->_productloader = $_productloader;
        $this->_sellerProducts = $_sellerProducts;
        $this->_customerSession = $_customerSession;
        $this->_customerRepositoryInterface = $_customerRepositoryInterface;
        $this->_addRepositoryInterface = $_addRepositoryInterface;
        $this->_inlineTranslation = $inlineTranslation;
        $this->transportBuilder = $transportBuilder;
        $this->scopeConfig = $scopeConfig;
        $this->_checkoutSession = $checkoutSession;
        $this->_storeRepository = $storeRepository;
        $this->sellertransaction = $sellertransaction;
        $this->salesList = $salesList;
        $this->productFaq = $productFaq;
        $this->stockRegistry = $stockRegistry;
        $this->_entityAttribute = $entityAttribute;
        $this->order = $order;
        // RMA
        $this->helper = $helper;
        $this->_marketplaceEmail = $marketplaceEmail;

        /*RMA ends*/
        $this->moduleManager = $moduleManager;
        $this->_messageManager = $messageManager;
        $this->saleslistFactory = $saleslistFactory;
        parent::__construct($context);
    }

    public function isModuleEnabled($module_name)
    {
        if ($this->moduleManager->isOutputEnabled($module_name)) {
            return true;
        } else {
            return false;
        }
    }

    public function getControllerModule()
    {
         return $this->_request->getControllerModule();
    }

    public function getVendorTurnOver($vendor_id)
    {
        $turnover = 0;
        $commission = 0;
        if ($this->saleperpartner->load($vendor_id, 'seller_id')) {
            $turnover = $this->saleperpartner->getSellerTurnover();
            $commission = $this->saleperpartner->getCommissionRule();
        }
        // $this->saleperpartner->load($vendor_id, 'seller_id');
        $data = ['turnover'=> $turnover, 'commission'=> $commission];
        return $data;

    }

    public function getCurrencySymbol()
    {
        $storeManager = $this->_objectManager->get('Magento\Store\Model\StoreManagerInterface');
        $currencyCode = $storeManager->getStore()->getCurrentCurrencyCode();
        $currency = $this->_objectManager->create('Magento\Directory\Model\CurrencyFactory')->create()->load($currencyCode);
        $currencySymbol = $currency->getCurrencySymbol();

        return $currencySymbol;
    }
        
    public function getFullActionName()
    {
        return $this->_request->getFullActionName();
    }
        
    public function getRouteName()
    {
         return $this->_request->getRouteName();
    }
        
    public function getActionName()
    {
         return $this->_request->getActionName();
    }
        
    public function getControllerName()
    {
         return $this->_request->getControllerName();
    }
        
    public function getModuleName()
    {
         return $this->_request->getModuleName();
    }   

    public function getMediaUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_MEDIA);
    } 
    public function getBaseUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
    } 
    public function getCurrentUrl()
    {
        return $this->_storeManager->getStore()->getCurrentUrl(false);
    }  

    public function getCurrentStore()
    {
        return $this->_storeManager->getStore();
    }   

    public function getCurrentUrlWithNoStore()
    {
        $urlInterface =  $this->_objectManager->create('\Magento\Framework\UrlInterface');
        return $urlInterface->getCurrentUrl();
    }

    public function getCustomer()
    {
        $urlInterface =  $this->_objectManager->create('\Magento\Framework\UrlInterface');
        if ($this->getBaseUrl().'customer/address/new/' != $urlInterface->getCurrentUrl()) {
            $customerSession = $this->_objectManager->create('Magento\Customer\Model\Session');
            $customerModel = $this->_objectManager->create('Magento\Customer\Model\Customer');
            $resultRedirectFactory  = $this->_objectManager->create('Magento\Framework\Controller\Result\RedirectFactory');
            if ($customerSession->getCustomerId()) {
                $sellerStatus = $this->getSellerStatus($customerSession->getCustomerId());
                if ($sellerStatus) {
                    $customer = $customerModel->load($customerSession->getCustomerId());
                    if (!empty($customer->getAddresses())) {
                        return ['result'=> true];
                    } else {
                        $this->_messageManager->addWarning($this->getAddAddressMessage());
                        return ['result'=> false, 'url'=> $this->getBaseUrl().'customer/address/new/'];
                    }
                } else {
                    $this->_messageManager->addNotice($this->getProfileApprovalWaitingMessage());
                    return ['result'=> true];
                }
            } else {
                return ['result'=> true];
            }            
        } else {
            return ['result'=> true];
        }
    }
    public function getSellerStatus($sellerId = 0)
    {
        $sellerStatus = 0;
        if (!$sellerId) {
            $sellerId = $customerSession->getCustomerId();
        }
        $webkulHelper = $this->_objectManager->create('Webkul\Marketplace\Helper\Data');
        $model = $webkulHelper->getSellerCollectionObj($sellerId);
        foreach ($model as $value) {
            $sellerStatus = $value->getIsSeller();
        }
        return $sellerStatus;
    }
    public function getGoogleFonts()
    {
        $jsonFile = dirname(__DIR__).'/Helper/google_fonts.json';
        $content = file_get_contents($jsonFile);
        $decodedList = json_decode($content);
        return $decodedList;
    }

    public function getOrderByIncrementId($orderIncrementId)
    {
        $order = $this->order->loadByIncrementId($orderIncrementId);
        return $order;
    }

    public function getStockDetails($productId)
    {
        $stockItem = $this->stockRegistry->getStockItem($productId);
        return $stockItem->getQty();
    }

    public function getWarehouseCharge()
    {
        $productInfo = $this->_cart->getQuote()->getAllVisibleItems();
        if (!empty($productInfo)) {
            $sellerPriceArray = array();
            $vendorHighestProduct = [];
            foreach ($productInfo as $item){
               $productObj = $this->_productloader->create()->load($item->getProductId());
               $mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id');

               $sellerId = $mageProduct->getSellerId();
               $shippingCost = $productObj->getShippingPriceToMygmbh();
               if (array_key_exists($sellerId,$sellerPriceArray))
                {
                    if ($sellerPriceArray[$sellerId] > $shippingCost || ($sellerPriceArray[$sellerId] == $shippingCost)) {
                        continue;
                    } else if($sellerPriceArray[$sellerId] < $shippingCost) {
                        $sellerPriceArray[$sellerId] = $shippingCost;
                        $vendorHighestProduct[$sellerId][$item->getProductId()] = $shippingCost;
                    } 
                } else {
                    $sellerPriceArray[$sellerId] = $shippingCost;
                    $vendorHighestProduct[$sellerId][$item->getProductId()] = $shippingCost;
                }

            }
            $this->_checkoutSession->setVendorHighestShippingPerProduct($vendorHighestProduct);
            $finalWarehouseCharge = array_sum($sellerPriceArray);
            $inclTaxWarehouseCharge = $finalWarehouseCharge + (($finalWarehouseCharge*19)/100);
            return $inclTaxWarehouseCharge;  
        }
    }

    public function getDropshipCharge()
    {
        $productInfo = $this->_cart->getQuote()->getAllVisibleItems();
        $sellerPriceArray = array();
        $shippingCost = 0;
        $length = 0;
        $width = 0;
        $height = 0;
        $weight = 0;
        if (!empty($productInfo)) {
            foreach ($productInfo as $item){
                $productObj = $this->_productloader->create()->load($item->getProductId());
                $mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id');
                $sellerId = $mageProduct->getSellerId();
                $shippingCost = $shippingCost + $productObj->getShippingPriceToMygmbh();
                $length = $length + $productObj->getMygmbhShippingProductLength();
                $width = $weight + $productObj->getMygmbhShippingProductWidth();
                $height = $height + $productObj->getMygmbhShippingProductHeight();
                $weight = $weight + $productObj->getWeight();

            }
            $shippingAddObj = $this->_cart->getQuote()->getShippingAddress();
            $shippingAddObj->getCountryId();
            $shippingAddObj->getPostcode();

            return $shippingCost;
        }
    }

    public function getCurrentCustomer()
    {

        $customerDataArray = array();
        $customer = $this->_customerRepositoryInterface->getById($this->_customerSession->getCustomer()->getId());
       // $billingAddressId = $customer->getDefaultBilling();
        $shippingAddressId = $customer->getDefaultShipping();
        if ($shippingAddressId) {
            //get default shipping address
             try {
                $shippingAddress = $this->_addRepositoryInterface->getById($shippingAddressId);
                $customerDataArray['zip_code'] = $shippingAddress->getPostcode();
                $apiUrl = 'https://restcountries.eu/rest/v2/alpha/'.$shippingAddress->getCountryId();
                $ch = curl_init();
                curl_setopt($ch, CURLOPT_URL,$apiUrl);
                    // receive server response ...
                curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
                $server_output = json_decode(curl_exec($ch),true);
                curl_close ($ch);
                if (!empty($server_output)) {
                    $customerDataArray['numeric_code'] = $server_output['numericCode'];
                }
            } catch (\Exception $e) {
                //
            }
            return $customerDataArray;
        } else {
            return $customerDataArray;
        }
    }

    public function getCustomerUpdated()
    {

        $customerDataArray = array();
        $customer = $this->_customerRepositoryInterface->getById($this->_customerSession->getCustomer()->getId());
       // $billingAddressId = $customer->getDefaultBilling();
        $addresses = $customer->getDefaultBilling();
    }

    public function getAllStores()
    {
        $stores = $this->_storeRepository->getList();
        $storesArr = [];
        foreach ($stores as $store) {
            $storesArr[$store->getStoreId()] = $store->getName();
        }
        return $storesArr;
    }

    public function getProductFaqCollection($vendorId, $productId)
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $faqColl = $this->productFaq->getCollection()
        ->addFieldToFilter('vendor_id', $vendorId)
        ->addFieldToFilter('product_id', $productId)
        ->addFieldToFilter('store_id', $storeId);
        return $faqColl; 
    }

    public function getSCCPrice($shippingCost, $length, $width, $height, $weight)
    {
        echo $shippingCost; echo "=>";
        echo $length; echo "=>";
        echo $width; echo "=>";
        echo $height; echo "=>";
        echo $weight; echo "=>";
        echo $zipCode = '452001'; echo "=>";
        echo $countryISOCode = '356'; echo "=>";

        $apiUrl = 'https://account.mygermany.com/web/content/sccws?p_p_id=sccws_WAR_SCCWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&_sccws_WAR_SCCWSportlet_p_v_g_id=10180&_sccws_WAR_SCCWSportlet_dataType=json&_sccws_WAR_SCCWSportlet_cmd=add&_sccws_WAR_SCCWSportlet_currentURL=%2Fweb%2Fcontent%2Fsccws&_sccws_WAR_SCCWSportlet_doAsUserId&deliveryFrom=276&deliveryTo='.$countryISOCode.'&zipCode='.$zipCode.'&weight='.$weight.'&weightUnit=kg&dimLength='.$length.'&dimUnit=in&dimWidth='.$width.'&dimHeight='.$height.'&value=71&insurance=true';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiUrl);
            // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = json_decode(curl_exec($ch),true);
        curl_close ($ch);
        // echo "<pre>";
        // print_r($server_output);
        // die('helper');

        // 'https://account.mygermany.com/web/content/sccws?p_p_id=sccws_WAR_SCCWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&_sccws_WAR_SCCWSportlet_p_v_g_id=10180&_sccws_WAR_SCCWSportlet_dataType=json&_sccws_WAR_SCCWSportlet_cmd=add&_sccws_WAR_SCCWSportlet_currentURL=%2Fweb%2Fcontent%2Fsccws&_sccws_WAR_SCCWSportlet_doAsUserId&deliveryFrom=276&deliveryTo=408&zipCode=91056&weight=24000&weightUnit=lb&dimLength=26&dimUnit=in&dimWidth=69&dimHeight=48&value=71&insurance=true'
        // $productInfo = $this->_cart->getQuote()->getAllVisibleItems();
        // $sellerPriceArray = array();
        // $shippingCost = 0;
        // if (!empty($productInfo)) {
        //     foreach ($productInfo as $item){
        //         $productObj = $this->_productloader->create()->load($item->getProductId());
        //         $mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id');
        //         $sellerId = $mageProduct->getSellerId();
        //         $shippingCost = $shippingCost +$productObj->getShippingPriceToMygmbh();
        //     }
        //     return $shippingCost;
        // }
    }

    public function getScopeConfigValue($configPath)
    {
        $scopeValue = $this->scopeConfig->getValue($configPath, \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $this->helper->getStore()->getStoreId());
        return $scopeValue;
    }

    public function getProfileApprovalWaitingMessage()
    {
        $path = "marketplace/general_settings/approval_waiting_msg";
        return $this->getScopeConfigValue($path);
    }

    public function getProfileSavedMessage()
    {
        $path = "marketplace/general_settings/profile_saved_message";
        return $this->getScopeConfigValue($path);
    }

    public function getAddAddressMessage()
    {
        $path = "marketplace/general_settings/add_address_message";
        return $this->getScopeConfigValue($path);
    }

    public function getSellerTransactionDetails($orderId, $sellerId) 
    {
        $salesListColl = $this->salesList->getCollection()
        ->addFieldToFilter('seller_id',['eq'=> $sellerId])
        ->addFieldToFilter('order_id',$orderId);
        $isPaid = 1;
        foreach ($salesListColl as $salesListkey => $salesListValue) {
            if (!$salesListValue->getPaidStatus()) {
                $isPaid = 0;
            }
        }
        return $isPaid;
    }

    public function isEuCountry($countryId)
    {
        $eu_countries = $this->scopeConfig->getValue('general/country/eu_countries');
        $eu_array = explode(',', $eu_countries);
        
        // $countryArr = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];
        $euCountry = false;
        if (in_array($countryId, $eu_array)) {
            $euCountry = true;
        }
        return $euCountry;
    }
    
    // Sensitive Attributes- Start
    public function getProductAttrLabel($attrCode)
    {
        $attrObj = $this->_entityAttribute->loadByCode(4, $attrCode);        
        return $attrObj->getFrontendLabel();
    }

    public function getCustomFields($product_id)
    {
        $fieldModel = $this->_objectManager->create('Mangoit\VendorField\Model\Fieldmodel')->getCollection();
        $fieldModel->addFieldToFilter('product_id', $product_id);
        return $fieldModel;
    }

    public function getProductSensitiveAttributes($productId)
    {
        $sensitiveAttrModel = $this->_objectManager->create('\Mangoit\Marketplace\Model\Sensitiveattrs');
        $sensitiveAttrModel->load($productId, 'mageproduct_id');
        
        return $sensitiveAttrModel;
    }

    public function getSensitiveAttributes()
    {
     /** @var  $coll \Magento\Eav\Model\ResourceModel\Entity\Attribute\Collection */

        $attributeSetId = 4;//your_attributeSetId
        $attributeCollection = $this->_objectManager->get ( 'Magento\Eav\Model\Entity\Attribute' )->getCollection ();
        $attributeCollection->addFieldToFilter('frontend_input', 'boolean');
        $attributeCollection->setAttributeSetFilter ( $attributeSetId );
        $attributeCollection->addFieldToFilter('is_user_defined', 1);
        $attributeCollection->addFieldToFilter('attribute_code', ['neq' => 'sw_featured']);
        $attributeCollection->addFieldToFilter('attribute_code', ['neq' => 'liquid']);
        return $attributeCollection;
    }
    // Sensitive Attributes- End

    public function sendMail($name, $email, $storeId = 0)
    {
            // $toName  =  // sender
            // $toEmail = $customerEmail; // sender

            $toName  = $this->getScopeConfigValue('trans_email/ident_general/name');  // sender
            $toEmail = $this->getScopeConfigValue('trans_email/ident_general/email'); // sender

            $salesName = $name;// receiver
            $salesEmail =  $email; // receiver

            $postObject = new \Magento\Framework\DataObject();
            $postObject->setData(['name'=> $salesName,]);

            /*$sender = [
                'name' => $salesName,
                'email' => $salesEmail,
            ];*/

            $sender = [
                'name' => $toName,
                'email' => $toEmail,
            ];

            $receiverInfo = ['name'=> $salesName, 'email'=> $salesEmail];

            // $pathToEmalFile1 =  DirectoryList::VAR_DIR.'/ImportEmail/'.$fileName1;
            // $pathToEmalFile2 =  DirectoryList::VAR_DIR.'/ImportEmail/'.$fileName2;
            $sellerStoreId = $storeId;
            $this->_marketplaceEmail->sendApprovalFskEmail($postObject, $sender, $receiverInfo, $sellerStoreId);

            return true;
            
            /*$this->_inlineTranslation->suspend();
            $transport = $this->transportBuilder
            ->setTemplateIdentifier('marketplace_email_fsk_approval')
            ->setTemplateOptions(
              [
                'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
              ]
            )
            ->setTemplateVars(['data' => $postObject])
            ->setFrom($sender)
            ->addTo($salesEmail); // ->addTo($toEmail);

            $transport->getTransport()->sendMessage();
            $this->_inlineTranslation->resume();
            return true;*/

    }

    public function isReturnRequestAlreadyExist($order_id)
    {
        $salesList = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');
        $collection = $salesList->getCollection()
        ->addFieldToFilter(
            'magerealorder_id', array('eq'=> $order_id) 
        )->addFieldToFilter(
            'return_request_by_customer', array('eq'=> 1) 
        );
        // var_dump(count($collection->getData()) );
        // die('helper2');
        if (count($collection->getData()) > 0) {
            return true;
        } else {
            return false;
        }
    }

    public function isRequestesBycustomer($order_id)
    {
        $salesList = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');
        $collection = $salesList->getCollection()->addFieldToFilter('magerealorder_id', array('eq'=> $order_id));
        if (count($collection->getData()) ) 
        {
            foreach ($collection as $item) {
                $item->setReturnRequestByCustomer(true);
                $item->save();
            }
            # code...
        }
    }

    public function sendCustomEmailToAdmin($data)
    {
        $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );

                //sending required data to email template.
        $postObjectData = [];
        $postObjectData = $data;
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($postObjectData);

        $sender = [
           'name' => $data['name'],
           'email' => $data['email'],
        ];

        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier('marketplace_email_return_order_customer')
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($generalEmail);

            $transport->getTransport()->sendMessage();

            $this->isRequestesBycustomer($data['orderid']);
            return true;
            
        } catch (Exception $e) {
            return false;
        }

    }

    /* This function will return if the vendor is already paid for the order.*/
    public function isVendorAlreadyPaid($seller_id, $order_id)
    {
        $vendorInvoiceModel = $this->_objectManager->create('Mangoit\VendorPayments\Model\Vendorinvoices');
        $collection = $vendorInvoiceModel->getCollection()->addFieldToFilter(
            'seller_id', ['eq'=> $seller_id]
        )->addFieldToFilter(
            'invoice_typ', ['eq'=> 2]
        )->addFieldToFilter(
            'order_ids', ['finset'=> $order_id]
        );
        
        if (count($collection->getData()) > 0 ) {
            foreach ($collection as $item) {
                $resultData = array(
                    'result'=> true, 
                    'transaction_id'=> $item['transaction_id'],
                    'invoice_number'=> $item['invoice_number']
                );                
            }
            return $resultData;
        } else {
            $resultData['result'] = false;
            return $resultData;
        }
    }

    /* This function will return the price of s'sent to mygermany' of product*/
    public function getSellerPriceArray($dataArray)
    {
        $productLoaderObj = $this->_productloader->create();
        $sellerPriceArray = array();
        foreach ($dataArray as $perRow){
            $product = $productLoaderObj->load($perRow['mageproduct_id']);
           $sellerId = $perRow['magerealorder_id'];
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

    /*This function will verify the order and send email regarding the return process*/
    public function checkOrderDetailsForVendor($order_id, $order_status)
    {
        // echo "<pre>"; 
        $data = [];
        $vendorStoreId = 0;
        if ($order_status == 'return_by_customer') {
           $data['return_by'] = 'customer';
        }

        if ($order_status == 'return_by_mygermany'){
            $data['return_by'] = 'myGermany';
        }

        $priceHelper = $this->_objectManager->create('Mangoit\VendorPayments\Helper\Data');
        $total_mail_receiver = [];

        $salesList = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');

        $seller_id_array = $this->getSellerIdArray($salesList, $order_id);

        if (!empty($seller_id_array)) {
            foreach ($seller_id_array as $vendor_id) {
                $collection = $salesList->getCollection()->addFieldToFilter(
                    'order_id', array('eq'=> $order_id)
                )->addFieldToFilter('seller_id', array('eq'=> $vendor_id));

                $sellerPriceArray = $this->getSellerPriceArray($collection);
                foreach ($collection as $item) {
                    $vendorIsPaid = $this->isVendorAlreadyPaid($item->getSellerId(), $order_id);

                    $vendor = $this->_customerRepositoryInterface->getById($item->getSellerId());

                    $vendorStoreId = $vendor->getCreatedIn();
                    if ($vendorStoreId == 'Germany') {
                        $vendorStoreId = 7;
                    } else {
                        $vendorStoreId = 1;
                    }
                    
                    /*$vendorStoreId = $vendor->getData("store_id");*/

                    $totalAmountArray[$item->getSellerId()][] = number_format($item['total_amount'], 2, '.', ''); //add VAT
                    $totalAmountArray[$item->getSellerId()][] = number_format((float)$sellerPriceArray[$item['magerealorder_id']], 2, '.', ' ');
                    $totalFeeArray[$item->getSellerId()][] = number_format($item['total_commission'], 2, '.', '');
                    $totalFeeArray[$item->getSellerId()][] = number_format($item['mits_payment_fee_amount'], 2, '.', '');
                    $totalFeeArray[$item->getSellerId()][] = number_format($item['mits_exchange_rate_amount'], 2, '.', '');

                    $data['seller_id'] = $item->getSellerId();
                    $data['seller_email'] = $vendor->getEmail();
                    $data['seller_name'] = $vendor->getFirstname();
                    $data['order_id'] = $item->getMagerealorderId();

                    if (!$vendorIsPaid['result']) {
                        if (!empty($data)) {
                            /* Order return by admin */
                            if (! $this->isReturnRequestAlreadyExist($data['order_id'])) {
                                $this->isRequestesBycustomer($data['order_id']);
                            }
                            /*Order return by admin  ends*/


                            $data['template'] = 'marketplace_email_return_order_vendor';

                            if (!in_array($data['seller_email'], $total_mail_receiver)) {

                                $this->sendEmailToVendor($data, $vendorStoreId);
                                $total_mail_receiver[] = $data['seller_email'];
                                # code...
                            } 
                        }                    
                    } else {
                        $orderIdArray[] = $item->getMagerealorderId();
                    }
                    $sellerPriceArray[$item['magerealorder_id']] = '';
                }

                // net total calculations
                // echo "<br> sum totalAmountArray ".array_sum($totalAmountArray[$item->getSellerId()]);
                // echo "<br> sum totalFeeArray ".array_sum($totalFeeArray[$item->getSellerId()]);

                $netTotal = (array_sum($totalAmountArray[$item->getSellerId()]) - array_sum($totalFeeArray[$item->getSellerId()]));
                $totalInclVat = number_format(($netTotal*19)/100, 2, '.', '');
                $totalToBePaid = number_format($netTotal + $totalInclVat, 2, '.', '');

                // echo "<br> totalInclVat: ".$netTotal;
                // echo "<br> totalInclVat: ".$totalInclVat;
                // echo "<br> totalToBePaid: ".$totalToBePaid;
                // echo "</pre>";
                // net total calculation ends

                if ($vendorIsPaid['result']) {
                    // print_r($vendorIsPaid);
                    // die('...........');
                    $data['total_paid_amount'] = $priceHelper->getFormatedPrice($totalToBePaid);
                    $data['all_order_ids'] = $orderIdArray;
                    $data['transaction_id'] = $vendorIsPaid['transaction_id'];
                    $data['invoice_number'] = $vendorIsPaid['invoice_number'];
                     if (!empty($data)) {
                        // print_r($data);
                        // echo "<br><br>";
                        $data['template'] = 'marketplace_email_return_paid_order_vendor';
                        $this->sendEmailToVendor($data, $vendorStoreId);
                    } 
                }
            }
        }
    }

    public function getSellerIdArray($salesList, $order_id)
    {
        $seller_id_array = [];
        $seller_collection = $salesList->getCollection()->addFieldToFilter(
            'order_id', array('eq'=> $order_id)
        )->addFieldToFilter('seller_id', array('neq'=> 0))->addFieldToSelect('seller_id');
        // print_r($seller_collection);
        foreach ($seller_collection as $item) {
            if (!in_array($item['seller_id'], $seller_id_array)) {
                $seller_id_array[] = $item['seller_id'];
            }
        }
        return $seller_id_array;
    }

    public function sendEmailToVendor($data, $vendorStoreId = 0)
    {
        $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );
        $generalName = $this->helper->getConfigValue(
                    'trans_email/ident_general/name',
                    $this->helper->getStore()->getStoreId()
                );

                //sending required data to email template. 'trans_email/ident_general/name
        $postObjectData = [];
        if (isset($data['all_order_ids'])) {
            $data['order_id'] = implode(',', array_unique($data['all_order_ids']));
            $data['pay_url'] = $this->getPaymentUrl($data);
        }

        $postObjectData = $data;
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($postObjectData);

        // $sender = [
        //    'name' => $data['name'],
        //    'email' => $data['email'],
        // ];

        // echo "<pre>";
        // print_r($data);
        // die();

        $sender = [
           'name' => $generalName,
           'email' => $generalEmail
        ];

        // print_r($sender);
        if ($data['template'] == 'marketplace_email_return_order_vendor') {
            $this->_marketplaceEmail->sendReturnOrderVendor($postObject, 
                $sender, 
                [
                    'email'=> $data['seller_email'],
                    'name'=> $data['seller_name']
                ], 
                $vendorStoreId);
            return true;
        } 

        // print_r($sender);
        if ($data['template'] == 'marketplace_email_return_paid_order_vendor') {
            $this->_marketplaceEmail->sendReturnPaidOrderVendorEmail(
                $postObject, 
                $sender, 
                [
                    'email'=> $data['seller_email'],
                    'name'=> $data['seller_name']
                ], 
                $vendorStoreId);
            return true;
        } 


        try {
            $transport = $this->transportBuilder
                ->setTemplateIdentifier($data['template'])
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->addTo($data['seller_email'])
                ->setFrom($sender);
                // ->setFrom($sender)

            $transport->getTransport()->sendMessage();
            return true;
            
        } catch (Exception $e) {
            return false;
        }

    }

    public function getPaymentUrl($data)
    {
        $seller_id = $data['seller_id'];
        $order_ids = $data['all_order_ids'];
        $storeManager = $this->_objectManager->create('\Magento\Store\Model\StoreManagerInterface');
        $url = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);
        $params = ['seller_id'=> $seller_id, 'order_ids'=> $order_ids, 'amount'=> $data['total_paid_amount']];
        // $url = $url.'marketplce/rma/email/receive/'.$params;
        $url = $url.'marketplce/rma/payment/receive/'.http_build_query($params);
        // return http_build_query($url);
        // return http_build_query($params);
        return $url;
    }

    public function sendEmailToAdmin($data, $storeId)
    {
        $generalEmail = $this->helper->getConfigValue(
                    'trans_email/ident_general/email',
                    $this->helper->getStore()->getStoreId()
                );

        $postObjectData = [];
        $postObjectData = $data;
        $postObject = new \Magento\Framework\DataObject();
        $postObject->setData($postObjectData);

        $sender = [
           'name' => $data['name'],
           'email' => $data['email'],
        ];


        try {
            
            $this->_marketplaceEmail->sendCustomEmail($data['email_template'], $sellerStoreId = 0, $postObject, $sender, ['email'=> $generalEmail]);
            /*$transport = $this->transportBuilder
                ->setTemplateIdentifier($data['email_template'])
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($generalEmail);

            $transport->getTransport()->sendMessage();
            return true;*/
            
        } catch (Exception $e) {
            return false;
        }
        
    }

    public function getSellerInformations($seller_id)
    {
        $seller = $this->_customerRepositoryInterface->getById($seller_id);
        return array(
            'first_name'=> $seller->getFirstname(),
            'last_name'=> $seller->getLastname()
        );
    }

    public function getPricebyorder($orderId, $vendor_to_mygermany_cost)
    {
        $sellerId = $this->_customerSession->getCustomer()->getId();
        $collection = $this->saleslistFactory->create()
                      ->getCollection()
                      ->addFieldToFilter(
                          'main_table.seller_id',
                          $sellerId
                      )->addFieldToFilter(
                          'main_table.order_id',
                          $orderId
                      )->getPricebyorderData();
        $name = '';
        $actualSellerAmount = 0;
        foreach ($collection as $coll) {
            $actualSellerAmount = $coll['total_amount'] - $coll['total_commission'] + $vendor_to_mygermany_cost;
        }

        return $actualSellerAmount;
    }
}
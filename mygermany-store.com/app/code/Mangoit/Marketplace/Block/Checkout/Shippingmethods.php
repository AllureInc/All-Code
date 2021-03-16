<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\Marketplace\Block\Checkout;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Shippingmethods extends \Magento\Framework\View\Element\Template
{

    protected $cart;

    protected $_productloader;  
    protected $_sellerProducts; 
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Directory\Model\CurrencyFactory
     */
    protected $currencyFactory;

    protected $pricingHelper;


    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        \Webkul\Marketplace\Model\Product $_sellerProducts,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Directory\Model\CurrencyFactory $currencyFactory,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        array $data = []
    ) {
        $this->cart = $cart;
        $this->_productloader = $_productloader;
        $this->_sellerProducts = $_sellerProducts;
        $this->storeManager = $storeManager;
        $this->currencyFactory = $currencyFactory;
        $this->pricingHelper = $pricingHelper;
        parent::__construct($context, $data);
    }


    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getAllShippingMethods()
    {
        $quoteObj = $this->cart->getQuote();
        $shippingAddObj = $quoteObj->getShippingAddress();
        $countryId = $shippingAddObj->getCountryId();
        $postalCode = $shippingAddObj->getPostcode();

        $currencyCodeFrom = $this->storeManager->getStore()->getCurrentCurrency()->getCode();
        $currencyCodeTo = $this->storeManager->getStore()->getBaseCurrency()->getCode();

        if (!empty($countryId) && (!empty($postalCode))) {
            $productInfo = $quoteObj->getAllVisibleItems();
            $itemCOunt = $quoteObj->getItemsCount();
            $sellerPriceArray = array();
            $shippingCost = 0;
            $length = 0;
            $width = 0;
            $height = 0;
            $weight = 0;
            $price = 0;
            $notAcceptable = 0;
            $multipleArticles = (($itemCOunt > 1) ? true : false);
            if (!empty($productInfo)) {
                $productObj = '';
                foreach ($productInfo as $item){
                    if ($item->getProduct()->getTypeId() == 'simple') {
                        $productObj = $this->_productloader->create()->load($item->getProductId());
                        //$mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id'); 
                    } else {
                        if ($option = $item->getOptionByCode('simple_product')) {
                            $productId = $option->getProduct()->getId();
                            $productObj = $this->_productloader->create()->load($productId);
                            //$mageProduct  = $this->_sellerProducts->load($item->getProductId(),'mageproduct_id');
                        }
                    }
                    if ($item->getProduct()->getSku() != 'wk_mp_ads_plan') {
                        //$sellerId = $mageProduct->getSellerId();
                        $shippingCost = $shippingCost + ($productObj->getShippingPriceToMygmbh() * $item->getQty());
                        $length = $length + ($productObj->getMygmbhShippingProductLength() * $item->getQty());
                        $width = $width + ($productObj->getMygmbhShippingProductWidth() * $item->getQty());
                        $height = $height + ($productObj->getMygmbhShippingProductHeight() * $item->getQty());
                        $weight = $weight + ($productObj->getWeight() * $item->getQty());
                        $price = $price + ($item->getBasePrice() * $item->getQty());
                    } else {
                        $notAcceptable = 1;
                        break;
                    }
                    
                }

                if (!$notAcceptable) {
                    $rate = $this->currencyFactory->create()->load($currencyCodeFrom)->getAnyRate($currencyCodeTo);

                    // $priceConverted = $price * $rate;

                    //get ISO 3166 country code
                    $countryISOCode = $this->getCountryISOCOde($countryId);
                    //get all shipping methods from LifeRay based on length, width, height, weight etc
                    $finalDropShipmentCost = $this->getSCCPrice($shippingCost,$length,$width,$height,$weight, $postalCode, $countryISOCode, $price, $multipleArticles);
                    return $finalDropShipmentCost;
                } else {
                    return 'ads';
                }
            } 
        }
        
    }
    public function getShippingCountry()
    {
        $quoteObj = $this->cart->getQuote();
        $shippingAddObj = $quoteObj->getShippingAddress();
        $countryId = $shippingAddObj->getCountryId();
        $countryArr = ['AT', 'BE', 'BG', 'HR', 'CY', 'CZ', 'DK', 'EE', 'FI', 'FR', 'DE', 'GR', 'HU', 'IE', 'IT', 'LV', 'LT', 'LU', 'MT', 'NL', 'PL', 'PT', 'RO', 'SK', 'SI', 'ES', 'SE', 'GB'];
        if (in_array($countryId, $countryArr)) {
            return true;
        } else {
            return false;
        }
    }

    public function getCurrentCurrency($price)
    {
        return $this->pricingHelper->currency($price, true, false);
    }
    public function getCountryISOCOde($countryCode)
    {
        $apiUrl = 'https://restcountries.eu/rest/v2/alpha/'.$countryCode;
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiUrl);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = json_decode(curl_exec($ch),true);
        curl_close ($ch);
        return $server_output['numericCode'];
    }

    public function getSCCPrice($shippingCost, $length, $width, $height, $weight, $postalCode, $countryISOCode, $price, $multipleArticles)
    {
        $weight = $weight*1000;
        // if ($multipleArticles) {
        //     $apiUrl = 'https://account.mygermany.com/web/content/sccws?p_p_id=sccws_WAR_SCCWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&_sccws_WAR_SCCWSportlet_p_v_g_id=10180&_sccws_WAR_SCCWSportlet_dataType=json&_sccws_WAR_SCCWSportlet_cmd=add&_sccws_WAR_SCCWSportlet_currentURL=%2Fweb%2Fcontent%2Fsccws&_sccws_WAR_SCCWSportlet_doAsUserId&deliveryFrom=276&deliveryTo='.$countryISOCode.'&zipCode='.$postalCode.'&weight_='.$weight.'&weightUnit=kg&dimLength_='.$length.'&dimUnit=cm&dimWidth_='.$width.'&dimHeight_='.$height.'&value_='.$price.'&insurance=no';
        // } else {
        //     $apiUrl = 'https://account.mygermany.com/web/content/sccws?p_p_id=sccws_WAR_SCCWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&_sccws_WAR_SCCWSportlet_p_v_g_id=10180&_sccws_WAR_SCCWSportlet_dataType=json&_sccws_WAR_SCCWSportlet_cmd=add&_sccws_WAR_SCCWSportlet_currentURL=%2Fweb%2Fcontent%2Fsccws&_sccws_WAR_SCCWSportlet_doAsUserId&deliveryFrom=276&deliveryTo='.$countryISOCode.'&zipCode='.$postalCode.'&weight='.$weight.'&weightUnit=kg&dimLength='.$length.'&dimUnit=cm&dimWidth='.$width.'&dimHeight='.$height.'&value='.$price.'&insurance=no';

        // }

        $apiUrl = 'https://account.mygermany.com/web/content/sccws?p_p_id=sccws_WAR_SCCWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&_sccws_WAR_SCCWSportlet_p_v_g_id=10180&_sccws_WAR_SCCWSportlet_dataType=json&_sccws_WAR_SCCWSportlet_cmd=add&_sccws_WAR_SCCWSportlet_currentURL=%2Fweb%2Fcontent%2Fsccws&_sccws_WAR_SCCWSportlet_doAsUserId&deliveryFrom=276&deliveryTo='.$countryISOCode.'&zipCode='.$postalCode.'&weight='.$weight.'&weightUnit=kg&dimLength='.$length.'&dimUnit=cm&dimWidth='.$width.'&dimHeight='.$height.'&value='.$price.'&insurance=no';
        

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiUrl);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = json_decode(curl_exec($ch),true);
        curl_close ($ch);
        if (isset($server_output['error'])) {
            return $server_output['error'];
        }
        $finalMethods = array();
        $methods = ['dhlPr', 'dhlEx', 'fedexPr', 'fedexEx'] ;
        foreach ($methods as $methodsVal) {
            $differMethods = $this->getDifferShippingMethods($server_output, $methodsVal);
            $shipping = $methodsVal.'Shipping';
            $workdays = $methodsVal.'Workdays';
            $insurance = $methodsVal.'Insurance';
            $prio = $methodsVal.'Prio';
            $riskCharge = $methodsVal.'RiskCharge';
            $declaration = $methodsVal.'Declaration';

            $keysArray = [$shipping, $workdays, $insurance, $prio];

            // if ($methodsVal == 'dhlPr') {
            //     $key = 'DHL Premium';
            // } else if($methodsVal == 'dhlEx'){
            //     $key = 'DHL Express';
            // } else if($methodsVal == 'fedexPr'){
            //     $key = 'FedEx Premium';
            // } else if($methodsVal == 'fedexEx'){
            //     $key = 'FedEx Economy';
            // }
            $finalMethods[$methodsVal] = $differMethods;
        }
        return $finalMethods;
    }

    public function getDifferShippingMethods($shippingArray, $shippingCode)
    {
        $filtered = array_filter(
            $shippingArray,
            function ($key) use ($shippingCode) {
                return preg_match ('/^'.$shippingCode.'(\w+)/i', $key);
            },
            ARRAY_FILTER_USE_KEY
        );
        return $filtered;
    }
}

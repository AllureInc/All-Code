<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\ShippingCostCalculator\Block;

/**
 * Mangoit ShippingCostCalculator Calculations Block.
 */
class Calculations extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $seller;
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollection;
     /**
     * @var array
     */
    private $countries;

    /**
    * @var \Magento\Directory\Model\Currency
    */
   protected $currencyModel;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\Marketplace\Model\Seller $seller,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        \Magento\Directory\Model\Currency $currencyModel,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->seller = $seller;
        $this->_countryCollection = $countryCollection;
        $this->currencyModel = $currencyModel;
        parent::__construct($context, $data);
    }

    /**
     * Returns countries array
     *
     * @return array
     */
    public function getCalculations()
    {
        $postData = $this->getRequest()->getParams();
        $countryIso = $this->getCountryISOCOde($postData['deliveryTo']);
        // $apiUrl = 'https://account.mygermany.com/web/content/sccws?p_p_id=sccws_WAR_SCCWSportlet&p_p_lifecycle=2&p_p_state=normal&p_p_mode=view&p_p_cacheability=cacheLevelPage&p_p_col_id=column-1&_sccws_WAR_SCCWSportlet_p_v_g_id=10180&_sccws_WAR_SCCWSportlet_dataType=json&_sccws_WAR_SCCWSportlet_cmd=add&_sccws_WAR_SCCWSportlet_currentURL=%2Fweb%2Fcontent%2Fsccws&_sccws_WAR_SCCWSportlet_doAsUserId&deliveryFrom=276&deliveryTo='.$countryISOCode.'&zipCode='.$postalCode.'&weight='.$weight.'&weightUnit=kg&dimLength='.$length.'&dimUnit=cm&dimWidth='.$width.'&dimHeight='.$height.'&value='.$price.'&insurance=no';
        
       $apiUrl = 'https://mygermany.com/api/calculateShipping?sentFrom=276&deliveryTo='.$countryIso.'&zipCode='.$postData['zipCode'].'&weight='.$postData['weight'].'&weightUnit='.$postData['weightUnit'].'&dimLength='.$postData['dimLength'].'&dimUnit='.$postData['dimUnit'].'&dimWidth='.$postData['dimWidth'].'&dimHeight='.$postData['dimHeight'].'&value='.$postData['value'].'&insurance='.$postData['insurance'].'&dropShipping='.$postData['drop_ship'].'&language=en-US';

        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL,$apiUrl);
        // receive server response ...
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        $server_output = json_decode(curl_exec($ch),true);
        curl_close ($ch);
        return $server_output;
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

    public function getFormatedPrice($price = 0)
    {
       $currencyCode = 'EUR';
       $currencySymbol = $this->currencyModel->load($currencyCode)->getCurrencySymbol();
       $precision = 2;   // for displaying price decimals 2 point
       //get formatted price by currency
       $formattedPrice = $this->currencyModel->format($price, ['symbol' => $currencySymbol, 'precision'=> $precision], false, false);
       return $formattedPrice;
    }

}

<?php
namespace Mangoit\Marketplace\Controller\Price;

class Calculation extends \Magento\Framework\App\Action\Action
{
    protected $_pageFactory;
    protected $_helper;
    protected $_commissionModel;
    protected $_session;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\View\Result\PageFactory $pageFactory,
        \Mangoit\Marketplace\Helper\Data $helper,
        \Mangoit\Vendorcommission\Model\TurnoverFactory $commissionModel,
        \Magento\Customer\Model\Session $session)
    {
        $this->_pageFactory = $pageFactory;
        $this->_helper = $helper;
        $this->_commissionModel = $commissionModel;
        $this->_session = $session;
        return parent::__construct($context);
    }

    public function execute()
    {
        $turnover = 0;
        $commissionRule = 0;
        if ( !empty($this->_session->getCustomer()->getId())  ) {
            $vendor_id = $this->_session->getCustomer()->getId();
            $data = $this->_helper->getVendorTurnOver($vendor_id);
            if (!empty($data)) {
                $turnover = $data['turnover'];
                $commissionRule = $data['commission'];
            }
        }
        $parameters = $this->getRequest()->getParams();

        if (isset($parameters['payment_ajax'])) {
            $option = $this->getPaymentMethodsData($parameters['payment_method']);
            echo json_encode($option);
            exit();
        } elseif (isset($parameters['country_ajax'])) {
            $country_option = $this->getCountryNameArray($parameters['paypal_zone']);
            echo json_encode($country_option);
            exit();
        } else {

            $shippingCharge = (float) $parameters['shipping_charge'];
            $shippingCost = (float) $parameters['num_of_articles'] * $shippingCharge;
            $commision_percent = 0;
            $total_item_price = (float) $parameters['item_price'] * (float) $parameters['num_of_articles'];
            $item_price = (float) $parameters['item_price'] * (float) $parameters['num_of_articles'];
            $model = $this->_commissionModel->create();
            $model->load(1);
            if (is_null($commissionRule) || ($commissionRule == 0)) {
                $rule = unserialize($model->getCommissionRule());
            } else {
                $rule = unserialize($commissionRule);
            }
                // print_r($rule);

            if (empty($turnover) || ($turnover == 0)) {
                $turnover = $item_price;
            }

            if (isset($rule[$parameters['product_type']])) {
                foreach ($rule[$parameters['product_type']] as $key => $value) {
                    $range = explode('-', $key);
                    if (in_array('<', $range)) {
                        if ( ($turnover > $range[0]) ) {
                            $commision_percent = (float) $value;
                        }
                    } else {                    
                        if ( ($turnover > $range[0]) && ($turnover <= $range[1]) ) {
                            $commision_percent = (float) $value;
                        }
                    }
                }
            }

            /* date: 13-11-2018 start */
            
            if (isset($parameters['selected_payment_method'])) {
                $payment_method = $parameters['selected_payment_method'];
            } else {
                $payment_method = 'other';                
            }

            if (isset($parameters['payment_option']) && ($parameters['selected_payment_method'] == 'paypal' )) {
                $payment_option = $parameters['payment_option'];
                $country_code = $parameters['country_name'];
            } elseif( isset($parameters['payment_option']) && ($parameters['selected_payment_method'] == 'credit_card' ) ) {
                $payment_option = $parameters['payment_option'];             
                $country_code = 0;
            } else {
                $payment_option = 0;
                $country_code = 0;
            }

            /* date: 13-11-2018 ends  */

            $item_price = (float)($item_price + $shippingCost);

            $adminAmount = (float) (($item_price * $commision_percent)/100);

            /* date: 13-11-2018 start   */
            $paymentFeesAmount = $this->getPaymentFees($item_price, $payment_method, $payment_option, $country_code);
            /* date: 13-11-2018 ends  */

            // $paymentFeesAmount = (float) (($item_price * 10)/100);

            $exchangeFeesAmount = (float) (($item_price * 1)/100);
            $totalFees = (float) ($adminAmount + $paymentFeesAmount + $exchangeFeesAmount);
            $item_price = (float) ($item_price - $totalFees);
            
            $resultArray = array(
                'itemCost'=> number_format($total_item_price, 2),
                'adminAmount'=> number_format($adminAmount, 2), 
                'shippingCost'=> number_format($shippingCost, 2), 
                'paymentFeesAmount'=> number_format($paymentFeesAmount, 2), 
                'exchangeFeesAmount'=> number_format($exchangeFeesAmount, 2), 
                'vendorAmount'=> number_format($item_price, 2), 
                'totalFees'=> number_format($totalFees, 2)
                );

            echo json_encode($resultArray);
            
        }
    } 

    /* date: 13-11-2018 starts  */

    public function getPaymentMethodsData($payment_method)
    { 
        if (($payment_method == 'paypal') || ($payment_method == 'credit_card')) {
            $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
            $paymentFeesCol = $objectManager->create('Mangoit\VendorPayments\Model\Paymentfees')->getCollection();

            switch ($payment_method) {
                case 'paypal':
                    $paymentFeesCol->addFieldToFilter('payment_method', 'paypal');
                    $paypalZone =  array(
                        'north_europe' => 'North Europe',
                        'europe_i' => 'Europe I',
                        'north_america' => 'North America',
                        'europe_ii' => 'Europe II',
                        'latin_america' => 'Latin America',
                        'apac'=> 'Apac',
                        'other'=> 'Other'
                        );
                    foreach ($paymentFeesCol->getData() as $item) {
                        if (is_null($item['effective_countries']) ) {
                           unset($paypalZone[$item['counrty_group']]);
                        }
                    }

                    return $paypalZone;

                    break;

                case 'credit_card':
                    return array(
                        'maestro' => 'Maestro',
                        'jbc' => 'JBC',
                        'visa' => 'Visa',
                        'amex' => 'Amex',
                        'diners' => 'Diners',
                        'unionpay' => 'UnionPay',
                        'alipay' => 'Alipay',
                        'googlepay' => 'GooglePay',
                        'applepay' => 'ApplePay',
                        );
                    # code...
                    break; 

                default:
                    return array(''=> 'select');
                    break;
            }
        }
    }

    public function getCountryNameArray($zone)
    {
        $result_array = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $countryModel = $objectManager->create('Magento\Directory\Model\Country');
        $paymentModel = $objectManager->create('Mangoit\VendorPayments\Model\Paymentfees');
        $loadedPayementModel =  $paymentModel->load($zone, 'counrty_group');
        // $loadedPayementModel =  $paymentModel->load('abdgghh', 'counrty_group');
        if (!$loadedPayementModel->isEmpty()) {
            $saved_countries = $loadedPayementModel->getEffectiveCountries();
            $saved_country_array = explode(',', $saved_countries);
            if (!empty($saved_country_array)) {
                foreach ($saved_country_array as $key => $value) {
                    if (strlen($value) > 1) {
                        $country_name = $countryModel->loadByCode($value);
                        $result_array[$value] = $country_name->getName();
                    }
                }
            }
        }

        return $result_array;
    }

    public function getPaymentFees($item_price, $payment_method, $payment_option, $country_code)
    {
        $countryArray = array();
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $paymentFeesCol = $objectManager->create('Mangoit\VendorPayments\Model\Paymentfees')
                    ->getCollection();
        if ($payment_method == 'paypal') 
        {
            $paymentFeesCol->addFieldToFilter('payment_method', 'paypal');
            foreach ($paymentFeesCol->getData() as $item) {
               $effective_countries =  explode(',', $item['effective_countries']);
               if ( (in_array($country_code, $effective_countries)) && ($payment_option == $item['counrty_group']) ) 
               {
                $fixedAmntOfCost = $item['cost_per_tans'];
                $percentOfCost = $item['percent_of_total_per_tans'];                   
               }

            }

            $costToMinus = (float) (($item_price * (float)$percentOfCost) / 100);            
            $costToMinus = ( (float) $costToMinus + (float) $fixedAmntOfCost );

        } elseif ($payment_method == 'credit_card') 
        {
            $paymentFeesCol->addFieldToFilter('payment_method', 'credit_card');

            foreach ($paymentFeesCol->getData() as $item) {
               if ( ($payment_option == $item['card_type']) ) 
               {
                $fixedAmntOfCost = $item['cost_per_tans'];
                $percentOfCost = $item['percent_of_total_per_tans'];                   
               }

            }

            $costToMinus = (float) (($item_price * (float)$percentOfCost) / 100);            
            $costToMinus = ( (float) $costToMinus + (float) $fixedAmntOfCost );

        } elseif ($payment_method == 'crypto') 
        {
            $paymentFeesCol->addFieldToFilter('payment_method', 'crypto');
            $percentOfCost = $paymentFeesCol->getFirstItem()->getPercentOfTotalPerTans();
            $costToMinus = (float) (($item_price * (float)$percentOfCost) / 100);  
            // $costToMinus = ($item_price/100) * (double)$percentOfCost;            
        } else {
            $paymentFeesCol->addFieldToFilter('payment_method', 'other');
            $percentOfCost = $paymentFeesCol->getFirstItem()->getPercentOfTotalPerTans();
            $costToMinus = (float) (($item_price * (float)$percentOfCost) / 100);  
            // $costToMinus = ($item_price/100) * (double)$percentOfCost;
        }

        return $costToMinus;

    }

    /* date: 13-11-2018 ends  */
}
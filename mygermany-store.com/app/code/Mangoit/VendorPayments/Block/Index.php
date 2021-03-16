<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\VendorPayments\Block;

use Mangoit\VendorPayments\Model\Paymentfees;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Magento\Directory\Model\ResourceModel\Country\CollectionFactory
     */
    protected $_countryCollection;

    /**
      * @var \Mangoit\VendorPayments\Model\Paymentfees
      */
    protected $_paymentFeesModel;

    protected $_returnArr = [
            'paypal' => [
                    'north_europe' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ],

                    'europe_i' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ],

                    'north_america' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ],

                    'europe_ii' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ],

                    'latin_america' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ],

                    'apac' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ],

                    'other' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ],

                            'effective_countries' => []

                        ]

                ],

            'credit_card' => [
                    'maestro' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'jcb' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'visa' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'amex' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'diners' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'unionpay' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'alipay' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'googlepay' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ],

                    'applepay' => [
                            'cost_per_t' => [
                                    'fixed' => 0.35,
                                    'in_percent' => 6.00
                                ]

                        ]

                ],

            'crypto' => [
                    'cost_per_t' => [
                            'in_percent' => 0.35
                        ]

                ],

            'other' => [
                    'cost_per_t' => [
                            'in_percent' => 0.35
                        ]

                ]
        ];

    /**
     * @var array
     */
    private $countries;

    /**
     * @var \Webkul\Marketplace\Model\Saleperpartner
     */
    private $_salesPerPartner;
    protected $_customerSession;

    /**
    * @var \Magento\Directory\Model\Currency
    */
   protected $currencyModel;

   protected $storeManageInterface;
   protected $scopeConfigInterface;
   protected $globalCommission;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        Paymentfees $paymentFeesModel,
        \Webkul\Marketplace\Model\Saleperpartner $salesPerPartner,
        \Magento\Customer\Model\SessionFactory $customerSession,
        \Magento\Directory\Model\Currency $currencyModel,
        \Magento\Store\Model\StoreManagerInterface $storeManageInterface,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfigInterface,
        \Mangoit\Vendorcommission\Model\Turnover $globalCommission,
        array $data = []
    ) {
        $this->_countryCollection = $countryCollection;
        $this->_paymentFeesModel = $paymentFeesModel;
        $this->_salesPerPartner = $salesPerPartner;
        $this->_customerSession = $customerSession->create();
        $this->currencyModel = $currencyModel;
        $this->storeManageInterface = $storeManageInterface;
        $this->scopeConfigInterface = $scopeConfigInterface;
        $this->globalCommission = $globalCommission;
        parent::__construct($context, $data);
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

    public function getVendorCommissionDetails()
    {
        $vendorCommission = [];
        if ($this->_customerSession->isLoggedIn()) {
            $customerId = $this->_customerSession->getId();
            $sellerData = $this->_salesPerPartner->load($customerId,'seller_id');
            $serializeRule = $sellerData->getCommissionRule();
            $vendorCommission['turnover'] = $sellerData->getSellerTurnover();
            if (!empty($serializeRule)) {
                $vendorCommission['commission_rule'] = unserialize($serializeRule);
            } else {
                $rangeArray = $this->scopeConfigInterface->getValue('marketplace/commission_setting/ranges');
                $globalSetting = $this->globalCommission->getCollection()->getFirstItem();
                $settingArr = $globalSetting->getCommissionRule();
                $vendorCommission['commission_rule'] = unserialize($settingArr);
            }
        }
        return $vendorCommission;
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
    /**
     * @param Decimal $price
     *
     * @return [type] [description]
     */
    public function getCurrencySymbol()
    {
        $currencyCode = 'EUR';
        $currencyCode = $this->storeManageInterface->getStore()->getBaseCurrencyCode();
        $currencySymbol = $this->currencyModel->load($currencyCode)->getCurrencySymbol();
        return $currencySymbol;
    }
}

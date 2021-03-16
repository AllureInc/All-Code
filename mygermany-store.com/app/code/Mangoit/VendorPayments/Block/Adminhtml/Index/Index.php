<?php

namespace Mangoit\VendorPayments\Block\Adminhtml\Index;

use Mangoit\VendorPayments\Model\Paymentfees;
use Mangoit\VendorPayments\Model\Exchangefees;

class Index extends \Magento\Backend\Block\Widget\Container
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
     * @var array
     */
    private $exchangeCharges;

    public function __construct(
    	\Magento\Backend\Block\Widget\Context $context,
    	\Magento\Directory\Model\ResourceModel\Country\CollectionFactory $countryCollection,
        Paymentfees $paymentFeesModel,
        Exchangefees $exchangeFeesModel,
    	array $data = []
    ) {
    	$this->_countryCollection = $countryCollection;
        $this->_paymentFeesModel = $paymentFeesModel;
        $this->_exchangeFeesModel = $exchangeFeesModel;
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

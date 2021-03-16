<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\ShippingCostCalculator\Block;

/**
 * Mangoit Marketplace Sellerlist Block.
 */
class Index extends \Magento\Framework\View\Element\Template
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
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->seller = $seller;
        $this->_countryCollection = $countryCollection;
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
     * Returns weight array
     *
     * @return array
     */
    public function getWeights()
    {
        $weights = [];
        for ($count = 1; $count <= 70; $count++) { 
            $weights[$count]['value'] = $count*1000;
            $weights[$count]['label'] = 'up to '.$count;
        }
        return $weights;
    }

}

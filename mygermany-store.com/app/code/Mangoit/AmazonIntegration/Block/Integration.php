<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\AmazonIntegration\Block;

/**
 * Mangoit AmazonIntegration Sellerlist Block.
 */
use \Webkul\AmazonMagentoConnect\Model\Config\Source;

class Integration extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $seller;
    protected $accounts;

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
        \Webkul\AmazonMagentoConnect\Model\Accounts $accounts,
        Source\CategoriesList $categoriesList,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->seller = $seller;
        $this->accounts = $accounts;
        $this->categoriesList = $categoriesList;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getCustomerDetails()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $sellerData = $this->seller->getCollection()
            ->addFieldToFilter('seller_id', $customerId)
            ->addFieldToFilter('store_id', 0);
        return $sellerData;
    }  

    public function getAmazonAccountDetail()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $sellerAcc = $this->accounts->load($customerId,'magento_seller_id');
        return $sellerAcc;
    }
    public function getCategoryList()
    {
        return $this->categoriesList->toOptionArray();
    }
}

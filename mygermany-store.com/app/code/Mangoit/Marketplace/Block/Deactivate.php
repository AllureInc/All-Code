<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\Marketplace\Block;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Deactivate extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $seller;

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
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->seller = $seller;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getCustomerDetails()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        if ($customerId) {
            $sellerData = $this->seller->getCollection()
                ->addFieldToFilter('seller_id', $customerId)
                ->addFieldToFilter('store_id', 0);
            $customer = $this->_customerRepositoryInterface->getById($customerId);
            
            $deactivate = false;
            if (!empty($sellerData)) {
                $deactivate_val = $sellerData->getFirstItem()->getAccountDeactivate();
                //$deactivate_val = $customer->getCustomAttribute('deactivated_account')->getValue();
                if ($deactivate_val) {
                    $deactivate = true;
                } else {
                    $deactivate = false;
                }
            } else {
                $deactivate = false;
            } 
        } else {
            $deactivate = false;
        }
        return $deactivate;
    }
}

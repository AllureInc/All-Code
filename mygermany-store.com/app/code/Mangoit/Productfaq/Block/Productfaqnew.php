<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\Productfaq\Block;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Productfaqnew extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $seller;
    protected $sellerProduct;
    protected $product;

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
        \Webkul\Marketplace\Model\Product $sellerProduct,
        \Magento\Catalog\Model\Product $product,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->seller = $seller;
        $this->sellerProduct = $sellerProduct;
        $this->product = $product;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getSellerProducts()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $options = array();
        if ($customerId) {
            $sellerProductColl = $this->sellerProduct->getCollection()
                ->addFieldToFilter('seller_id', $customerId);
            if (!empty($sellerProductColl)) {
                if ($sellerProductColl->count() > 0) {
                    foreach($sellerProductColl as $key => $value){
                        $productObj = $this->product->load($value->getMageproductId());
                        $arr[$value->getMageproductId()] = $productObj->getName();
                    }
                    foreach ($arr as $key => $val){
                        $options[]= array('value' => $key, 'label' => $val);
                    }
                }
                return $options;
            } else {
                return $options;
            }
        }
    }
}

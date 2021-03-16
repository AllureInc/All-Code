<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\AmazonIntegration\Block;

use Webkul\AmazonMagentoConnect\Model\ProductMap;
/**
 * Webkul AmazonIntegration Sellerlist Block.
 */
class Exportproduct extends \Magento\Framework\View\Element\Template
{

    protected $customerSession;
    protected $_customerRepositoryInterface;
    protected $sellerProduct;
    protected $_productloader; 

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Customer\Api\CustomerRepositoryInterface $customerRepositoryInterface,
        \Webkul\Marketplace\Model\Product $sellerProduct,
        \Magento\Catalog\Model\Product $_productloader,
        ProductMap $productMap,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->_customerRepositoryInterface = $customerRepositoryInterface;
        $this->sellerProduct = $sellerProduct;
        $this->_productloader = $_productloader;
        $this->productMap = $productMap;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getSellerProductCollection()
    {
        $customerId = $this->customerSession->getCustomer()->getId();
        $accountId = $this->getRequest()->getParam('id');
        $mappedProId = [];
         //get values of current page
        $page = ($this->getRequest()->getParam('p'))? $this->getRequest()->getParam('p') : 1;
        //get values of current limit
        $pageSize = ($this->getRequest()->getParam('limit'))? $this->getRequest()->getParam('limit') : 5;
        $sellerProductData = $this->sellerProduct->getCollection()
        ->addFieldToFilter('seller_id', $customerId)
        ->addFieldToFilter('status', 1);
        $collection = $this->productMap->getCollection();
        
        $collection->addFieldToFilter('mage_amz_account_id', ['eq' => $accountId]);
        foreach ($collection as $product) {
            $mappedProId[] = $product->getMagentoProId();
        }
        if (!empty($mappedProId)) {
            $sellerProductData->addFieldToFilter(
                'mageproduct_id',
                ['nin'=>$mappedProId]
            );
        }
        // $sellerProductData->setOrder('title','ASC');
        $sellerProductData->setPageSize($pageSize);
        $sellerProductData->setCurPage($page);
        return $sellerProductData;
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->load($id);
    }
    protected function _prepareLayout()
    {
       parent::_prepareLayout();

        if ($this->getSellerProductCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'fme.news.pager'
            )->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)->setCollection(
                $this->getSellerProductCollection()
            );
            $this->setChild('pager', $pager);
           $this->getSellerProductCollection()->load();
        }
        return $this;
    }

    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }
}

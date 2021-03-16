<?php
/**
 * Mangoit Software.
 *
 */

namespace Mangoit\Productfaq\Block;

/**
 * Webkul Marketplace Sellerlist Block.
 */
class Productfaqedit extends \Magento\Framework\View\Element\Template
{

    
     protected $productFaq;
     protected $_productloader; 
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Ced\Productfaq\Model\Productfaq $productFaq,
        \Magento\Catalog\Model\ProductFactory $_productloader,
        array $data = []
    ) {
        $this->productFaq = $productFaq;
        $this->_productloader = $_productloader;
        parent::__construct($context, $data);
    }

    /**
     * @return bool|\Magento\Ctalog\Model\ResourceModel\Product\Collection
     */
    public function getFaqDetails()
    {
        $id = $this->getRequest()->getParam('id');
        $faq = $this->productFaq->load($id);
        if (!empty($faq)) {
            return $faq;
        } else {
            return '';
        }
    }

    public function getLoadProduct($id)
    {
        return $this->_productloader->create()->load($id);
    }
}

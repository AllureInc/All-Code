<?php 
namespace Mangoit\Productfaq\Block\Product\View;
/**
 * Mangoit
 *
 */
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Model\Product;
use Ced\Productfaq\Helper\Data;
use Ced\Productfaq\Model\ProductfaqFactory;
use Ced\Productfaq\Model\LikesFactory;

class Description extends \Ced\Productfaq\Block\Product\View\Description
{
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Registry                      $registry
     * @param array                                            $data
     */
    protected $_storeManager;
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Registry $registry,
        ProductfaqFactory $productFactory,
        LikesFactory $likesFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $data = []
    ) {
        $this->_storeManager = $storeManager;
        parent::__construct($context, $registry, $productFactory, $likesFactory,$data);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        /**
         * @var \Magento\Theme\Block\Html\Pager 
        */
        $pager = $this->getLayout()->createBlock(
            'Magento\Theme\Block\Html\Pager',
            'ced.faq.list.pager'
        );
        $defaultStoreId = $this->_storeManager->getStore()->getId();
        $stores = [$defaultStoreId];
        $collectionObj = $this->getCollection()
        ->addFieldToFilter(['store_id', 'vendor_id'],
        [
            ['in' => $stores],
            ['eq' => 0],
        ]);

        $pager->setAvailableLimit(array(5=>5,10=>10,15=>15))->setShowPerPage(true)
            ->setShowAmounts(true)
            ->setCollection($collectionObj);
        $this->setChild('pager', $pager);
        $this->getCollection()->load();

        return $this;
    }
}
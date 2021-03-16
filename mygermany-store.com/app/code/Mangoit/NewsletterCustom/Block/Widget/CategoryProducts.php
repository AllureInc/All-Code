<?php
namespace Mangoit\NewsletterCustom\Block\Widget;

use Magento\Widget\Block\BlockInterface;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory as ProductCollectionFactory;
use Magento\Catalog\Model\CategoryFactory;

class CategoryProducts extends \Magento\Framework\View\Element\Template implements BlockInterface
{
	protected $_template = 'widget/categoryproducts.phtml';
	 
	/**
     * Default value for products count that will be shown
     */
    const DEFAULT_PRODUCTS_COUNT = 10;

    /**
     * Store Manager Obj
     *
     * @var Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;

    /**
     * Category Factory Obj
     *
     * @var Magento\Catalog\Model\CategoryFactory
     */
	protected $_categoryFactory;
 
    /**
     * Product Collection Factory
     *
     * @var Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    protected $_productCollectionFactory;

    /**
     * Image helper
     *
     * @var Magento\Catalog\Helper\Image
     */
    protected $_imageHelper;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    public function __construct(
    	\Magento\Catalog\Block\Product\Context $productContext,
    	Context $context,
    	StoreManagerInterface $storeManager,
    	ProductCollectionFactory $productCollectionFactory,
    	CategoryFactory $categoryFactory
    ) {
 
        $this->_storeManager = $storeManager;
        $this->_categoryFactory = $categoryFactory;
    	$this->_productCollectionFactory = $productCollectionFactory;
    	$this->_imageHelper = $productContext->getImageHelper();
    	$this->_cartHelper = $productContext->getCartHelper();
        parent::__construct($context);
    }
 
    /**
     * Get value of widgets' title parameter
     *
     * @return mixed|string
     */
    public function getTitle()
    {
        return 'Category';
        // return $this->getData('title');
    }
 
    /**
     * Retrieve Category ids
     *
     * @return string
     */
    public function getCategoryIds()
    {
        if ($this->getData('categoryids') == '') {
            return $this->_storeManager->getStore()->getRootCategoryId();
        }
        return $this->getData('categoryids');
    }


    /**
     * Get the configured limit of products
     * @return int
     */
    public function getProductLimit() {
		if($this->getData('productcount') == ''){
			return self::DEFAULT_PRODUCTS_COUNT;
		}
        return $this->getData('productcount');
    }
 
    /**
     * Image helper Object
     */
 	public function imageHelperObj(){
        return $this->_imageHelper;
    }

    /**
     * Get the add to cart url
     * @return string
     */
     public function getAddToCartUrl($product, $additional = [])
    {
		return $this->_cartHelper->getAddUrl($product, $additional);
    }

    public function getProductCollection()
	{
	    $category_ids = explode("/", $this->getCategoryIds());
        $categoryId = isset($category_ids[1]) ? $category_ids[1] : false;
	    $category = $this->_categoryFactory->create()->load($categoryId);
        $limit = $this->getProductLimit();

	    $collection = $this->_productCollectionFactory->create();
	    $collection->addAttributeToSelect('*')
	    	->addCategoryFilter($category)
	    	->addAttributeToFilter('visibility', \Magento\Catalog\Model\Product\Visibility::VISIBILITY_BOTH)
	    	->addAttributeToFilter('status',\Magento\Catalog\Model\Product\Attribute\Source\Status::STATUS_ENABLED)
            ->setPageSize(
                $limit
            );
	    return $collection;
	}

	/**
     * Return HTML block with price
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param string $priceType
     * @param string $renderZone
     * @param array $arguments
     * @return string
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function getProductPriceHtml(
        \Magento\Catalog\Model\Product $product,
        $priceType = null,
        $renderZone = \Magento\Framework\Pricing\Render::ZONE_ITEM_LIST,
        array $arguments = []
    ) {
        if (!isset($arguments['zone'])) {
            $arguments['zone'] = $renderZone;
        }
        $arguments['zone'] = isset($arguments['zone'])
            ? $arguments['zone']
            : $renderZone;
        $arguments['price_id'] = isset($arguments['price_id'])
            ? $arguments['price_id']
            : 'old-price-' . $product->getId() . '-' . $priceType;
        $arguments['include_container'] = isset($arguments['include_container'])
            ? $arguments['include_container']
            : true;
        $arguments['display_minimal_price'] = isset($arguments['display_minimal_price'])
            ? $arguments['display_minimal_price']
            : true;

            /** @var \Magento\Framework\Pricing\Render $priceRender */
        $priceRender = $this->getLayout()->getBlock('product.price.render.default');

        $price = '';
        if ($priceRender) {
            $price = $priceRender->render(
                \Magento\Catalog\Pricing\Price\FinalPrice::PRICE_CODE,
                $product,
                $arguments
            );
        }
        return $price;
    }
}

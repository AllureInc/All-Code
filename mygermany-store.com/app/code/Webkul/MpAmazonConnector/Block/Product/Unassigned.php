<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Product;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Webkul\MpEbaymagentoconnect\Model\ResourceModel\Productmap\CollectionFactory;
use Magento\Catalog\Model\Category;
use Magento\Catalog\Helper\Category as CategoryHelper;
use Webkul\Marketplace\Helper\Data as MpDataHelper;

class Unassigned extends ProductSyncList
{
    private $_mapedProcollection;
    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface $mappedProductRepository,
        ProductRepositoryInterface $productRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        Category $category,
        CategoryHelper $categoryHelper,
        MpDataHelper $mpDataHelper,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Marketplace\Block\Product\Create $mpProductCreate,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->category = $category;
        $this->categoryHelper = $categoryHelper;
        $this->mpDataHelper = $mpDataHelper;
        $this->mpProductCreate = $mpProductCreate;
        $this->_storeManager = $context->getStoreManager();
        parent::__construct($context, $customerSession, $mappedProductRepository, $productRepository, $helper, $data);
    }

    /**
     * getUnAssignedProList
     * @return Webkul\MpEbaymagentoconnect\Model\ResourceModel\Productmap\CollectionFactory
     */
    public function getUnAssignedProList()
    {
        if ($this->getMappedProducts()) {
            $this->_mapedProcollection = $this->getMappedProducts()->addFieldToFilter('assign', 0);
        }
        return $this->_mapedProcollection;
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getMappedProducts()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ebayconnect.unassigpro.list.pager'
            )->setCollection(
                $this->getUnAssignedProList()
            );
            $this->setChild('pager', $pager);
        }
        return $this;
    }

    /**
     * getProAssignToCategoryUrl
     * @return string
     */
    public function getProAssignToCategoryUrl()
    {
        return $this->getUrl(
            'mpamazonconnect/product/assigntocate',
            ['_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * Retrieve categories tree
     *
     * @param string|null $filter
     * @return array
     */
    public function getCategoriesTree($filter = null)
    {
        return $this->mpProductCreate->getCategoriesTree();
    }

    /**
     * getCategoryObj
     * @return array
     */
    public function getCategories()
    {
        $allowedCats = $this->mpDataHelper->getAllowedCategoryIds();
        if ($allowedCats) {
            $categories = explode(',', trim($allowedCats));
        } else {
            $categories = $this->categoryHelper->getStoreCategories();
        }
        return $categories;
    }

    /**
     * isChildCategory
     * @param Category $category
     * @return boolean
     */
    public function isChildCategory($category)
    {
        $childCats = $this->category->getAllChildren($category);
        return count($childCats)-1 > 0 ? true : false;
    }

    /**
     * getCategoryObj
     * @param int $catId
     * @return array
     */
    public function getCategory($catId)
    {
        return $this->category->load($catId);
    }
}

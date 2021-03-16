<?php
namespace Mangoit\ReportsPatch\Block\Adminhtml\Wishlist;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
	/**
     * @var \Magento\Reports\Model\ResourceModel\Quote\CollectionFactory
     */
    protected $quoteItemCollectionFactory;

    /**
     * @var \Magento\Quote\Model\QueryResolver
     */
    protected $queryResolver;

    /**
     * @var \Magento\Wishlist\Helper\Data
     */
    protected $wishListHelper;

    /**
     * @var \Magento\Reports\Model\ResourceModel\Wishlist\Product\CollectionFactory
     */
    protected $_productsFactory;
    protected $wishListItemsColl;

	/**
     * Stores current currency code
     *
     * @var array
     */
    protected $_currentCurrencyCode = null;

    /**
     * Ids of current stores
     *
     * @var array
     */
    protected $_storeIds = [];

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Reports\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory
     * @param \Magento\Quote\Model\QueryResolver $queryResolver
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Quote\Model\QueryResolver $queryResolver,
        \Magento\Wishlist\Helper\Data $wishListHelper,
        \Magento\Reports\Model\ResourceModel\Wishlist\Product\CollectionFactory $productsFactory,
        \Magento\Wishlist\Model\ResourceModel\Item\Collection $wishListItemsColl,
        \Magento\Reports\Model\ResourceModel\Quote\Item\CollectionFactory $quoteItemCollectionFactory,
        array $data = []
    ) {
        $this->quoteItemCollectionFactory = $quoteItemCollectionFactory;
        $this->queryResolver = $queryResolver;
        $this->wishListHelper = $wishListHelper;
        $this->_productsFactory = $productsFactory;
        $this->wishListItemsColl = $wishListItemsColl;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('gridProducts');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid
     */
    protected function _prepareCollection()
    {
        /** @var \Magento\Wishlist\Model\ResourceModel\Wishlist\Collection $collection */

        // join "Magento\Wishlist\Model\ResourceModel\Item\Collection" and "Magento\Wishlist\Model\ResourceModel\Wishlist\Collection"

        // $this->wishListItemsColl wishlist_item
        // $collection wishlist

        // $collection = $this->getWishlist();

		$this->wishListItemsColl->getSelect()->join(
            ['ot' => $this->wishListItemsColl->getTable('wishlist')],
            "main_table.wishlist_id = ot.wishlist_id"
        )->join(
            ['cust' => $this->wishListItemsColl->getTable('customer_entity')],
            "ot.customer_id = cust.entity_id",
            ['fullname' => "CONCAT(firstname, ' ', lastname)"]
        );

        $this->setCollection($this->wishListItemsColl);
        return parent::_prepareCollection();
    }

    /**
     * @return \Magento\Wishlist\Model\ResourceModel\Wishlist\Collection
     */
    public function getWishlist()
    {
        $collection = $this->wishListHelper->getWishlist()->getCollection();

        return $collection;
    }

    /**
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'product_id',
            [
                'header' => __('Product ID'),
                'align' => 'right',
                'index' => 'product_id',
                'sortable' => false,
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'name',
            [
                'header' => __('Product Name'),
                'index' => 'name',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        $this->addColumn(
            'customer_name',
            [
                'header' => __('Customer Name'),
                'index' => 'fullname',
                'sortable' => false,
                'header_css_class' => 'col-product',
                'column_css_class' => 'col-product'
            ]
        );

        $currencyCode = $this->getCurrentCurrencyCode();

        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'type' => 'currency',
                'currency_code' => $currencyCode,
                'index' => 'price',
                'sortable' => false,
                'renderer' => \Magento\Reports\Block\Adminhtml\Grid\Column\Renderer\Currency::class,
                'rate' => $this->getRate($currencyCode),
                'header_css_class' => 'col-price',
                'column_css_class' => 'col-price'
            ]
        );

        $this->addColumn(
            'added_at',
            [
                'header' => __('Added At'),
                'align' => 'right',
                'index' => 'added_at',
                'sortable' => false,
                'header_css_class' => 'col-carts',
                'column_css_class' => 'col-carts'
            ]
        );

        $this->setFilterVisibility(false);

        $this->addExportType('*/*/exportProductCsv', __('CSV'));
        $this->addExportType('*/*/exportProductExcel', __('Excel XML'));

        return parent::_prepareColumns();
    }


    /**
     * @param \Magento\Framework\DataObject $row
     *
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl('catalog/product/edit', ['id' => $row->getProductId()]);
    }

    /**
     * StoreIds setter
     * @codeCoverageIgnore
     *
     * @param array $storeIds
     * @return $this
     */
    public function setStoreIds($storeIds)
    {
        $this->_storeIds = $storeIds;
        return $this;
    }

    /**
     * Retrieve currency code based on selected store
     *
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        if ($this->_currentCurrencyCode === null) {
            reset($this->_storeIds);
            $this->_currentCurrencyCode = count(
                $this->_storeIds
            ) > 0 ? $this->_storeManager->getStore(
                current($this->_storeIds)
            )->getBaseCurrencyCode() : $this->_storeManager->getStore()->getBaseCurrencyCode();
        }
        return $this->_currentCurrencyCode;
    }

    /**
     * Get currency rate (base to given currency)
     *
     * @param string|\Magento\Directory\Model\Currency $toCurrency
     * @return float
     */
    public function getRate($toCurrency)
    {
        return $this->_storeManager->getStore()->getBaseCurrency()->getRate($toCurrency);
    }

}

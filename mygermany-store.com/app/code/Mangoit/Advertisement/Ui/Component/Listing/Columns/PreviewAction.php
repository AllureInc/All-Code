<?php


namespace Mangoit\Advertisement\Ui\Component\Listing\Columns;

use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;
use Magento\Framework\Url;

class PreviewAction extends Column
{
    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    protected $categoryRepository;

    protected $_productCollectionFactory;

    protected $getStoreCodeById = [];

    protected $urls = [
            1 => 'home', // Home Seller Ads Page Top
            2 => 'home', // Home Seller Popup Ads
            3 => 'home', // Home Seller Ads Page Bottom Container
            4 => 'category', // Category Seller Ads Page Top
            5 => 'category', // Category Seller Ads Page Bottom Container
            6 => 'category', // Category Seller Ads Main
            7 => 'category', // Category Seller Ads Div Sidebar Main Before
            8 => 'category', // Category Seller Ads Div Sidebar Main After
            9 => 'product', // Catalog Product Seller Ads Page Top
            10 => 'product', // Catalog Product Seller Ads Page Bottom Container
            11 => 'product', // Home Seller Ads Product Main Info
            12 => 'search', // Catalog Search Seller Ads Page Top
            13 => 'search', // Catalog Search Seller Ads Page Bottom Container
            14 => 'search', // Catalog Search Seller Ads Main
            15 => 'search', // Catalog Search Seller Ads div Sidebar Main Before
            16 => 'search', // Catalog Search Seller Ads div Sidebar Main After
            17 => 'checkout_cart', // Checkout cart Seller Ads Page Top
            18 => 'checkout_cart', // Checkout cart Seller Ads Page bottom Container
            19 => 'checkout', // Checkout Seller Ads Page Top
            20 => 'checkout', // Checkout Seller Ads Page bottom Container
        ];

    /**
     * @param ContextInterface   $context
     * @param UiComponentFactory $uiComponentFactory
     * @param UrlInterface       $urlBuilder
     * @param array              $components
     * @param array              $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        Url $urlBuilder,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Catalog\Model\Category $categoryRepository,
        \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory $productCollectionFactory,
        array $components = [],
        array $data = []
    ) {
    
        $this->_urlBuilder = $urlBuilder;
        $this->storeManager = $storeManager;
        $this->categoryRepository = $categoryRepository;
        $this->_productCollectionFactory = $productCollectionFactory;

        $stores = $this->storeManager->getStores(true, false);
        foreach($stores as $store){
            $this->getStoreCodeById[$store->getId()] = $store->getCode();
        }
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source
     *
     * @param  array $dataSource
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            // $storeId = $this->context->getFilterParam('store_id');
            // echo "<pre>";
            foreach ($dataSource['data']['items'] as &$item) {
                $storeId = $item['store_id'];
                // print_r($item);die;
                $pageName = isset($this->urls[$item['block_position']])?$this->urls[$item['block_position']] :'';
                if($pageName == 'category') {
                    $url = $this->getCategoryUrl().'?is_admin=1&block='.urlencode(base64_encode($item['id']));
                } elseif ($pageName == 'product') {
                   $url = $this->getProductUrl($storeId, urlencode(base64_encode($item['id'])));
                } elseif ($pageName == 'home') {
                   $url = $this->getHomeUrl($storeId, urlencode(base64_encode($item['id'])));
                } elseif ($pageName == 'search') {
                   $url = $this->getSearchUrl($storeId, urlencode(base64_encode($item['id'])));
                } elseif ($pageName == 'checkout_cart') {
                   $url = $this->getCartUrl($storeId, urlencode(base64_encode($item['id'])));
                } elseif ($pageName == 'checkout') {
                   $url = $this->getCheckoutUrl($storeId, urlencode(base64_encode($item['id'])));
                } else {
                   $url = $this->getHomeUrl($storeId, urlencode(base64_encode($item['id'])));
                }
                $item[$this->getData('name')]['preview'] = [
                    'href' => $url,
                    'label' => __('Preview'),
                    'hidden' => false,
                    'target' => '_blank',
                ];
            }
        }
        return $dataSource;
    }

    public function getCategoryUrl()
    {
        return $this->categoryRepository->getCollection()
            ->addFieldToFilter('level', ['gteq' => 3])
            ->getFirstItem()->getUrl();
    }

    public function getProductUrl($storeId, $blockId)
    {
        $collection =  $this->_productCollectionFactory->create()
                ->addAttributeToSelect('*')
                ->addAttributeToFilter('status', array('in'=>array(1,2)))
                ->load()->getFirstItem();
        $storeParam = isset($this->getStoreCodeById[$storeId]) ? $this->getStoreCodeById[$storeId] : '';

        $routeParams = [ '_nosid' => true, '_query' => ['___store' => $storeParam, 'is_admin' => 1,'is_admin' => 1,'block' => $blockId]];
        $routeParams['id'] = $collection->getId();
        $routeParams['s'] = $collection->getUrlKey();
        $url = $this->_urlBuilder->getUrl('catalog/product/view',$routeParams);

        return $url;
    }

    public function getHomeUrl($storeId, $blockId)
    {
        $storeParam = isset($this->getStoreCodeById[$storeId]) ? $this->getStoreCodeById[$storeId] : '';
        $routeParams = [ '_nosid' => true, '_query' => ['___store' => $storeParam, 'is_admin' => 1,'is_admin' => 1,'block' => $blockId]];
        $url = $this->_urlBuilder->getUrl('',$routeParams);
        return $url;
    }

    public function getSearchUrl($storeId, $blockId)
    {
        $storeParam = isset($this->getStoreCodeById[$storeId]) ? $this->getStoreCodeById[$storeId] : '';
        $routeParams = [ '_nosid' => true, '_query' => ['___store' => $storeParam, 'q' => 'pro','is_admin' => 1,'is_admin' => 1,'block' => $blockId]];
        $url = $this->_urlBuilder->getUrl('search/product/',$routeParams);
        return $url;
    }

    public function getCartUrl($storeId, $blockId)
    {
        $storeParam = isset($this->getStoreCodeById[$storeId]) ? $this->getStoreCodeById[$storeId] : '';
        $routeParams = [ '_nosid' => true, '_query' => ['___store' => $storeParam, 'is_admin' => 1,'is_admin' => 1,'block' => $blockId]];
        $url = $this->_urlBuilder->getUrl('advertisement/block/cartpreview',$routeParams);
        return $url;
    }

    public function getCheckoutUrl($storeId, $blockId)
    {
        $storeParam = isset($this->getStoreCodeById[$storeId]) ? $this->getStoreCodeById[$storeId] : '';
        $routeParams = [ '_nosid' => true, '_query' => ['___store' => $storeParam, 'type' => 'checkout','is_admin' => 1,'is_admin' => 1,'block' => $blockId]];
        $url = $this->_urlBuilder->getUrl('advertisement/block/cartpreview',$routeParams);
        return $url;
    }
}

<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Block\Adminhtml\Pricing\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;

class PositionPricingOrderDetail extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;
    
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Order
     */
    protected $_adOrderHelper;
    
    /**
     * @var \Webkul\MpAdvertisementManager\Model\PricingFactory
     */
    protected $_mpadvertismentPricing;
    
    /**
     * @var \Magento\Framework\App\ResourceConnection
     */
    protected $_resource;
    
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Order
     */
    protected $_adsOrderHelper;
    
    /**
     * Undocumented function
     *
     * @param \Magento\Backend\Block\Template\Context             $context
     * @param \Magento\Backend\Helper\Data                        $backendHelper
     * @param \Magento\Sales\Model\OrderFactory                   $productFactory
     * @param \Webkul\MpAdvertisementManager\Helper\Order         $adOrderHelper
     * @param \Magento\Framework\App\ResourceConnection           $resource
     * @param \Webkul\MpAdvertisementManager\Helper\Order         $adsOrderHelper
     * @param \Webkul\MpAdvertisementManager\Model\PricingFactory $mpadvertismentPricing
     * @param \Magento\Framework\Registry                         $coreRegistry
     * @param array                                               $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Sales\Model\OrderFactory $productFactory,
        \Webkul\MpAdvertisementManager\Helper\Order $adOrderHelper,
        \Magento\Framework\App\ResourceConnection $resource,
        \Webkul\MpAdvertisementManager\Helper\Order $adsOrderHelper,
        \Webkul\MpAdvertisementManager\Model\PricingFactory $mpadvertismentPricing,
        \Magento\Framework\Registry $coreRegistry,
        array $data = []
    ) {
        $this->_productFactory = $productFactory;
        $this->_adOrderHelper = $adOrderHelper;
        $this->_mpadvertismentPricing = $mpadvertismentPricing;
        $this->_coreRegistry = $coreRegistry;
        $this->_resource = $resource;
        $this->_adsOrderHelper = $adsOrderHelper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('ads_pricing');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }
    public function getAllSellersIds()
    {
        return $this->_adOrderHelper->getAllSellersIds();
    }

    /**
     * @param Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($column->getId() == 'id') {
            /*
            $productIds = $this->_getSelectedProducts();
            if (empty($productIds)) {
                $productIds = 0;
            }
            if ($column->getFilter()->getValue()) {
                $this->getCollection()->addFieldToFilter('entity_id', ['in' => $productIds]);
            } elseif (!empty($productIds)) {
                $this->getCollection()->addFieldToFilter('entity_id', ['nin' => $productIds]);
            }
            */
        } else {
            parent::_addColumnFilterToCollection($column);
        }
        return $this;
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $orderId = [];
        $id = $this->getRequest()->getParam('id');
        
        if (isset($id) && $id!="") {
            $mpadvertismentPricing = $this->_mpadvertismentPricing->create()->load($id);
            $position = $mpadvertismentPricing->getBlockPosition();
            $orders = $this->_adsOrderHelper->getAdsOrderIds($position);
            foreach ($orders as $order) {
                $orderId[] =$order;
            }
            if (empty($orderId)) {
                $orderId[] = null;
            }
            $collection = $this->_productFactory->create()->getCollection()->addAttributeToSelect('*')->join(
                ['tcr' => $this->_resource->getTableName('sales_order_grid')],
                "main_table.entity_id = tcr.entity_id",
                ['tcr_grand_total'=>'tcr.grand_total',
                'tcr.customer_name']
            )->join(
                ['mpadsdetail' => $this->_resource->getTableName('marketplace_ads_purchase_details')],
                "main_table.entity_id = mpadsdetail.order_id"
            )->addAttributeToFilter(
                "entity_id",
                ['in'=>[$orderId]]
            )
            ->addFieldToFilter('main_table.status', ['in'=>['complete','processing','pending']]);
            if (!empty($this->getAllSellersIds())) {
                $collection->addAttributeToFilter(
                    "customer_id",
                    ['in'=>[$this->getAllSellersIds()]]
                );
            }
            $this->setCollection($collection);
            $this->getCollection()->addFieldToFilter('entity_id', ['in' => $orderId]);
            $this->getCollection()->addFieldToFilter('block_position', ['in' => $position]);
        }
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'type' => 'checkbox',
                'name' => 'id',
                'index' => 'entity_id',
                'header_css_class' => 'col-select col-massaction',
                'column_css_class' => 'col-select col-massaction'
            ]
        );
        $this->addColumn(
            'increment_id',
            [
                'header' => __('Order Id'),
                'sortable' => true,
                'index' => 'increment_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'customer_name',
            [
                'header' => __('Seller Name'),
                'sortable' => true,
                'index' => 'customer_name'
            ]
        );
        $this->addColumn(
            'price',
            [
                'header' => __('Price'),
                'sortable' => true,
                'index' => 'price',
                'filter_index'=>'mpadsdetail.price'
            ]
        );
        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'sortable' => true,
                'index' => 'status'
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpadvertisementmanager/*/grid', ['_current' => true]);
    }

    // /**
    //  * @return array
    //  */
    /*
    protected function _getSelectedProducts()
    {
        $products = $this->getRequest()->getPost('selected_products');
        if ($products === null) {
            $products = $this->getCategory()->getProductsPosition();
            return array_keys($products);
        }
        return $products;
    }
    */
}

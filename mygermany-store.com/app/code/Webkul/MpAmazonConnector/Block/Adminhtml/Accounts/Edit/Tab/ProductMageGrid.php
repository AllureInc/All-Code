<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Adminhtml\Accounts\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Widget\Grid\Column;
use Magento\Backend\Block\Widget\Grid\Extended;
use Webkul\MpAmazonConnector\Model\ProductMapFactory;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

class ProductMageGrid extends \Magento\Backend\Block\Widget\Grid\Extended
{

    private $sellerId;

    /**
     * @var ProductMapFactory
     */
    private $productMap;

    /**
     * @var \Magento\Catalog\Model\ResourceModel\Product\CollectionFactory
     */
    private $productCollectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    private $productVisibility;

    /**
     * @param \Magento\Backend\Block\Template\Context   $context
     * @param \Magento\Backend\Helper\Data              $backendHelper
     * @param CollectionFactory                         $productCollectionFactory
     * @param ProductmapFactory                         $productMap
     * @param \Magento\Catalog\Model\Product\Visibility $productVisibility
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        CollectionFactory $productCollectionFactory,
        ProductMapFactory $productMap,
        \Magento\Catalog\Model\Product\Visibility $productVisibility,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        array $data = []
    ) {
        $this->helper = $helper;
        $this->productMap = $productMap;
        $this->productCollectionFactory = $productCollectionFactory;
        $this->productVisibility = $productVisibility;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('mage_map_product');
        $this->setDefaultSort('id');
        $this->setUseAjax(true);
    }

    /**
     * @return Grid
     */
    protected function _prepareCollection()
    {
        $mappedProId = [];
        $entityId = $this->getRequest()->getParam('id');
        $this->sellerId = $this->helper->getCustomerId($entityId);
        $mappedProId = $this->helper->getSellerProductIds($this->sellerId);
        $mageProCollection = $this->productCollectionFactory
                            ->create()
                            ->addAttributeToSelect('*')
                            ->addFieldToFilter(
                                'type_id',
                                ['in' => ['simple']]
                            );
        if ($this->sellerId) {
            $mageProCollection->addFieldToFilter(
                'entity_id',
                ['in'=>$mappedProId]
            );
        } else {
            if (!empty($mappedProId)) {
                $mageProCollection->addFieldToFilter(
                    'entity_id',
                    ['nin'=>$mappedProId]
                );
            }
        }
        $mageProCollection->setVisibility($this->productVisibility->getVisibleInSiteIds());
        $this->setCollection($mageProCollection);
        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('Id'),
                'sortable' => true,
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'name',
            [
                'header' => __('Name'),
                'sortable' => true,
                'index' => 'name'
            ]
        );
        $this->addColumn(
            'identification_value',
            [
                'header' => __('Product Identifier'),
                'sortable' => true,
                'index' => 'identification_value'
            ]
        );
        $this->addColumn(
            'type_id',
            [
                'header' => __('Type'),
                'sortable' => true,
                'index' => 'type_id'
            ]
        );
        $this->addColumn(
            'sku',
            [
                'header' => __('Sku'),
                'sortable' => false,
                'index' => 'sku'
            ]
        );
        return parent::_prepareColumns();
    }

    /**
     * get massaction
     * @return object
     */
    protected function _prepareMassaction()
    {
        $accountId = $this->getRequest()->getParam('id');
        $this->setMassactionIdField('entity_id');
        $this->setChild('massaction', $this->getLayout()->createBlock($this->getMassactionBlockName()));
        $this->getMassactionBlock()->setFormFieldName('mageProEntityIds');
        $this->getMassactionBlock()->addItem(
            'import_to_amazon',
            [
                'label' => __('Export To Amazon'),
                'url' => $this->getUrl(
                    '*/exportproduct/synctoamazon',
                    [
                        'id'=>$this->getRequest()->getParam('id')
                    ]
                )
            ]
        );
        return $this;
    }
}

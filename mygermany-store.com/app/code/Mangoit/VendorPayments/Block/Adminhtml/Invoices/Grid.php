<?php
namespace Mangoit\VendorPayments\Block\Adminhtml\Invoices;


class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory]
     */
    protected $_setsFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $_productFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Type
     */
    protected $_type;

    /**
     * @var \Magento\Catalog\Model\Product\Attribute\Source\Status
     */
    protected $_status;
    protected $_collectionFactory;

    /**
     * @var \Magento\Catalog\Model\Product\Visibility
     */
    protected $_visibility;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;
    protected $helper;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Set\CollectionFactory $setsFactory
     * @param \Magento\Catalog\Model\ProductFactory $productFactory
     * @param \Magento\Catalog\Model\Product\Type $type
     * @param \Magento\Catalog\Model\Product\Attribute\Source\Status $status
     * @param \Magento\Catalog\Model\Product\Visibility $visibility
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Mangoit\VendorPayments\Model\ResourceModel\Vendorinvoices\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        \Mangoit\VendorPayments\Helper\Data $helper,
        array $data = []
        ) {

      $this->_collectionFactory = $collectionFactory;
      $this->_websiteFactory = $websiteFactory;
      $this->moduleManager = $moduleManager;
      $this->helper = $helper;
      parent::__construct($context, $backendHelper, $data);
  }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        $this->setId('vendorInvoicesGrid');
        $this->setDefaultSort('id');
        $this->setDefaultDir('DESC');
        $this->setSaveParametersInSession(true);
        $this->setUseAjax(false);

    }

    /**
     * @return Store
     */
    protected function _getStore()
    {
        $storeId = (int)$this->getRequest()->getParam('store', 0);
        return $this->_storeManager->getStore($storeId);
    }

    /**
     * @return $this
     */
    protected function _prepareCollection()
    {
      try{
         $collection =$this->_collectionFactory->load();
         // print_r($collection->getData());die;
         $this->setCollection($collection);
         parent::_prepareCollection();
         return $this;
     }
     catch(Exception $e)
     {
        echo $e->getMessage();
     }
 }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        if ($this->getCollection()) {
            if ($column->getId() == 'websites') {
                $this->getCollection()->joinField(
                    'websites',
                    'catalog_product_website',
                    'website_id',
                    'product_id=entity_id',
                    null,
                    'left'
                    );
            }
        }
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * @SuppressWarnings(PHPMD.ExcessiveMethodLength)
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'entity_id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );

        $this->addColumn(
            'seller_id',
            [
                'header' => __('Seller ID'),
                'type' => 'number',
                'index' => 'seller_id',
                'class' => 'name'
            ]
        );

        $this->addColumn(
            'invoice_number',
            [
                'header' => __('Invoice Number'),
                'type' => 'text',
                'index' => 'invoice_number',
                'class' => 'invoice_number'
            ]
        );

        $this->addColumn(
            'seller_name',
            [
                'header' => __('Seller Name'),
                'index' => 'seller_name',
                'class' => 'name',
                'filter' => false,
                'sortable' => false,
                'renderer'  => 'Mangoit\VendorPayments\Block\Adminhtml\Invoices\Renderer\RenderRow'
            ]
        );

        $this->addColumn(
            'shop_name',
            [
                'header' => __('Shop Name'),
                'index' => 'shop_name',
                'class' => 'name',
                'filter' => false,
                'sortable' => false,
                'renderer'  => 'Mangoit\VendorPayments\Block\Adminhtml\Invoices\Renderer\RenderRow'
            ]
        );

        $this->addColumn(
            'invoice_status',
            [
                'header' => __('Status'),
                'index' => 'invoice_status',
                'width' => '50px',
                'type' => 'options',
                'options' => array_merge(
                        ['' => 'Select Status'],
                        $this->helper->getVendorInvoiceStatuses()
                    ) //dropdown result
            ]
        );

        $this->addColumn(
            'invoice_typ',
            [
                'header' => __('Invoice Type'),
                'index' => 'invoice_typ',
                'width' => '50px',
                'type' => 'options',
                'options' => array_merge(
                        ['' => 'Select Type'],
                        $this->helper->getVendorInvoiceTypes()
                    ) //dropdown result
            ]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'index' => 'created_at',
                'class' => 'created_at'
            ]
        );

        $this->addColumn(
            'action', [
                'header' => __('Action'),
                'type' => 'action',
                'getter' => 'getId',
                'actions' => [
                    [
                        'caption' => __('View'),
                        'url' => ['base' => '*/*/view'],
                        'field' => 'id',
                    ],
                    [
                        'caption' => __('Download Invoice'),
                        'url' => ['base' => '*/*/download'],
                        'field' => 'id',
                    ],
                ],
                'filter' => false,
                'sortable' => false,
                'index' => 'stores',
                'header_css_class' => 'col-action',
                'column_css_class' => 'col-action',
            ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('vendorpayments/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'vendorpayments/*/view',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
            );
    }
}

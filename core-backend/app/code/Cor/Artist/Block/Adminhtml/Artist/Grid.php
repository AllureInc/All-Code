<?php
namespace Cor\Artist\Block\Adminhtml\Artist;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @var \Cor\Artist\Model\ResourceModel\Artist\Collection
     */
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Store\Model\WebsiteFactory $websiteFactory
     * @param \Cor\Artist\Model\ResourceModel\Artist\Collection $collectionFactory
     * @param \Magento\Framework\Module\Manager $moduleManager
     * @param array $data
     *
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Cor\Artist\Model\ResourceModel\Artist\Collection $collectionFactory,
        \Magento\Framework\Module\Manager $moduleManager,
        array $data = []
    ) {        
        $this->_collectionFactory = $collectionFactory;
        $this->_websiteFactory = $websiteFactory;
        $this->moduleManager = $moduleManager;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        
        $this->setId('artistGrid');
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
            $this->setCollection($collection);
            parent::_prepareCollection();          
            return $this;
        }
        catch(Exception $e)
        {
            echo $e->getMessage();die;
        }
    }

    /**
     * @param \Magento\Backend\Block\Widget\Grid\Column $column
     * @return $this
     */
    protected function _addColumnFilterToCollection($column)
    {
        return parent::_addColumnFilterToCollection($column);
    }

    /**
     * @return $this
     * 
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'id',
            [
                'header' => __('ID'),
                'type' => 'number',
                'index' => 'id',
                'header_css_class' => 'col-id',
                'column_css_class' => 'col-id'
            ]
        );
        $this->addColumn(
            'artist_name',
            [
                'header' => __('Artist Name'),
                'index' => 'artist_name',
                'class' => 'artist_name'
            ]
        );
        $this->addColumn(
            'artist_rep_name',
            [
                'header' => __('Artist Rep Name'),
                'index' => 'artist_rep_name',
                'class' => 'artist_rep_name'
            ]
        );
        $this->addColumn(
            'artist_rep_number',
            [
                'header' => __('Artist Rep Number'),
                'index' => 'artist_rep_number',
                'class' => 'artist_rep_number'
            ]
        );
        $this->addColumn(
            'artist_rep_email',
            [
                'header' => __('Artist Rep Email'),
                'index' => 'artist_rep_email',
                'class' => 'artist_rep_email'
            ]
        );
        $this->addColumn(
            'artist_tax_id',
            [
                'header' => __('Tax Id'),
                'index' => 'artist_tax_id',
                'class' => 'artist_tax_id'
            ]
        );
        $this->addColumn(
            'wnine_received',
            [
                'header' => __('W9 Received'),
                'index' => 'wnine_received',
                'class' => 'wnine_received',
                'type' => 'options',
                'options' => ['1'=> __('Yes'), '0'=> __('No')],
                'renderer'  => 'Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer\Wnineoptions',
            ]
        );
        $this->addColumn(
            'gross_total_sale',
            [
                'header' => __('Gross Total Sale'),
                'class' => 'gross_total_sale',
                'renderer'  => 'Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer\GrossTotalSale',
            ]
        );
        $this->addColumn(
            'most_recent_event',
            [
                'header' => __('Most Recent Event Date'),
                'class' => 'most_recent_event',
                'renderer'  => 'Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer\Recentevent',
            ]
        );
        $this->addColumn(
            'most_recent_settlement_date',
            [
                'header' => __('Most Recent Settlement Date'),
                'index' => 'most_recent_settlement_date',
                'class' => 'most_recent_settlement_date',
                'renderer'  => 'Cor\Artist\Block\Adminhtml\Artist\Edit\Tab\Renderer\Recentsettlement',
            ]
        );
        $this->addColumn(
           'edit',
           [
               'header' => __('Action'),
               'type' => 'action',
               'getter' => 'getId',
               'actions' => [
                   [
                       'caption' => __('Edit'),
                       'url' => [
                           'base' => '*/*/edit',
                           'params' => ['store' => $this->getRequest()->getParam('store')]
                       ],
                       'field' => 'id'
                   ]
               ],
               'filter' => false,
               'sortable' => false,
               'index' => 'stores',
               'header_css_class' => 'col-action',
               'column_css_class' => 'col-action'
           ]
        );

        $block = $this->getLayout()->getBlock('grid.bottom.links');
        if ($block) {
            $this->setChild('grid.bottom.links', $block);
        }

        return parent::_prepareColumns();
    }

     /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('id');
        $this->getMassactionBlock()->setFormFieldName('id');

        $this->getMassactionBlock()->addItem(
            'delete',
            array(
                'label' => __('Delete'),
                'url' => $this->getUrl('artist/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
        return $this;
    }

    /**
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('artist/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'artist/*/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }
}

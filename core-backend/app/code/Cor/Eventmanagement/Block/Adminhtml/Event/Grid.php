<?php
namespace Cor\Eventmanagement\Block\Adminhtml\Event;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;
    protected $_collectionFactory;

    /**
     * @var \Magento\Store\Model\WebsiteFactory
     */
    protected $_websiteFactory;

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
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Magento\Store\Model\WebsiteFactory $websiteFactory,
        \Cor\Eventmanagement\Model\ResourceModel\Event\Collection $collectionFactory,
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
        $this->setId('productGrid');
        $this->setDefaultSort('id');
        // $this->setDefaultDir('DESC');
        $this->setDefaultDir('ASC');
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
     * @return $this (Grid of events)
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
            'event_start_date',
            [
                'header' => __('Start Date'),
                'index' => 'event_start_date',
                'type' => 'date',
            ]
        );
        $this->addColumn(
            'event_end_date',
            [
                'header' => __('End Date'),
                'index' => 'event_end_date',
                'type' => 'date',
            ]
        );
        $this->addColumn(
            'event_name',
            [
                'header' => __('Name'),
                'index' => 'event_name',
                'class' => 'event_name'
            ]
        );
        $this->addColumn(
            'event_street',
            [
                'header' => __('Street'),
                'index' => 'event_street',
                'class' => 'event_street'
            ]
        );
        $this->addColumn(
            'event_city',
            [
                'header' => __('City'),
                'index' => 'event_city',
                'class' => 'event_city'
            ]
        );
        $this->addColumn(
            'event_state',
            [
                'header' => __('State'),
                'index' => 'event_state',
                'class' => 'event_state'
            ]
        );
        $this->addColumn(
            'event_zip',
            [
                'header' => __('Zip Code'),
                'index' => 'event_zip',
                'class' => 'event_zip'
            ]
        );
        $this->addColumn(
            'event_country',
            [
                'header' => __('Country'),
                'index' => 'event_country',
                'class' => 'event_country'
            ]
        );
        $this->addColumn(
            'event_capacity',
            [
                'header' => __('Capacity'),
                'index' => 'event_capacity',
                'class' => 'event_capacity'
            ]
        );
        $this->addColumn(
            'event_phone',
            [
                'header' => __('Phone'),
                'index' => 'event_phone',
                'class' => 'event_phone'
            ]
        );
        $this->addColumn(
            'event_status',
            [
                'header' => __('Status'),
                'index' => 'event_status',
                'class' => 'event_status',
                'type' => 'options',
                'options' => ['1'=> __('Closed'), '0'=> __('Open')],
                'renderer'  => 'Cor\Eventmanagement\Block\Adminhtml\Event\Edit\Tab\Renderer\Statusoption',
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
                'url' => $this->getUrl('eventmanagement/*/massDelete'),
                'confirm' => __('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'close',
            array(
                'label' => __('Close'),
                'url' => $this->getUrl('eventmanagement/*/massclose'),
                'confirm' => __('Are you sure?')
            )
        );
        $this->getMassactionBlock()->addItem(
            'open',
            array(
                'label' => __('Open'),
                'url' => $this->getUrl('eventmanagement/*/massopen'),
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
        return $this->getUrl('eventmanagement/*/index', ['_current' => true]);
    }

    /**
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\Object $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return $this->getUrl(
            'eventmanagement/*/edit',
            ['store' => $this->getRequest()->getParam('store'), 'id' => $row->getId()]
        );
    }
}

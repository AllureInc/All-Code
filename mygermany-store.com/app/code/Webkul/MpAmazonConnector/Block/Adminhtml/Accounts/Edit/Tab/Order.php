<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Adminhtml\Accounts\Edit\Tab;

/**
 * Adminhtml amazon orders grid block
 */
class Order extends \Magento\Backend\Block\Widget\Grid\Extended
{
    private $sellerId;

    /**
     * @var  \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory
     * @param \Magento\Framework\Registry $coreRegistry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Magento\Framework\View\Element\UiComponent\DataProvider\CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->helper = $helper;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('amazon_order_map_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * Apply various selection filters to prepare the sales order grid collection.
     *
     * @return $this
     */
    protected function _prepareCollection()
    {
        $entityId = $this->getRequest()->getParam('id');
        $this->sellerId = $this->helper->getCustomerId($entityId);
        $collection = $this->collectionFactory->getReport('mpamazonconnect_order_map_list_data_source')
                                ->addFieldToSelect('entity_id')
                                ->addFieldToSelect('created_at')
                                ->addFieldToSelect('mage_order_id')
                                ->addFieldToSelect('amazon_order_id')
                                ->addFieldToSelect('purchase_date')
                                ->addFieldToSelect('status')
                                ->addFieldToFilter('seller_id', $this->sellerId);

        $this->setCollection($collection);
        return parent::_prepareCollection();
    }

    /**
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn('mage_order_id', ['header' => __('Magento Order Id'), 'width' => '100', 'index' => 'mage_order_id']);

        $this->addColumn('amazon_order_id', ['header' => __('Amazon Order Id'), 'index' => 'amazon_order_id']);
        
        $this->addColumn('status', ['header' => __('Order Status'), 'index' => 'status']);
        
        $this->addColumn('created_at', ['header' => __('Sync Date'), 'index' => 'created_at', 'type' => 'datetime']);

        $this->addColumn('purchase_date', ['header' => __('Purchase Date'), 'index' => 'purchase_date', 'type' => 'datetime']);

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
        $this->getMassactionBlock()->setFormFieldName('orderEntityIds');
        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Delete'),
                'url' => $this->getUrl(
                    '*/order/massdelete',
                    [
                        'id'=>$this->getRequest()->getParam('id')
                    ]
                ),
                'confirm' => __('Are you sure want to delete?'),
            ]
        );
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('mpamazonconnect/order/resetgrid', ['_current' => true]);
    }
}

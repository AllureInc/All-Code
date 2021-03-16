<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Edit\Tab\Export\Order;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Core\Model\Profile;
use Plenty\Order\Model\ResourceModel\Export\Order\Collection;
use Plenty\Order\Model\ResourceModel\Export\Order\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;

/**
 * Class Grid
 * @package Plenty\Order\Block\Adminhtml\Profile\Edit\Tab\Export\Order
 */
class Grid extends Extended
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Grid constructor.
     * @param Context $context
     * @param Data $backendHelper
     * @param Registry $coreRegistry
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        Data $backendHelper,
        Registry $coreRegistry,
        CollectionFactory $collectionFactory,
        array $data = []
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Grid constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('order_grid');
        $this->setDefaultSort('entity_id');
        $this->setDefaultDir('DESC');
        $this->setUseAjax(true);
    }

    /**
     * @return Profile
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_PROFILE);
    }

    /**
     * @return Extended
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addProfileFilter($this->_getModel()->getId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * @return Extended
     * @throws \Exception
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'entity_id',
            ['header' => __('ID'), 'align' => 'left', 'index' => 'entity_id', 'width' => 10]
        );

        $this->addColumn(
            'profile_id',
            ['header' => __('Profile ID'), 'align' => 'left', 'index' => 'profile_id', 'width' => 10]
        );

        $this->addColumn(
            'order_id',
            ['header' => __('Order ID'), 'align' => 'left', 'index' => 'order_id', 'width' => 20]
        );

        $this->addColumn(
            'order_increment_id',
            ['header' => __('Order Real ID'), 'align' => 'left', 'index' => 'order_increment_id', 'width' => 20]
        );

        $this->addColumn(
            'customer_id',
            ['header' => __('Customer ID'), 'align' => 'left', 'index' => 'customer_id', 'width' => 20]
        );

        $this->addColumn(
            'status',
            ['header' => __('Status'), 'align' => 'left', 'index' => 'status', 'width' => 30]
        );

        $this->addColumn(
            'plenty_order_id',
            ['header' => __('Plenty Order ID'), 'align' => 'left', 'index' => 'plenty_order_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_status_id',
            ['header' => __('Plenty Status ID'), 'align' => 'left', 'index' => 'plenty_status_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_status_name',
            ['header' => __('Plenty Status Name'), 'align' => 'left', 'index' => 'plenty_status_name', 'width' => 50]
        );

        $this->addColumn(
            'plenty_status_lock',
            ['header' => __('Plenty Status Lock'), 'align' => 'left', 'index' => 'plenty_status_lock', 'width' => 20]
        );

        $this->addColumn(
            'plenty_referrer_id',
            ['header' => __('Plenty Referrer ID'), 'align' => 'left', 'index' => 'plenty_referrer_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_contact_id',
            ['header' => __('Plenty Contact ID'), 'align' => 'left', 'index' => 'plenty_contact_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_billing_address_id',
            ['header' => __('Plenty Billing Address ID'), 'align' => 'left', 'index' => 'plenty_billing_address_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_shipping_address_id',
            ['header' => __('Plenty Shipping Address ID'), 'align' => 'left', 'index' => 'plenty_shipping_address_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_payment_id',
            ['header' => __('Plenty Payment ID'), 'align' => 'left', 'index' => 'plenty_payment_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_payment_method_id',
            ['header' => __('Plenty Payment Method ID'), 'align' => 'left', 'index' => 'plenty_payment_method_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_payment_status_id',
            ['header' => __('Plenty Payment Status ID'), 'align' => 'left', 'index' => 'plenty_payment_status_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_payment_assignment_id',
            ['header' => __('Plenty Payment Assignment ID'), 'align' => 'left', 'index' => 'plenty_payment_assignment_id', 'width' => 20]
        );

        $this->addColumn(
            'plenty_location_id',
            ['header' => __('Plenty Location ID'), 'align' => 'left', 'index' => 'plenty_location_id', 'width' => 20]
        );

        $this->addColumn(
            'message',
            ['header' => __('Message'), 'align' => 'left', 'index' => 'message', 'width' => 150]
        );

        $this->addColumn(
            'created_at',
            [
                'header' => __('Created At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'created_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'updated_at',
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'updated_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'processed_at',
            [
                'header' => __('Processed At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'processed_at',
                'default' => ' ---- '
            ]
        );

        return parent::_prepareColumns();
    }

    /**
     * Retrieve the Url for a specified sales order row.
     *
     * @param \Magento\Sales\Model\Order|\Magento\Framework\DataObject $row
     * @return string
     */
    public function getRowUrl($row)
    {
        return false;
    }

    /**
     * {@inheritdoc}
     */
    public function getGridUrl()
    {
        return $this->getUrl('plenty_order/export_order/grid', ['_current' => true]);
    }
}

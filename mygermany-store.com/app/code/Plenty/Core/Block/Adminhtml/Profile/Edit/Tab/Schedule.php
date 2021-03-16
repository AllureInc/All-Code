<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Backend\Block\Widget\Grid;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Core\Model\Profile;
use Plenty\Core\Model\ResourceModel\Profile\Schedule\Collection;
use Plenty\Core\Model\ResourceModel\Profile\Schedule\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Filter\Status as FilterStatus;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Renderer\Status as RendererStatus;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Schedule\Grid\Renderer\Action as RendererAction;

/**
 * Class Schedule
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab
 */
class Schedule extends Grid\Extended
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
     * Schedule constructor.
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
     * Grid constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('profile_schedule_grid');
        $this->setDefaultSort('created_at');
        $this->setDefaultDir('ASC');
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
     * @return Grid\Extended
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
     * @return Grid\Extended
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
            'status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => FilterStatus::class,
                'index' => 'queue_status',
                'renderer' => RendererStatus::class
            ]
        );

        $this->addColumn(
            'job_code',
            ['header' => __('Job Code'), 'align' => 'center', 'index' => 'job_code', 'width' => 50]
        );

        $this->addColumn(
            'message',
            ['header' => __('Message'), 'align' => 'left', 'index' => 'message', 'width' => 100]
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
            'scheduled_at',
            [
                'header' => __('Scheduled At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'scheduled_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'executed_at',
            [
                'header' => __('Executed At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'executed_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'finished_at',
            [
                'header' => __('Finished At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'finished_at',
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            'action',
            [
                'header' => __('Action'),
                'align' => 'center',
                'filter' => false,
                'sortable' => false,
                RendererAction::class
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
        return $this->getUrl('plenty_core/profile/schedule', ['_current' => true]);
    }

}

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

use Plenty\Core\Model\ResourceModel\Profile\History\Collection;
use Plenty\Core\Model\ResourceModel\Profile\History\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\History\Grid\Filter\Status as FilterStatus;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\History\Grid\Renderer\Status as RendererStatus;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\History\Grid\Renderer\Action as RendererAction;

/**
 * Class History
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab
 */
class History extends Grid\Extended
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
     * History constructor.
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
        $this->setId('profile_history_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * @return \Plenty\Core\Model\Profile
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('plenty_profile');
    }

    /**
     * @return Grid\Extended
     */
    protected function _prepareCollection()
    {
        /** @var Collection $collection */
        $collection = $this->_collectionFactory->create();
        $collection->addProfileFilter(
            $this->_coreRegistry->registry(RegistryConstants::CURRENT_PROFILE)->getId()
        );

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
            'action_code',
            ['header' => __('Action Code'), 'align' => 'center', 'index' => 'action_code', 'width' => 40]
        );

        $this->addColumn(
            'status',
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => FilterStatus::class,
                'index' => 'status',
                'renderer' => RendererStatus::class
            ]
        );

        $this->addColumn(
            'message',
            ['header' => __('Message'), 'align' => 'center', 'index' => 'message', 'width' => 150]
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
            'processed_at',
            [
                'header' => __('Processed At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => 'processed_at',
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
     * @return string
     */
    public function getGridUrl()
    {
        return $this->getUrl('plenty_core/profile/history', ['_current' => true]);
    }

}

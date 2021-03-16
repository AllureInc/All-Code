<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product;

use Magento\Backend\Block\Widget\Grid\Extended;
use Magento\Backend\Block\Template\Context;
use Magento\Backend\Helper\Data;
use Magento\Framework\Registry;

use Plenty\Item\Model\ResourceModel\Export\Product\Collection;
use Plenty\Item\Model\ResourceModel\Export\Product\CollectionFactory;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;
use Plenty\Item\Api\Data\Export\ProductInterface;
use Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product\Grid\Filter\Status as FilterStatus;
use Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product\Grid\Renderer\Status as RendererStatus;
use Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product\Grid\Renderer\Action as RendererAction;

/**
 * Class Grid
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit\Tab\Export\Product
 */
class Grid extends Extended
{
    /**
     * @var string
     */
    protected $_massactionBlockName = \Plenty\Core\Block\Adminhtml\Widget\Grid\Massaction\Extended::class;

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
     * {@inheritdoc}
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('product_export_grid');
        $this->setDefaultSort('created_at', 'desc');
        $this->setUseAjax(true);
    }

    /**
     * @return string
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _toHtml()
    {
        $html = $this->getLayout()
            ->createBlock(BeforeGridHtml::class)
            ->setTemplate('Plenty_Item::profile/tab/export/product/before-grid-html.phtml')
            ->setData('product_grid_id', $this->getId())
            ->toHtml();

        $html .= parent::_toHtml();
        return $html;
    }

    /**
     * @return \Plenty\Core\Model\Profile
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
     * {@inheritdoc}
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            ProductInterface::ENTITY_ID,
            ['header' => __('ID'), 'align' => 'left', 'index' => ProductInterface::ENTITY_ID, 'width' => 10]
        )->addColumn(
            ProductInterface::PROFILE_ID,
            ['header' => __('Profile ID'), 'align' => 'left', 'index' => ProductInterface::PROFILE_ID, 'width' => 10]
        )->addColumn(
            ProductInterface::PRODUCT_ID,
            ['header' => __('Product ID'), 'align' => 'left', 'index' => ProductInterface::PRODUCT_ID, 'width' => 10]
        )->addColumn(
            ProductInterface::SKU,
            ['header' => __('SKU'), 'align' => 'left', 'index' => ProductInterface::SKU, 'width' => 10]
        )->addColumn(
            ProductInterface::STATUS,
            [
                'header' => __('Status'),
                'align' => 'center',
                'filter' => FilterStatus::class,
                'index' => ProductInterface::STATUS,
                'renderer' => RendererStatus::class
            ]
        )->addColumn(
            ProductInterface::NAME,
            ['header' => __('Name'), 'align' => 'left', 'index' => ProductInterface::NAME, 'width' => 100]
        )->addColumn(
            ProductInterface::ITEM_ID,
            ['header' => __('Item ID'), 'align' => 'left', 'index' => ProductInterface::ITEM_ID, 'width' => 100]
        )->addColumn(
            ProductInterface::VARIATION_ID,
            ['header' => __('Variation ID'), 'align' => 'left', 'index' => ProductInterface::VARIATION_ID, 'width' => 10]
        )->addColumn(
            ProductInterface::MAIN_VARIATION_ID,
            ['header' => __('Main Variation ID'), 'align' => 'left', 'index' => ProductInterface::MAIN_VARIATION_ID, 'width' => 10]
        )->addColumn(
            ProductInterface::TYPE,
            ['header' => __('Type'), 'align' => 'left', 'index' => ProductInterface::TYPE, 'width' => 50]
        );

        $this->addColumn(
            ProductInterface::PRODUCT_TYPE,
            ['header' => __('Product Type'), 'align' => 'left', 'index' => ProductInterface::PRODUCT_TYPE, 'width' => 50]
        );

        $this->addColumn(
            ProductInterface::ATTRIBUTE_SET,
            ['header' => __('Attribute Set'), 'align' => 'left', 'index' => ProductInterface::ATTRIBUTE_SET, 'width' => 50]
        );

        $this->addColumn(
            ProductInterface::VISIBILITY,
            ['header' => __('Visibility'), 'align' => 'left', 'index' => ProductInterface::VISIBILITY, 'width' => 50]
        );

        $this->addColumn(
            ProductInterface::MESSAGE,
            ['header' => __('Message'), 'align' => 'left', 'index' => ProductInterface::MESSAGE, 'width' => 100]
        );

        $this->addColumn(
            ProductInterface::CREATED_AT,
            [
                'header' => __('Created At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => ProductInterface::CREATED_AT,
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            ProductInterface::UPDATED_AT,
            [
                'header' => __('Updated At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => ProductInterface::UPDATED_AT,
                'default' => ' ---- '
            ]
        );

        $this->addColumn(
            ProductInterface::PROCESSED_AT,
            [
                'header' => __('Processed At'),
                'type' => 'datetime',
                'align' => 'center',
                'index' => ProductInterface::PROCESSED_AT,
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
     * @return string
     */
    protected function getAdditionalJavascript() {
        return "window.{$this->getId()}_massactionJsObject = {$this->getId()}_massactionJsObject";
    }

    /**
     * @return $this
     */
    protected function _prepareMassaction()
    {
        $this->setMassactionIdField('entity_id');
        $this->getMassactionBlock()->setTemplate('Plenty_Core::widget/grid/massaction_extended.phtml');
        $this->getMassactionBlock()->setFormFieldName('product_ids');
        // $this->getMassactionBlock()->setUseAjax(true);
        // $this->getMassactionBlock()->setHideFormElement(true);
        $this->getMassactionBlock()->setUseSelectAll(true);

        $this->getMassactionBlock()->addItem(
            'export_products',
            [
                'label' => __('Export Products'),
                'url' => $this->getUrl('plenty_item/*/massExport', ['_current' => true]),
                'confirm' => __('Are you sure you want to export the selected product(s)?'),
                'complete' => 'refreshCouponCodesGrid'
            ]
        );

        $this->getMassactionBlock()->addItem(
            'stop_export_products',
            [
                'label' => __('Stop Export'),
                'url' => $this->getUrl('plenty_item/*/massStopExport', ['_current' => true]),
                'confirm' => __('Are you sure you want to stop export of the selected product(s)?'),
                'complete' => 'refreshCouponCodesGrid'
            ]
        );

        $this->getMassactionBlock()->addItem(
            'delete',
            [
                'label' => __('Remove From Export List'),
                'url' => $this->getUrl('plenty_item/*/massDelete', ['_current' => true]),
                'confirm' => __('Are you sure you want to remove the selected product(s) from export list?'),
                'complete' => 'refreshCouponCodesGrid'
            ]
        );

        return $this;
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
        return $this->getUrl('plenty_item/export_product/grid', ['_current' => true]);
    }
}

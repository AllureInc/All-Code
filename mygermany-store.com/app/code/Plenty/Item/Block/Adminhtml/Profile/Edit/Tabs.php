<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Edit;

use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;
/**
 * Class Tabs
 * @package Plenty\Item\Block\Adminhtml\Profile\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Tabs constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Json\EncoderInterface $jsonEncoder
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Json\EncoderInterface $jsonEncoder,
        \Magento\Backend\Model\Auth\Session $authSession,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }

    /**
     * Constructor
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Profile Information'));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'profile_main',
            [
                'label' => __('Profile Setting'),
                'title' => __('Profile Setting'),
                'content' => $this->getLayout()
                    ->createBlock(\Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Main::class)
                    ->toHtml()
            ]
        );

        $this->addTab(
            'profile_config',
            [
                'label' => __('%1 Configuration', $this->_getModel()->getAdaptor()
                    ? ucfirst($this->_getModel()->getAdaptor())
                    : 'General'),
                'title' => __('%1 Configuration', $this->_getModel()->getAdaptor()
                    ? ucfirst($this->_getModel()->getAdaptor())
                    : 'General'),
                'content' => $this->getLayout()->createBlock(Form::class)->toHtml()
            ]
        );

        if ($this->_getModel()->getAdaptor() === 'export') {
            $this->addTab(
                'profile_export_list',
                [
                    'label' => __('Product Export List'),
                    'title' => __('Product Export List'),
                    'class' => 'ajax',
                    'content' => $this->getLayout()
                        ->createBlock(Tab\Export\Product\Grid::class)
                        ->toHtml()
                    // 'url' => $this->getUrl('plenty_item/export_product/grid', ['_current' => true]),

                ]
            );
        }

        $this->addTab(
            'profile_schedule',
            [
                'label' => __('Schedules'),
                'url' => $this->getUrl('plenty_core/profile/schedule', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'profile_schedule_history',
            [
                'label' => __('Schedule History'),
                'url' => $this->getUrl('plenty_core/profile/history', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'api_attribute_log',
            [
                'label' => __('Api Attribute Log'),
                'url' => $this->getUrl('plenty_item/import_attribute/grid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'api_category_log',
            [
                'label' => __('Api Category Log'),
                'url' => $this->getUrl('plenty_item/import_category/grid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'api_item_log',
            [
                'label' => __('Api Item Log'),
                'url' => $this->getUrl('plenty_item/import_item/grid', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        return parent::_beforeToHtml();
    }

    /**
     * @return \Plenty\Core\Model\Profile
     */
    private function _getModel()
    {
        return $this->_coreRegistry->registry(RegistryConstants::CURRENT_PROFILE);
    }
}

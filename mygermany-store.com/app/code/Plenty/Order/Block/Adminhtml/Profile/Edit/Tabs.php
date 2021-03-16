<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Edit;

use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;
use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Main;

/**
 * Class Tabs
 * @package Plenty\Order\Block\Adminhtml\Profile\Edit
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
     * Tabs constructor.
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('plenty_profile_order_tabs');
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
                    ->createBlock(Main::class)
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

        $this->addTab(
            'profile_schedule',
            [
                'label' => __('Schedules'),
                'url' => $this->getUrl('plenty_core/profile/schedule', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'profile_history',
            [
                'label' => __('Schedule History'),
                'url' => $this->getUrl('plenty_core/profile/history', ['_current' => true]),
                'class' => 'ajax'
            ]
        );

        $this->addTab(
            'api_order_log',
            [
                'label' => __('Order Synchronisation'),
                'url' => $this->getUrl('plenty_order/export_order/grid', ['_current' => true]),
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

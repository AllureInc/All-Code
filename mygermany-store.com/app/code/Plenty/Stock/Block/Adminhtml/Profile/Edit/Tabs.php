<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Stock\Block\Adminhtml\Profile\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;
use Magento\Framework\Registry;

use Plenty\Core\Block\Adminhtml\Profile\Edit\Tab\Main as MainTab;
use Plenty\Core\Controller\Adminhtml\Profile\RegistryConstants;

/**
 * Class Tabs
 * @package Plenty\Stock\Block\Adminhtml\Profile\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Core registry
     *
     * @var Registry
     */
    protected $_coreRegistry = null;

    /**
     * Tabs constructor.
     * @param Context $context
     * @param EncoderInterface $jsonEncoder
     * @param Session $authSession
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        Registry $registry,
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
        $this->setId('plenty_profile_stock_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Profile Information'));
    }

    /**
     * @return \Magento\Backend\Block\Widget\Tabs
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Exception
     */
    protected function _beforeToHtml()
    {
        $this->addTab(
            'profile_main',
            [
                'label' => __('Profile Setting'),
                'title' => __('Profile Setting'),
                'content' => $this->getLayout()
                    ->createBlock(MainTab::class)
                    ->toHtml()
            ]
        )->addTab(
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
        )->addTab(
            'profile_schedule',
            [
                'label' => __('Schedules'),
                'url' => $this->getUrl('plenty_core/profile/schedule', ['_current' => true]),
                'class' => 'ajax'
            ]
        )->addTab(
            'profile_history',
            [
                'label' => __('Schedule History'),
                'url' => $this->getUrl('plenty_core/profile/history', ['_current' => true]),
                'class' => 'ajax'
            ]
        )->addTab(
            'api_stock_log',
            [
                'label' => __('Inventory Synchronisation'),
                'url' => $this->getUrl('plenty_stock/import_inventory/grid', ['_current' => true]),
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

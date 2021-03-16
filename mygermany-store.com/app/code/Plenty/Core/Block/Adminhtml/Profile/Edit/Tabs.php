<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Edit;

/**
 * Class Tabs
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
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
                'label' => __('Profile Settings'),
                'title' => __('Profile Settings'),
                'content' => $this->getLayout()->createBlock(Tab\Main::class)->toHtml()
            ]
        );
        return parent::_beforeToHtml();
    }
}

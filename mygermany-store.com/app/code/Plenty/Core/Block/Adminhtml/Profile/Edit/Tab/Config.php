<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Edit\Tab;

use Magento\Ui\Component\Layout\Tabs\TabInterface;

/**
 * Class Config
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab
 */
class Config extends \Plenty\Core\Block\Adminhtml\Profile\Config\Form implements TabInterface
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    protected $_nameInLayout = 'profile_config';

    protected $_profileEntityName = null;

    /**
     * @return \Magento\Framework\Phrase
     */
    protected function _getProfileEntityName()
    {
        if (null === $this->_profileEntityName) {
            $this->_profileEntityName = ucwords(str_replace('_', ' ', $this->getRequest()->getParam('section')));
        }
        return __("{$this->_profileEntityName} Configuration");
    }

    /**
     * Return Tab label
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabLabel()
    {
        return $this->_getProfileEntityName();
    }

    /**
     * Return Tab title
     *
     * @return \Magento\Framework\Phrase
     */
    public function getTabTitle()
    {
        return $this->_getProfileEntityName();
    }

    /**
     * Tab class getter
     *
     * @return string
     */
    public function getTabClass()
    {
        return '';
    }

    /**
     * Return URL link to Tab content
     *
     * @return string
     */
    public function getTabUrl()
    {
        return null;
    }

    /**
     * Tab should be loaded trough Ajax call
     *
     * @return bool
     */
    public function isAjaxLoaded()
    {
        return false;
    }

    /**
     * Can show tab in tabs
     *
     * @return boolean
     */
    public function canShowTab()
    {
        return (bool) $this->getRequest()->getParam('id');
    }

    /**
     * Tab is hidden
     *
     * @return boolean
     */
    public function isHidden()
    {
        return false;
    }
}

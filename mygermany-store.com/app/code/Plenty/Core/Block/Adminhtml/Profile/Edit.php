<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\Registry;
use Magento\Backend\Block\Widget\Form\Container;

/**
 * Class Edit
 * @package Plenty\Core\Block\Adminhtml\Profile
 */
class Edit extends Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * Edit constructor.
     * @param Context $context
     * @param Registry $registry
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        array $data = [])
    {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * @return \Plenty\Core\Model\Profile
     */
    public function getModel()
    {
        return $this->_coreRegistry->registry('plenty_profile');
    }

    /**
     * Prepare layout object
     *
     * @return \Magento\Framework\View\Element\AbstractBlock
     */
    protected function _prepareLayout()
    {
        $this->_objectId = 'id';
        $this->_blockGroup = 'Plenty_Core';
        $this->_controller = 'Adminhtml_Profile';
        $this->setId($this->_objectId);

        $this->buttonList->add(
            'save_and_continue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                        'buttonAdapter' => [
                            'actions' => [
                                [
                                    'targetName' => 'plenty_core_profile_form',
                                    'actionName' => 'save',
                                    'params' => [
                                        true,
                                        [
                                            'id'        => $this->getRequest()->getParam('id'),
                                            'section'   => $this->getRequest()->getParam('section'),
                                            'website'   => $this->getRequest()->getParam('website'),
                                            'store'     => $this->getRequest()->getParam('store'),
                                            'back'      => 'edit'
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ],
            10
        );

        $objId = (int) $this->getRequest()->getParam($this->_objectId);
        if (!empty($objId)) {
            $this->addButton(
                'delete',
                [
                    'label' => __('Delete Profile'),
                    'class' => 'delete',
                    'data_attribute' => [
                        'role' => 'delete-user'
                    ]
                ]
            );
            $this->buttonList->update('save', 'label', __('Save Profile'));
        } else {
            $this->buttonList->remove('save');
            $this->buttonList->remove('delete');
        }

        return parent::_prepareLayout();
    }

    /**
     * @return bool
     */
    public function getEditMode()
    {
        return (bool) $this->getModel()->getId();
    }

    /**
     * Return header text for form
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        return $this->getEditMode()
            ?  __('Edit Profile')
            : __('New Profile');
    }

    /**
     * @return int
     */
    public function getObjectId()
    {
        return (int) $this->getRequest()->getParam($this->_objectId);
    }

    /**
     * Return delete url
     *
     * @return string
     */
    public function getDeleteUrl()
    {
        return $this->getUrl('*/*/delete', ['id' => $this->getRequest()->getParam('id')]);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getDeleteMessage()
    {
        return __('Are you sure you want to do this?');
    }

    /**
     * @return string
     */
    public function getFormActionUrl()
    {
        return $this->getUrl('*/*/save', ['_current' => true]);
    }

    /**
     * @return string
     */
    public function getBackUrl()
    {
        return $this->getUrl("plenty_core/profile");
    }

    /**
     * Retrieve Save As Flag
     *
     * @return int
     */
    public function getSaveAsFlag()
    {
        return $this->getRequest()->getParam('_save_as_flag') ? '1' : '';
    }
}

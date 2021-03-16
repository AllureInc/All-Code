<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Block\Adminhtml\Profile\Edit\Tab;

/**
 * Class Main
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit\Tab
 */
class Main extends \Magento\Backend\Block\Widget\Form\Generic
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Plenty\Core\Model\Profile\Source\EntityType
     */
    protected $_entityFactory;

    /**
     * @var \Plenty\Core\Model\Profile\Source\AdaptorType
     */
    protected $_adaptorFactory;

    /**
     * Main constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Plenty\Core\Model\Profile\Source\EntityType $entityType
     * @param \Plenty\Core\Model\Profile\Source\AdaptorType $adaptorType
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Plenty\Core\Model\Profile\Source\EntityType $entityType,
        \Plenty\Core\Model\Profile\Source\AdaptorType $adaptorType,
        array $data = []
    ) {
        $this->_entityFactory = $entityType;
        $this->_adaptorFactory = $adaptorType;
        parent::__construct($context, $registry, $formFactory, $data);
    }

    /**
     * @return \Plenty\Core\Model\Profile
     */
    protected function _getModel()
    {
        return $this->_coreRegistry->registry('plenty_profile');
    }

    /**
     * @return \Magento\Backend\Block\Widget\Form\Generic
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function _prepareForm()
    {
        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create();
        $form->setHtmlIdPrefix('profile_');

        $baseFieldset = $form->addFieldset('base_fieldset', ['legend' => __('Select Profile Options')]);

        $isNewObject = $this->_getModel()->isObjectNew();

        if (!$isNewObject) {
            $baseFieldset->addField('entity_id', 'hidden', ['name' => 'profile_id']);
        } else {
            if (!$this->_getModel()->hasData('is_active')) {
                $this->_getModel()->setData('is_active', true);
            }
        }

        if (!$isNewObject) {
            $baseFieldset->addField(
                'is_active',
                'select',
                [
                    'name' => 'is_active',
                    'label' => __('Profile Status'),
                    'id' => 'is_active',
                    'title' => __('Profile Status'),
                    'class' => 'input-select',
                    'options' => ['1' => __('Active'), '0' => __('Inactive')]
                ]
            );
        }
        $baseFieldset->addField(
            'name',
            'text',
            [
                'name' => 'name',
                'label' => __('Profile Name'),
                'id' => 'name',
                'title' => __('Profile Name'),
                'required' => true
            ]
        );
        $baseFieldset->addField(
            'entity',
            'select',
            [
                'label' => __('Entity'),
                'title' => __('Entity'),
                'name' => 'entity',
                'id' => 'entity',
                'required' => true,
                'value' => $this->_getModel()->getEntity(),
                'values' => $this->_entityFactory->toOptionArray(),
                'disabled' => !$isNewObject
            ]
        );
        $baseFieldset->addField(
            'adaptor',
            'select',
            [
                'label' => __('Direction'),
                'title' => __('Direction'),
                'name' => 'adaptor',
                'id' => 'adaptor',
                'required' => true,
                'value' => $this->_getModel()->getAdaptor(),
                'values' => $this->_adaptorFactory->toOptionArray(),
                'disabled' => !$isNewObject
            ]
        );
        if (!$isNewObject) {
            $baseFieldset->addField(
                'crontab',
                'text',
                [
                    'name' => 'crontab',
                    'label' => __('Cron Schedule'),
                    'id' => 'crontab',
                    'title' => __('Cron Schedule'),
                    'required' => false
                ]
            );
        }

        $data = $this->_getModel()->getData();
        $form->setUseContainer(false);
        $form->setValues($data);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}

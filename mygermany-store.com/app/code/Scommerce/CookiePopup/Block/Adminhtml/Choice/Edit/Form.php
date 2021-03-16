<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Block\Adminhtml\Choice\Edit;

/**
 * Class Form
 * @package Scommerce\CookiePopup\Block\Adminhtml\Choice\Edit
 */
class Form extends \Magento\Backend\Block\Widget\Form\Generic
{
    /** @var \Scommerce\CookiePopup\Model\ChoiceRegistry */
    private $registry;

    /** @var \Scommerce\CookiePopup\Model\Config\SourceFactory */
    private $sourceFactory;

    /**
     * Form constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Scommerce\CookiePopup\Model\ChoiceRegistry $registry
     * @param \Magento\Framework\Data\FormFactory $formFactory
     * @param \Magento\Store\Model\System\Store $systemStore
     * @param \Scommerce\CookiePopup\Model\Config\SourceFactory $sourceFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Scommerce\CookiePopup\Model\ChoiceRegistry $registry,
        \Magento\Framework\Data\FormFactory $formFactory,
        \Magento\Store\Model\System\Store $systemStore,
        \Scommerce\CookiePopup\Model\Config\SourceFactory $sourceFactory,
        array $data = []
    ) {
        $this->sourceFactory = $sourceFactory;
        $this->_systemStore = $systemStore;
        $this->registry = $registry;
        parent::__construct($context, $registry->getRegistry(), $formFactory, $data);
    }

    /**
     * Prepare form
     *
     * @return $this
     */
    protected function _prepareForm()
    {
        $formData = $this->registry->get();

        /** @var \Magento\Framework\Data\Form $form */
        $form = $this->_formFactory->create(
            [
                'data' =>
                    [
                        'id' => 'edit_form',
                        'action' => $this->getData('action'),
                        'method' => 'post'
                    ]
            ]
        );

        $form->setHtmlIdPrefix('choice_');

        $fieldset = $form->addFieldset(
            'base_fieldset',
            [
                'legend' => __('General Information'),
                'class' => 'fieldset-wide'
            ]
        );

        $fieldset->addField(
            'choice_name',
            'text',
            [
                'name' => 'choice_name',
                'label' => __('Choice Name'),
                'title' => __('Choice Name'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'cookie_name',
            'text',
            [
                'name' => 'cookie_name',
                'label' => __('Cookie Name'),
                'title' => __('Cookie Name'),
                'required' => true
            ]
        );

        /* Check is single store mode */
        if (!$this->_storeManager->isSingleStoreMode()) {
            $field = $fieldset->addField(
                'store_id',
                'multiselect',
                [
                    'name' => 'stores[]',
                    'label' => __('Store View'),
                    'title' => __('Store View'),
                    'required' => true,
                    'values' => $this->_systemStore->getStoreValuesForForm(false, true)
                ]
            );
            $renderer = $this->getLayout()->createBlock(
                'Magento\Backend\Block\Store\Switcher\Form\Renderer\Fieldset\Element'
            );
            $field->setRenderer($renderer);
        } else {
            $fieldset->addField(
                'store_id',
                'hidden',
                ['name' => 'stores[]', 'value' => $this->_storeManager->getStore(true)->getId()]
            );
            $formData->setStoreId($this->_storeManager->getStore(true)->getId());
        }

        $fieldset->addField(
            'choice_description',
            'textarea',
            [
                'name' => 'choice_description',
                'label' => __('Choice Description'),
                'title' => __('Choice Description'),
                'required' => true
            ]
        );

        $fieldset->addField(
            'list',
            'textarea',
            [
                'name' => 'list',
                'label' => __('Cookies Created By'),
                'title' => __('Cookies Created By'),
                'required' => false
            ]
        );

        $fieldset->addField(
            'required',
            'select',
            [
                'label' => __('Required'),
                'title' => __('Required'),
                'name' => 'required',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        $fieldset->addField(
            'default_state',
            'select',
            [
                'label' => __('Set By Default'),
                'title' => __('Set By Default'),
                'name' => 'default_state',
                'required' => true,
                'options' => ['1' => __('Yes'), '0' => __('No')]
            ]
        );

        if ($formData) {
            if ($formData->getId()) {
                $fieldset->addField(
                    'choice_id',
                    'hidden',
                    ['name' => 'choice_id']
                );
            }

            $form->setValues($formData->getData());
        }

        $form->setUseContainer(true);
        $this->setForm($form);

        return parent::_prepareForm();
    }
}
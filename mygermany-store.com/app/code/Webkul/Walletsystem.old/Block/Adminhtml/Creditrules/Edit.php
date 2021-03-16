<?php

/**
 * Block\Adminhtml\Creditrules Edit.php
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Block\Adminhtml\Creditrules;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry           $registry
     * @param array                                 $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
        $this->_coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Initialize edit block
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'entity_id';
        $this->_blockGroup = 'Webkul_Walletsystem';
        $this->_controller = 'adminhtml_creditrules';
        parent::_construct();
        if ($this->_isAllowedAction('Webkul_Walletsystem::walletcreditrules')) {
            $this->buttonList->update('save', 'label', __('Save Credit Rule'));
            $this->buttonList->add(
                'my_back',
                [
                    'label' =>  'Back',
                    'onclick'   => 'setLocation(\'' . $this->getUrl('*/*/creditrules') . '\')',
                    'class'     =>  'back'
                ]
            );
            $this->buttonList->remove('back');
            $this->buttonList->add(
                'saveandcontinue',
                [
                    'label' => __('Save and Continue Edit'),
                    'class' => 'save',
                    'data_attribute' => [
                        'mage-init' => [
                            'button' =>
                                [
                                    'event' => 'saveAndContinueEdit',
                                    'target' => '#edit_form'
                                ],
                        ],
                    ]
                ],
                -100
            );
        } else {
            $this->buttonList->remove('save');
        }
        if ($this->_isAllowedAction('Webkul_Walletsystem::delete')) {
            $this->buttonList->update(
                'delete',
                'label',
                __('Delete Rule')
            );
            $this->buttonList->update(
                'delete',
                'level',
                -200
            );
        } else {
            $this->buttonList->remove('delete');
        }
    }

    /**
     * Retrieve text for header element depending on loaded post
     *
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        $codRegistry = $this->_coreRegistry->registry('cod_pricerates');
        $codPrice = $this->escapeHtml($codRegistry);
        if ($codPrice->getEntityId()) {
            return __("Edit Rule '%1'", $codPrice->getEntityId());
        } else {
            return __('New Rule');
        }
    }

    /**
     * Check permission for passed action
     *
     * @param string $resourceId
     * @return bool
     */
    protected function _isAllowedAction($resourceId)
    {
        return $this->_authorization->isAllowed($resourceId);
    }
    /**
     * Getter of url for "Save and Continue" button
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl(
            '*/*/save',
            ['_current' => true,
                'back' => 'edit',
                'active_tab' => ''
            ]
        );
    }
}

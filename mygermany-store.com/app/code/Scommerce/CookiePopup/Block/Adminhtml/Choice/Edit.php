<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Block\Adminhtml\Choice;

/**
 * Class Edit
 * @package Scommerce\CookiePopup\Block\Adminhtml\Choice
 */
class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /** @var string */
    protected $_objectId = 'choice_id';

    /** @var string */
    protected $_blockGroup = 'Scommerce_CookiePopup';

    /** @var string So called "container controller" to specify group of blocks participating in some action */
    protected $_controller = 'adminhtml_choice';

    /**
     * Tag edit block
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();

        if (! $this->_authorization->isAllowed('Scommerce_CookiePopup::config_CookiePopup')) {
            $this->removeButton('save');
            $this->removeButton('delete');
            return;
        }

        $this->buttonList->add('saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => [
                            'event' => 'saveAndContinueEdit',
                            'target' => '#edit_form'
                        ],
                    ],
                ]
            ],
            -100
        );

    }

    /**
     * Getter of url for "Save and Continue" button
     * tab_id will be replaced by desired by JS later
     *
     * @return string
     */
    protected function _getSaveAndContinueUrl()
    {
        return $this->getUrl('manage/*/save', ['_current' => true, 'back' => 'edit', 'active_tab' => '']);
    }
}
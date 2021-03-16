<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\Block\Adminhtml\Profile\Edit;

use Magento\Framework\View\Element\UiComponent\Control\ButtonProviderInterface;

/**
 * Class SaveButton
 * @package Plenty\Core\Block\Adminhtml\Profile\Edit
 */
class SaveButton extends GenericButton implements ButtonProviderInterface
{
    /**
     * SaveButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        parent::__construct($context, $registry);
    }

    /**
     * @return array
     */
    public function getButtonData()
    {
        if (!$this->getProfile()) {
            return [];
        }

        return [
            'label' => __('Save Profile'),
            'class' => 'save primary',
            'data_attribute' => [
                'mage-init' => [
                    'button' => ['event' => 'save'],
                    'buttonAdapter' => [
                        'actions' => [
                            [
                                'targetName' => 'plenty_core_profile_form.areas',
                                'actionName' => 'save',
                                'params' => [
                                    true,
                                    [
                                        'id'        => $this->getRequest()->getParam('id'),
                                        'section'   => $this->getRequest()->getParam('section'),
                                        'website'   => $this->getRequest()->getParam('website'),
                                        'store'     => $this->getRequest()->getParam('store')
                                    ]
                                ]
                            ]
                        ]
                    ]
                ]
            ],
            'sort_order' => 80
        ];
    }
}

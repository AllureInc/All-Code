<?php
/**
 * Product attribute edit form observer
 *
 * Copyright © 2013-2017 Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Solrbridge\Search\Observer\Attribute\Edit\Tab\Front;

use Magento\Config\Model\Config\Source;
use Magento\Framework\Module\Manager;
use Magento\Framework\Event\ObserverInterface;
use Solrbridge\Search\Helper\Utility;

class FormAlterObserver implements ObserverInterface
{
    /**
     * @var \Magento\Config\Model\Config\Source\Yesno
     */
    protected $optionList;

    /**
     * @var \Magento\Framework\Module\Manager
     */
    protected $moduleManager;

    /**
     * @param Manager $moduleManager
     * @param Source\Yesno $optionList
     */
    public function __construct(Manager $moduleManager, Source\Yesno $optionList)
    {
        $this->optionList = $optionList;
        $this->moduleManager = $moduleManager;
    }

    /**
     * @param \Magento\Framework\Event\Observer $observer
     * @return void
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        if (!$this->moduleManager->isOutputEnabled('Solrbridge_Search')) {
            return;
        }

        /** @var \Magento\Framework\Data\Form\AbstractForm $form */
        $form = $observer->getForm();

        $fieldset = $form->getElement('front_fieldset');

        $fieldset->addField(
            'solrbridge_search_boost_weight',
            'select',
            [
                'name' => 'solrbridge_search_boost_weight',
                'label' => __("Solr Boost Weight"),
                'title' => __('The heigher value will produce the heigher result.'),
                'note' => __('[SolrBridge] The heigher value will produce the heigher result.'),
                'values' => Utility::getSearchWeightOptions(),
            ]
        );
        
        $fieldset->addField(
            'solrbridge_search_multiple_filter',
            'select',
            [
                'name' => 'solrbridge_search_multiple_filter',
                'label' => __("Solr Allow Multiple Filter"),
                'title' => __('Select Yes to allow multiple filters on frontend for this attribute.'),
                'note' => __('[SolrBridge] Select Yes to allow multiple filters on frontend for this attribute.'),
                'values' => $this->optionList->toOptionArray(),
            ]
        );
        
        $fieldset->addField(
            'solrbridge_search_render_as_dropdown',
            'select',
            [
                'name' => 'solrbridge_search_render_as_dropdown',
                'label' => __('Solr filter as Dropdown'),
                'title' => __('Select Yes to render this attribute as Dropdown in Layer Navigation.'),
                'note' => __('[SolrBridge] Select Yes to render this attribute as Dropdown in Layer Navigation.'),
                'values' => $this->optionList->toOptionArray(),
            ]
        );
    }
}

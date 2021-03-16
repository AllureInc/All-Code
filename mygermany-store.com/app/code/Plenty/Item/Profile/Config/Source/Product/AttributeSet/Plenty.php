<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Product\AttributeSet;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Eav\Api\Data\AttributeSetInterface;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Model\ResourceModel\Import\Attribute\Collection;
use Plenty\Item\Model\ResourceModel\Import\Attribute\CollectionFactory;

/**
 * Class Plenty
 * @package Plenty\Item\Profile\Config\Source\Product\AttributeSet
 */
class Plenty implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var Collection
     */
    private $_collection;

    /**
     * @var \Magento\Framework\App\RequestInterface $_requestInterface
     */
    private $_requestInterface;

    /**
     * @var array
     */
    private $_options;

    /**
     * Plenty constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory
    ) {
        $this->_requestInterface = $context->getRequest();
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        if (null === $this->_options) {
            $this->_options = [['value' => '', 'label' => __('--- select ---')]];
            foreach ($this->__getAttributeCollection() as $attribute) {
                $this->_options[] = [
                    'value' => $attribute->getData('property_id'),
                    'label' => $attribute->getData('property_code'),
                ];
            }
        }

        return $this->_options;
    }

    /**
     * @return Collection
     */
    private function __getAttributeCollection()
    {
        if (null === $this->_collection) {
            $collection = $this->_collectionFactory->create();
            $collection->addFieldToFilter('profile_id', (int) $this->_requestInterface->getParam('id'))
                ->addFieldToFilter('type', 'property');
            $this->_collection = $collection->load();
        }

        return $this->_collection;
    }

}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Profile\Config\Source\Category;

use Magento\Framework\Data\OptionSourceInterface;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Api\Data\Import\CategoryInterface;
use Plenty\Item\Model\ResourceModel\Import\Category\Collection;
use Plenty\Item\Model\ResourceModel\Import\Category\CollectionFactory;

/**
 * Class Plenty
 * @package Plenty\Item\Profile\Config\Source\Category
 */
class Plenty implements OptionSourceInterface
{
    /**
     * @var array
     */
    private $_options;

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
            $this->_options = [];
            /** @var CategoryInterface $category */
            foreach ($this->_getCategoryCollection() as $category) {
                $this->_options[] = [
                    'value' => $category->getCategoryId(),
                    'label' => "{$category->getName()} [{$category->getCategoryId()}: {$category->getOriginalPath()}]",
                ];
            }
        }

        return $this->_options;
    }

    /**
     * @return Collection
     */
    private function _getCategoryCollection()
    {
        if (null === $this->_collection) {
            $collection = $this->_collectionFactory->create();
            $collection->addFieldToFilter('profile_id', (int) $this->_requestInterface->getParam('id'));
            $this->_collection = $collection->load();
        }

        return $this->_collection;
    }
}
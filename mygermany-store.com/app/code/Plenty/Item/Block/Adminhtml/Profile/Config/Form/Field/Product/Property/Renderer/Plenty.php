<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Property\Renderer;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\View\Element\Context;
use Plenty\Item\Model\ResourceModel\Import\Attribute\Collection;
use Plenty\Item\Model\ResourceModel\Import\Attribute\CollectionFactory;

/**
 * Class Plenty
 * @package Plenty\Item\Block\Adminhtml\Profile\Config\Form\Field\Product\Property\Renderer
 */
class Plenty extends Select
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
     * Plenty constructor.
     * @param Context $context
     * @param CollectionFactory $collectionFactory
     * @param array $data
     */
    public function __construct(
        Context $context,
        CollectionFactory $collectionFactory,
        array $data = [])
    {
        $this->_requestInterface = $context->getRequest();
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml()
    {
        if (!$this->getOptions()) {
            foreach ($this->__getAttributeCollection() as $attribute) {
                $this->addOption($attribute->getData('property_id'), addslashes($attribute->getData('property_code')));
            }
        }
        return parent::_toHtml();
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

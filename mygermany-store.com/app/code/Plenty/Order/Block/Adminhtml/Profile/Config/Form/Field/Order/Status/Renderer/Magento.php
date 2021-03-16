<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Status\Renderer;

use Magento\Sales\Model\ResourceModel\Order\Status\Collection;

/**
 * Class Magento
 * @package Plenty\Order\Block\Adminhtml\Profile\Config\Form\Field\Order\Status\Renderer
 */
class Magento extends \Magento\Framework\View\Element\Html\Select
{
    /**
     * @var Collection
     */
    private $_statusCollectionFactory;

    /**
     * @var array
     */
    private $_statuses;

    /**
     * Magento constructor.
     * @param \Magento\Framework\View\Element\Context $context
     * @param Collection $collection
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Context $context,
        Collection $collection,
        array $data = [])
    {
        $this->_statusCollectionFactory = $collection;
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
            foreach ($this->_getStatuses() as $status) {
                if (!isset($status['value']) || !isset($status['label'])) {
                    continue;
                }
                $this->addOption($status['value'], addslashes($status['label']));
            }
        }
        return parent::_toHtml();
    }

    /**
     * @return array
     */
    private function _getStatuses()
    {
        if (null === $this->_statuses) {
            $this->_statuses = $this->_statusCollectionFactory->toOptionArray();
        }

        return $this->_statuses;
    }
}

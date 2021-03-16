<?php
/**
 * @category  Cnnb
 * @package   Cnnb_Gtm
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the Magento Attribute Options
 */
namespace Cnnb\Gtm\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

/**
 * AttributeColumn | Admin Block Class
 */
class AttributeColumn extends Select
{
    /**
     * Set "name" for <select> element
     *
     * @param string $value
     * @return $this
     */
    public function setInputName($value)
    {
        return $this->setName($value);
    }

    /**
     * Set "id" for <select> element
     *
     * @param $value
     * @return $this
     */
    public function setInputId($value)
    {
        return $this->setId($value);
    }

    /**
     * Render block HTML
     *
     * @return string
     */
    public function _toHtml(): string
    {
        if (!$this->getOptions()) {
            $this->setOptions($this->getSourceOptions());
        }
        return parent::_toHtml();
    }

    /**
     * Provide Magento Attribute Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $attribute_data = [];
        $attributeFactory = ObjectManager::getInstance()->get(CollectionFactory::class);
        $attributeInfo = $attributeFactory->create();

        foreach ($attributeInfo as $items) {
            $attribute_data[] = [
                'label'=> $items->getFrontendLabel(),
                'value'=> $items->getAttributeCode()
            ];
        }

        return $attribute_data;
    }
}

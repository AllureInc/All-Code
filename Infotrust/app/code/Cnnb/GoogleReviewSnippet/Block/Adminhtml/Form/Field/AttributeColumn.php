<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GoogleReviewSnippet
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 * 
 * Admin Block Class
 * Provides the Magento Attribute Options
 */
namespace Cnnb\GoogleReviewSnippet\Block\Adminhtml\Form\Field;

use Magento\Framework\Option\ArrayInterface;
use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;

class AttributeColumn implements ArrayInterface
{    
    public function toOptionArray()
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
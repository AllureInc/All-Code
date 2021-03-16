<?php
/**
 * @category  Cnnb
 * @package   Cnnb_AlgoliaFix
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the Magento Attribute Options
 */
namespace Cnnb\AlgoliaFix\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\Attribute\CollectionFactory;
use Magento\Framework\Option\ArrayInterface;

/**
 * AttributeColumn | Admin Block Class
 */
class AttributesData implements ArrayInterface
{
    /**
     * @var CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Attribute constructor.
     *
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(CollectionFactory $collectionFactory)
    {
        $this->_collectionFactory = $collectionFactory;
    }
    /**
     * Provide Magento Attribute Options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $attributes   = $this->_collectionFactory->create()->addVisibleFilter();
        $arrAttribute = [];

        foreach ($attributes as $attribute) {
            $arrAttribute[] = [
                'label' => $attribute->getFrontendLabel(),
                'value' => $attribute->getAttributeCode()
            ];
        }

        return $arrAttribute;
    }
}

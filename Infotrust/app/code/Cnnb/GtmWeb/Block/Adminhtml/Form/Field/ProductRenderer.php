<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GtmWeb
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the Product Page Options
 */
namespace Cnnb\GtmWeb\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Model\ResourceModel\Product\CollectionFactory;

/**
 * ProductRenderer | Admin Block Class
 */
class ProductRenderer extends Select
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
     * Provides Banner Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $product_data = [];
        $productCollection = ObjectManager::getInstance()->get(CollectionFactory::class);
        $productInfo = $productCollection->create()->addAttributeToSelect('*');

        $product_data[] = ['label'=> __('Please Select'),'value'=> ''];
        
        foreach ($productInfo as $product) {
            $label = $product->getName().' :: '.$product->getEntityId();
            $product_data[] = [
                'label'=> $label,
                'value'=> $label
            ];
        }

        return $product_data;
    }
}

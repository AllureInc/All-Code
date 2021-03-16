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
use Magento\Store\Model\StoreManagerInterface;

/**
 * StoreNameList | Admin Block Class
 */
class StoreNameList extends Select
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
     * Store Name Options
     *
     * @return array
     */
    private function getSourceOptions(): array
    {
        $store = ObjectManager::getInstance()->get(StoreManagerInterface::class);
        $storeManagerDataList = $store->getStores();
        $options = ['label'=> __('Please Select'), 'value'=> ''];
       
        foreach ($storeManagerDataList as $storeData) {
            if ($storeData->getId() > 0) {
                $options[] = [
                    'label' => $storeData->getName(),
                    'value' => $storeData->getName()
                ];
            }
        }
        return $options;
    }
}

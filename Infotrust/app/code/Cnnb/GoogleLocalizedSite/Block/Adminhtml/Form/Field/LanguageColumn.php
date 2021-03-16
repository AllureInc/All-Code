<?php
/**
 * @category  Cnnb
 * @package   Cnnb_GoogleLocalizedSite
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Admin Block Class
 * Provides the configuration fields data
 */
namespace Cnnb\GoogleLocalizedSite\Block\Adminhtml\Form\Field;

use Magento\Framework\View\Element\Html\Select;
use Magento\Config\Model\Config\Source\Locale;
use Magento\Framework\App\ObjectManager;

/**
 * 
 */
class LanguageColumn extends Select
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
        $lannguageArray = ObjectManager::getInstance()->get(Locale::class)->toOptionArray();
        return $lannguageArray;
    }
}

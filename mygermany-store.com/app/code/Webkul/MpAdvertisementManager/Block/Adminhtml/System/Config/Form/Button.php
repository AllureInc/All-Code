<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Block\Adminhtml\System\Config\Form;
 
use Magento\Framework\App\Config\ScopeConfigInterface;
 
class Button extends \Magento\Config\Block\System\Config\Form\Field
{
    const BUTTON_TEMPLATE = 'Webkul_MpAdvertisementManager::system/config/form/button.phtml';

    /**
     * Set template to itself
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if (!$this->getTemplate()) {
            $this->setTemplate(static::BUTTON_TEMPLATE);
        }
        return $this;
    }
    
    /**
     * Render button
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    public function render(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        // Remove scope label
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }
    
    /**
     * Return list addons ajax url
     *
     * @return string
     */
    public function getAjaxSaveUrl()
    {
        return $this->getUrl('mpadvertisementmanager/pricing/ads');
    }
    
    /**
     * Get the button and scripts contents
     *
     * @param  \Magento\Framework\Data\Form\Element\AbstractElement $element
     * @return string
     */
    protected function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * getButtonHtml get button html
     *
     * @return html
     */
    public function getButtonHtml()
    {
        
        $button = $this->getLayout()->createBlock('\Magento\Backend\Block\Widget\Button')
            ->setData(
                [
                    'id'        => 'ads_config',
                    'label'     => __('Configure Ads'),
                    'class' => 'primary'
                ]
            );
        return $button->_toHtml();
    }

    /**
     * getAdsPositions get ads positions
     *
     * @return array
     */
    public function getAdsPositions()
    {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $positions = $objectManager->get("\Webkul\MpAdvertisementManager\Helper\Data")->getAdsPositions();
        return $positions;
    }

    /**
     * getSavedAdsSettings get save ads settings
     *
     * @return array
     */
    public function getSavedAdsSettings()
    {
        //Get Object Manager Instance
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $helper = $objectManager->get("\Webkul\MpAdvertisementManager\Helper\Data");
        return $helper->getAdsSettings();
    }
}

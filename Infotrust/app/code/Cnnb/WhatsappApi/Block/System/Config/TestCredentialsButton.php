<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block class
 */

namespace Cnnb\WhatsappApi\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;

class TestCredentialsButton extends Field
{
    /**
     * @var $_template;
     */
    protected $_template = 'Cnnb_WhatsappApi::system/config/button.phtml';

    public function __construct(
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
    }

    /**
     * Function for rendering website value
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Function getting HTML
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Function for getting ajax URL
     */
    public function getAjaxSyncUrl()
    {
        return $this->getUrl('whatsapp/api/test');
    }

    /**
     * Function getting Button HTML
     */
    public function getButtonHtml()
    {
        $button = $this->getLayout()->createBlock(
            'Magento\Backend\Block\Widget\Button'
        )->setData(
            [
                'id' => 'api_test_btn',
                'label' => __('Test Credentials'),
            ]
        );
        return $button->toHtml();
    }
}

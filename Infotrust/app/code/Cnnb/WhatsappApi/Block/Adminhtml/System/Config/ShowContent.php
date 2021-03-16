<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Block File
 */

namespace Cnnb\WhatsappApi\Block\Adminhtml\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Backend\Block\Template\Context;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Model\Session;

class ShowContent extends Field
{
    /**
     * @var $_template;
     */
    protected $_template = 'Cnnb_WhatsappApi::system/config/content.phtml';

    /**
     * @var $_session;
     */
    protected $_session;

    public function __construct(
        Context $context,
        Session $session,
        array $data = []
    ) {
        $this->_session = $session;
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
     * Function for getting html
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->_toHtml();
    }

    /**
     * Function for getting session content array
     */
    public function getSessionContentArray()
    {
        if (isset($_SESSION['content_data'])) {
            return $_SESSION['content_data'];
        } else {
            return '';
        }
    }
}

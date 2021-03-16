<?php
/**
 * @category  Cnnb
 * @package   Cnnb_WhatsappApi
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 * Model class
 */

namespace Cnnb\WhatsappApi\Model\Config\Source;

use Magento\Framework\App\ObjectManager;
use Cnnb\WhatsappApi\Helper\Data;
use Magento\Backend\Model\Session;

class TemplateList implements \Magento\Framework\Option\ArrayInterface
{
    /**
     * @var $_helper
     */
    protected $_helper;

    /**
     * @var $_session
     */
    protected $_session;

    public function toOptionArray()
    {
        $this->_helper = ObjectManager::getInstance()->get(Data::class);
        $this->_session = ObjectManager::getInstance()->get(Session::class);
        $messageTemplateList = $this->_helper->getMessageTemplateLists();
        $optionArray = [];
        $contentArray = [];
        if ($messageTemplateList && !empty($messageTemplateList) && isset($messageTemplateList[0]['uuid'])) {
            $optionArray[] = [
                    'value'=> '',
                    'label'=> __(' -- select --')
                ];
            foreach ($messageTemplateList as $key => $value) {
                $optionArray[] = [
                    'value'=> $value['uuid'],
                    'label'=> $value['element_name']
                ];
                $contentArray[$value['uuid']] = [
                    'element_name'=> $value['element_name'],
                    'content'=> $value['content']
                ];
            }
            $this->_session->unsContentArray();
            $this->_session->setContentArray($contentArray);
            $_SESSION['content_data'] = $contentArray;
            return $optionArray;
        }
        return [
            ['value' => '', 'label' => __('No Template Available')]
        ];
    }
}

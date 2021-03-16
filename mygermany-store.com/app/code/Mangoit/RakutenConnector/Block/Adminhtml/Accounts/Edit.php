<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Adminhtml\Accounts;

class Edit extends \Magento\Backend\Block\Widget\Form\Container
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    private $coreRegistry = null;

    /**
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry,
        array $data = []
    ) {
    
        $this->coreRegistry = $registry;
        parent::__construct($context, $data);
    }

    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_objectId = 'id';
        $this->_controller = 'adminhtml_accounts';
        $this->_blockGroup = 'Mangoit_RakutenConnector';

        parent::_construct();

        $this->buttonList->update('save', 'label', __('Save Rakuten Account'));
        $this->buttonList->remove('delete');
        $this->buttonList->remove('reset');
        $this->buttonList->add(
            'saveandcontinue',
            [
                'label' => __('Save and Continue Edit'),
                'class' => 'save',
                'data_attribute' => [
                    'mage-init' => [
                        'button' => ['event' => 'saveAndContinueEdit', 'target' => '#edit_form'],
                    ],
                ]
            ],
            -100
        );
    }

    /**
     * get savecontinue link url
     * @return string
     */
    public function geSaveContinueUrl()
    {
        return $this->getUrl("*/*/save", ["edit"=>1]);
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getHeaderText()
    {
        if ($this->coreRegistry->registry('permissions_user')->getId()) {
            $username = $this->escapeHtml(
                $this->coreRegistry->registry('permissions_user')->getUsername()
            );
            return __("Edit Rakuten Account '%1'", $username);
        } else {
            return __('New Rakuten Account');
        }
    }
}

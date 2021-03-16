<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Adminhtml\Accounts\Edit;

/**
 * Accounts page left menu
 */
class Tabs extends \Magento\Backend\Block\Widget\Tabs
{
    /**
     * Class constructor
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('page_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Amazon Account'));
    }

    /**
     * @return $this
     */
    protected function _beforeToHtml()
    {
        $amzAccountId = (int)$this->getRequest()->getParam('id');
        $this->addTab(
            'main_section',
            [
                'label' => __('Account Information'),
                'title' => __('Amazon Account Information'),
                'content' => $this->getLayout()->createBlock(
                    'Webkul\MpAmazonConnector\Block\Adminhtml\Accounts\Edit\Tab\Main'
                )->toHtml(),
                'active' => true
            ]
        );
        if ($amzAccountId) {
            $this->addTab(
                'product_sync',
                [
                    'label' => __('Mapped Product'),
                    'title' => __('Import Product To Amazon'),
                    'url' => $this->getUrl('*/*/product', ['id' => $amzAccountId]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );
            $this->addTab(
                'order_sync',
                [
                    'label' => __('Mapped Order'),
                    'title' => __('Import Order To Amazon'),
                    'url' => $this->getUrl('*/*/order', ['id' => $amzAccountId]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );
            $this->addTab(
                'import_to_amazon',
                [
                    'label' => __('Export Product'),
                    'url'       => $this->getUrl('*/exportproduct/index', ['id' => $amzAccountId]),
                    'class'     => 'ajax',
                    'title'     => __('Export Product To Amazon'),
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}

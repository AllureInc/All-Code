<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Block\Adminhtml\Accounts\Edit;

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
        $this->setTitle(__('Rakuten Account'));
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
                'title' => __('Rakuten Account Information'),
                'content' => $this->getLayout()->createBlock(
                    'Mangoit\RakutenConnector\Block\Adminhtml\Accounts\Edit\Tab\Main'
                )->toHtml(),
                'active' => true
            ]
        );
        if ($amzAccountId) {
            $this->addTab(
                'product_sync',
                [
                    'label' => __('Mapped Product'),
                    'title' => __('Import Product To Rakuten'),
                    'url' => $this->getUrl('*/*/product', ['id' => $amzAccountId]),
                    'class' => 'ajax',
                    'active' => false
                ]
            );
            $this->addTab(
                'order_sync',
                [
                    'label' => __('Mapped Order'),
                    'title' => __('Import Order To Rakuten'),
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
                    'title'     => __('Export Product To Rakuten'),
                ]
            );
        }
        return parent::_beforeToHtml();
    }
}

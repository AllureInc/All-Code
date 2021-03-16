<?php
/**
 * Block\Adminhtml\Wallet\Edit\Tabs.php
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Block\Adminhtml\Wallet\Edit;

use Magento\Backend\Block\Template\Context;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\Json\EncoderInterface;
use Magento\Backend\Model\Auth\Session;

class Tabs extends \Magento\Backend\Block\Widget\Tabs
{

    /**
     * @var InlineInterface
     */
    protected $_translateInline;

    /**
     * @param Context                                   $context
     * @param InlineInterface                           $translateInline
     * @param EncoderInterface                          $jsonEncoder
     * @param Session                                   $authSession
     * @param array                                     $data
     */
    public function __construct(
        Context $context,
        InlineInterface $translateInline,
        EncoderInterface $jsonEncoder,
        Session $authSession,
        array $data = []
    ) {
        $this->_translateInline = $translateInline;
        parent::__construct($context, $jsonEncoder, $authSession, $data);
    }
    /**
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('wallet_tabs');
        $this->setDestElementId('edit_form');
        $this->setTitle(__('Adjust Amount to Wallet'));
    }

    /**
     * Prepare Layout
     *
     * @return $this
     */
    protected function _prepareLayout()
    {
        $this->addTab(
            'addamounttowallet',
            [
                'label' => __('Adjust Amount to Wallet'),
                'content'=>$this->getLayout()->createBlock(
                    'Webkul\Walletsystem\Block\Adminhtml\Wallet\Edit\Tab\Form'
                )->toHtml(),
                'class' => 'ajax'
            ]
        );
        return parent::_prepareLayout();
    }

    /**
     * Translate html content
     *
     * @param string $html
     * @return string
     */
    protected function _translateHtml($html)
    {
        $this->_translateInline->processResponseBody($html);
        return $html;
    }
}

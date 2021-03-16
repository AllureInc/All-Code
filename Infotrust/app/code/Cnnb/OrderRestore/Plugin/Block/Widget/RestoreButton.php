<?php
/**
 * @category  Cnnb
 * @package   Cnnb_OrderRestore
 * @author    Cnnb
 * @copyright Copyright © CNNB All rights reserved.
 *
 */
namespace Cnnb\OrderRestore\Plugin\Block\Widget;

use Magento\Framework\UrlInterface;
use Cnnb\OrderRestore\Helper\Data;

class RestoreButton
{
    /**
     * Core registry
     *
     * @var \Magento\Framework\Registry
     */
    protected $_coreRegistry = null;

    /**
     * @var \Magento\Framework\App\Action\Context
     */
    protected $_context = null;

    /**
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * @var \Cnnb\OrderRestore\Helper\Data
     */
    protected $_helper;

    /**
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Cnnb\OrderRestore\Helper\Data $helper
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
     */
    public function __construct(
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\App\Action\Context $context,
        UrlInterface $urlBuilder,
        Data $helper
    ) {
        $this->_coreRegistry = $coreRegistry;
        $this->_context = $context;
        $this->_urlBuilder = $urlBuilder;
        $this->_helper = $helper;
    }


    public function afterGetButtonList(
        \Magento\Backend\Block\Widget\Context $subject,
        \Magento\Backend\Block\Widget\Button\ButtonList $buttonList
    )
    {
        $request = $this->_context->getRequest();

        if($request->getFullActionName() == 'sales_order_view' && $this->_helper->isModuleEnable()){
            $order = $this->getOrder();
            if ($order && $order->getState()=='canceled') {
                $message = __('Are you sure you want to restore this order?');
                $buttonList->add(
                    'order_restore',
                    [
                        'label' => __($this->_helper->getOrderRestoreButtonTitle()),
                        'onclick' => "confirmSetLocation('{$message}', '".$this->getUnCancelUrl()."')",
                    ]
                );
            }
        }

        return $buttonList;
    }

    public function getOrder()
    {
        return $this->_coreRegistry->registry('sales_order');
    }

    public function getUnCancelUrl()
    {
        return $this->_urlBuilder->getUrl('*/*/restore', ['order_id'=>$this->getOrder()->getId()]);
    }
} 
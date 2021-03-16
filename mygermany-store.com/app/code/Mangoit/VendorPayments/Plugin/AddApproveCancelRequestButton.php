<?php

namespace Mangoit\VendorPayments\Plugin;

use Magento\Sales\Block\Adminhtml\Order\View;
use Magento\Framework\UrlInterface;
use Potato\Zendesk\Model\Config;

/**
 * Class AddOrderCreateButton
 */
class AddApproveCancelRequestButton
{
    /** @var Config  */
    protected $config;

    /** @var UrlInterface  */
    protected $urlBuilder;

    /**
     * @var \Mangoit\VendorPayments\Model\Vendorinvoices
     */
    protected $invoiceModel;

    /**
     * AddPrintButton constructor.
     * @param Config $config
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        Config $config,
        UrlInterface $urlBuilder,
        \Mangoit\VendorPayments\Model\Vendorinvoices $invoiceModel
    ) {
        $this->config = $config;
        $this->urlBuilder = $urlBuilder;
        $this->invoiceModel = $invoiceModel;
    }

    /**
     * @param View $view
     */
    public function beforeSetLayout(View $view)
    {
        $url = $this->urlBuilder->getUrl('vendorpayments/cancelrequest/approve/order_id/'.$view->getOrderId());

        $allOrders = $this->invoiceModel->getCollection()
            ->addFieldToFilter('canceled_order_id', $view->getOrderId())
            ->addFieldToFilter('cancellation_req_status', '5')->count();
        if ($allOrders) {
            if ($this->config->isSupportOrderSection()) {
                $view->addButton('approve_cancel_btn',
                    [
                        'label'   => __('Approve Cancel Request'),
                        'class'   => 'approve_cancel_btn',
                        'onclick' => "setLocation(window.location = '".$url."')",
                    ]
                );
            }
            # code...
        }
    }
}

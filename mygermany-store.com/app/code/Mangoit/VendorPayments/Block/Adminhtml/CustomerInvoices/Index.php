<?php

namespace Mangoit\VendorPayments\Block\Adminhtml\CustomerInvoices;


class Index extends \Magento\Backend\Block\Widget\Container
{
    public function __construct(
    	\Magento\Backend\Block\Widget\Context $context,
    	array $data = []
    ) {
        parent::__construct($context, $data);
        $this->addButton(
            'download_report_form_submit',
            ['label' => __('Download Report'), 'onclick' => 'reportDownloadFormSubmit()', 'class' => 'primary']
        );
    }
}

<?php
namespace Mangoit\VendorPayments\Plugin;
 
class Cancelbtn
{
    public function beforeGetOrderId(\Magento\Sales\Block\Adminhtml\Order\View $subject){
        $subject->addButton(
                'reset33',
                ['label' => __('Button 2'), 'onclick' => 'setLocation(window.location.href)', 'class' => 'reset'],
                -1
            );
        return null;
    } 
}
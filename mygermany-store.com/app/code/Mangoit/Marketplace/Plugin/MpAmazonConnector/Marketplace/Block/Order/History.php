<?php
namespace Mangoit\Marketplace\Plugin\MpAmazonConnector\Marketplace\Block\Order;

class History extends \Webkul\MpAmazonConnector\Plugin\Marketplace\Block\Order\History
{

    public function afterGetAllSalesOrder(\Webkul\Marketplace\Block\Order\History $subject, $result)
    {
        $result->getSelect()->where("`omni_channel` IS NULL or `omni_channel` != 'amazon'");
        return $result;
    }
}

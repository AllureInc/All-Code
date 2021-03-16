<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Plugin\Marketplace\Block\Order;

class History
{

    public function afterGetAllSalesOrder(\Webkul\Marketplace\Block\Order\History $subject, $result)
    {
        $result->addFieldToFilter(
            'omni_channel',
            ['neq' => 'amazon']
        );
        return $result;
    }
}

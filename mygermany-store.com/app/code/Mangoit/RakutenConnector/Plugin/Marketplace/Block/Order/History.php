<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Plugin\Marketplace\Block\Order;

class History
{

    public function afterGetAllSalesOrder(\Webkul\Marketplace\Block\Order\History $subject, $result)
    {
        $result->addFieldToFilter(
            'is_rakuten',
            ['null' => true]
        );
    	/*echo "<pre>";
    	print_r($result->getData());
    	die("..33");*/
        return $result;
    }
}

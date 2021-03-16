<?php
namespace Mangoit\LifeRayConnect\Api;
 
interface OrderInvoiceInterface
{
    /**
     * Order Invoice
     *
     * @api
     * @param string $orderid is a Magento Order id
     * @return string success or failure message
     */
    public function orderinvoice($orderid);
}
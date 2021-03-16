<?php

namespace Kerastase\Aramex\Model\Order;

/**
 * Kerastase Package
 * User: wbraham
 * Date: 7/9/19
 * Time: 09:09 AM
 */
class Order
{
    protected $sku;

    protected $qtyOrdered;

    protected $unitPrice;

    /**
     * @var \Kerastase\Aramex\Helper\Data
     */
    protected $helper;

    public function __construct(
        \Kerastase\Aramex\Helper\Data $helper
    )
    {
        $this->helper = $helper;
        $this->helper->log('$productId::' . $this->sku);
        return $this;
    }

    public function getSku()
    {
        return $this->sku;
    }

    public function getQtyOrdered()
    {
        return $this->qtyOrdered;
    }

    public function getUnitPrice()
    {
        return $this->unitPrice;
    }
}

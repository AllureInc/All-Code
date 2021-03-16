<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */

namespace Mangoit\RakutenConnector\Block\Account;

use Magento\Framework\View\Element\Template\Context;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Helper\Orders as HelperOrders;
use Webkul\Marketplace\Model\OrdersFactory;

class Dashboard extends \Magento\Framework\View\Element\Template
{
    /**
     * @var OrdersFactory $ordersFactory
     */
    private $ordersFactory;

    /**
     *
     * @param Context $context
     * @param OrdersFactory $ordersFactory
     * @param \Magento\Framework\Pricing\Helper\Data $priceHelper
     * @param \Webkul\Marketplace\Model\SaleslistFactory $saleslist
     * @param HelperData $mpHelper
     * @param array $data
     */
    public function __construct(
        Context $context,
        OrdersFactory $ordersFactory,
        \Magento\Framework\Pricing\Helper\Data $priceHelper,
        \Webkul\Marketplace\Model\SaleslistFactory $saleslist,
        HelperData $mpHelper,
        array $data = []
    ) {
        $this->ordersFactory = $ordersFactory;
        $this->priceHelper = $priceHelper;
        $this->saleslist = $saleslist;
        $this->mpHelper = $mpHelper;
        parent::__construct($context, $data);
    }

    /**
     * get etsy orders
     *
     * @return array
     */
    public function getAmzOrders()
    {
        $collection = $this->ordersFactory
                    ->create()
                    ->getCollection()
                    ->addFieldToFilter('is_rakuten', 1);
        return $collection;
    }

    /**
     * get etsy order view url
     *
     * @return string
     */
    public function getAmzOrdersViewUrl()
    {
        return $this->getUrl('rakutenconnect/order/history', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * get total sales
     *
     * @return string
     */
    public function getTotalSales()
    {
        $sellerId = $this->mpHelper->getCustomerId();
        $collection = $this->saleslist->create()
                        ->getCollection()
                        ->addFieldToFilter(
                            'main_table.is_rakuten',
                            1
                        )->addFieldToFilter(
                            'main_table.is_rakuten',
                            $sellerId
                        );
        $total = 0;
        foreach ($collection as $coll) {
            $total += $coll->getActualSellerAmount();
        }
        return $this->priceHelper->currency($total, true, false);
    }
}

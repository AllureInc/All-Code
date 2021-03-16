<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */

namespace Webkul\MpAmazonConnector\Block\Account;

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
                    ->addFieldToFilter('omni_channel', 'amazon');
        return $collection;
    }

    /**
     * get etsy order view url
     *
     * @return string
     */
    public function getAmzOrdersViewUrl()
    {
        return $this->getUrl('mpamazonconnect/order/history', ['_secure' => $this->getRequest()->isSecure()]);
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
                            'main_table.omni_channel',
                            'amazon'
                        )->addFieldToFilter(
                            'main_table.seller_id',
                            $sellerId
                        );
        $total = 0;
        foreach ($collection as $coll) {
            $total += $coll->getActualSellerAmount();
        }
        return $this->priceHelper->currency($total, true, false);
    }
}

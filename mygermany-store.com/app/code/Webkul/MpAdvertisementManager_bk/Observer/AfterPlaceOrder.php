<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class AfterPlaceOrder implements ObserverInterface
{

    /**
     * @var \Magento\Sales\Model\Order
     */
    protected $_salesOrder;

    /**
     * @var \Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory
     */
    protected $_adsPurchaseDetail;

    /**
     * @var \Magento\Sales\Model\Order\ItemFactory
     */
    protected $_magentoSalesOrderItem;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * Constructer
     *
     * @param \Magento\Sales\Model\Order $salesOrder
     * @param \Magento\Framework\Session\SessionManagerInterface $session
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\SalesRule\Model\Rule $salesRule
     * @param \Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory $adsPurchaseDetail
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem
     */
    public function __construct(
        \Magento\Sales\Model\Order $salesOrder,
        \Webkul\MpAdvertisementManager\Model\AdsPurchaseDetailFactory $adsPurchaseDetail,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem
    ) {
        $this->_salesOrder = $salesOrder;
        $this->_adsPurchaseDetail = $adsPurchaseDetail;
        $this->_messageManager = $messageManager;
        $this->_magentoSalesOrderItem = $magentoSalesOrderItem;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
          $order = $observer->getEvent()->getOrder();
          // $oids=$observer->getOrderIds();
          // $order = $this->_salesOrder->load($oids);
            $data = [];
        foreach ($order->getAllItems() as $item) {
            if ($item->getSku() == "wk_mp_ads_plan") {
                try {
                    $options = $this->_magentoSalesOrderItem->create()->load($item->getItemId())->getProductOptions();
                    $data['product_id'] = $item->getProductId();
                    $data['price'] = $item->getPrice();
                    $data['sku'] = $item->getSku();
                    $data['order_id'] = $order->getEntityId();
                    $data['seller_id'] = $order->getCustomerId();
                    $data['block_name'] = $options['options'][0]['value'];
                    $data['block_position'] = $options['options'][1]['value'];
                    $data['block'] = $options['info_buyRequest'][$data['block_position']]['block'];
                    $data['valid_for'] = $options['options'][2]['value'];
                    $data['store_id'] = $order->getStoreId();
                    $data['store_name'] = $order->getStoreName();
                    $data['created_at'] = $order->getCreatedAt();
                    $data['enable'] = 1;
                    $data['item_id'] = $item->getItemId();
                    $adsPurchaseDetailModel = $this->_adsPurchaseDetail->create();
                    $adsPurchaseDetailModel->setData($data);
                    $adsPurchaseDetailModel->save();
                } catch (\Exception $e) {
                    $this->_messageManager->addError(__($e->getMesage()));
                }
            }
        }
    }
}

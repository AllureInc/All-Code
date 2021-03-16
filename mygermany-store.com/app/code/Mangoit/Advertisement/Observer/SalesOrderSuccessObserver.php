<?php

namespace Mangoit\Advertisement\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class SalesOrderSuccessObserver implements ObserverInterface
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
    protected $marketplaceHelper;
    protected $currencyModel;
    protected $_scopeConfig;
    protected $_transportBuilder;


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
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem,
        \Magento\Directory\Model\Currency $currencyModel,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder
    ) {
        $this->_salesOrder = $salesOrder;
        $this->_adsPurchaseDetail = $adsPurchaseDetail;
        $this->_messageManager = $messageManager;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_magentoSalesOrderItem = $magentoSalesOrderItem;
        $this->currencyModel = $currencyModel;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $logger =  $objectManager->create('\Psr\Log\LoggerInterface');
        
        $orderIds = $observer->getOrderIds();

        foreach ($orderIds as $lastOrderId) {
            $adsPurchaseDetailColltn =  $this->_adsPurchaseDetail->create()
                ->getCollection()
                ->addFieldToFilter('order_id', $lastOrderId)
                ->addFieldToFilter('seller_id', ['neq' => 0]);

            if($adsPurchaseDetailColltn->count() > 0) {
                continue;
            }

            $order = $this->_salesOrder->load($lastOrderId);
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

                        $logger->info("#### MpAdvertisementManager Logger ####");
                        $logger->info("#### canInvoice: ".$order->canInvoice());
                        $logger->info("#### if condition  ");
                        $logger->info(!$order->canInvoice());
                        if (!$order->canInvoice()) {
                            $data['invoice_generated'] = 1;
                        }

                        $adsPurchaseDetailModel = $this->_adsPurchaseDetail->create();
                        $adsPurchaseDetailModel->setData($data);
                        $adsPurchaseDetailModel->save();


                        $sellerDataObj = $this->marketplaceHelper->getSellerDataBySellerId($order->getCustomerId());
                        $vendorName = '';
                        foreach ($sellerDataObj->getData() as $data) {
                            $vendorName = $data['name'];
                        }

                        $postObjectData = [];
                        $postObjectData['order_id'] = $order->getIncrementId();
                        $postObjectData['block_name'] = $options['options'][0]['value'];
                        $postObjectData['valid_for'] = $options['options'][2]['value'];
                        $postObjectData['sellername'] = $vendorName;
                        $currencyCode = $order->getOrderCurrencyCode();
                        $currencySymbol = $this->currencyModel->load($currencyCode)->getCurrencySymbol();
                        $precision = 2;   // for displaying price decimals 2 point
                        //get formatted price by currency
                        $formattedPrice = $this->currencyModel->format($order->getBaseSubtotalInclTax(), ['symbol' => $currencySymbol, 'precision'=> $precision], false, false);
                        $postObjectData['price'] = $formattedPrice;

                        $generalEmail = $this->_scopeConfig->getValue(
                                'trans_email/ident_general/email',
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );

                        $salesName = $this->_scopeConfig->getValue(
                                'trans_email/ident_sales/name',
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );
                        $salesEmail = $this->_scopeConfig->getValue(
                                'trans_email/ident_sales/email',
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );

                        $emailTemplate = $this->_scopeConfig->getValue(
                                'marketplace/ads_settings/admin_notif_template',
                                \Magento\Store\Model\ScopeInterface::SCOPE_STORE
                            );

                        $postObject = new \Magento\Framework\DataObject();
                        $postObject->setData($postObjectData);

                        $sender = [
                           'name' => $salesName,
                           'email' => $salesEmail,
                        ];

                        $transport = $this->_transportBuilder
                            ->setTemplateIdentifier($emailTemplate)
                            ->setTemplateOptions(
                                [
                                    'area' => \Magento\Framework\App\Area::AREA_ADMINHTML,
                                    'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                                ]
                            )
                            ->setTemplateVars(['data' => $postObject])
                            ->setFrom($sender)
                            ->addTo($generalEmail);

                        $transport->getTransport()->sendMessage();
                    } catch (\Exception $e) {
                        $this->_messageManager->addError(__($e->getMesage()));
                    }
                }
            }
        }
        // $order = $observer->getEvent()->getOrder();
        // $oids=$observer->getOrderIds();
    }
}

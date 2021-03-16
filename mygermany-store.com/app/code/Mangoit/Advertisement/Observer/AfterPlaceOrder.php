<?php

namespace Mangoit\Advertisement\Observer;

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

    protected $_scopeConfig;
    protected $marketplaceHelper;
    protected $_transportBuilder;

    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyModel;

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
        \Magento\Sales\Model\Order\ItemFactory $magentoSalesOrderItem,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder,
        \Magento\Directory\Model\Currency $currencyModel
    ) {
        $this->_salesOrder = $salesOrder;
        $this->_adsPurchaseDetail = $adsPurchaseDetail;
        $this->_messageManager = $messageManager;
        $this->_magentoSalesOrderItem = $magentoSalesOrderItem;

        $this->_scopeConfig = $scopeConfig;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_transportBuilder = $transportBuilder;
        $this->currencyModel = $currencyModel;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        $order = $observer->getEvent()->getOrder();
        foreach ($order->getAllItems() as $item) {
            if ($item->getSku() == "wk_mp_ads_plan") {
                try {
                    $sellerDataObj = $this->marketplaceHelper->getSellerDataBySellerId($order->getCustomerId());
                    $vendorName = '';
                    foreach ($sellerDataObj->getData() as $data) {
                        $vendorName = $data['name'];
                    }
                    $options = $this->_magentoSalesOrderItem->create()->load($item->getItemId())->getProductOptions();

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
                    $this->_messageManager->addError(__($e->getMessage()));
                }
            }
        }
    }
}

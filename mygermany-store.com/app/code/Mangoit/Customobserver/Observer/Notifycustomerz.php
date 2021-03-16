<?php

namespace Mangoit\Customobserver\Observer;

class Notifycustomerz implements \Magento\Framework\Event\ObserverInterface
{
	protected $logger;
	protected $_stockItem;
	protected $_scopeConfig;
    protected $_transportBuilder;
    protected $_catalogProductHelper;

    public function __construct(\Psr\Log\LoggerInterface $logger, 
    	\Magento\CatalogInventory\Api\StockStateInterface $stockItem, 
    	\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig, 
        \Magento\Framework\Mail\Template\TransportBuilder $transportBuilder, 
        \Magento\Catalog\Helper\Product $catalogProductHelper)
    {
        $this->logger = $logger;
        $this->_stockItem = $stockItem;
        $this->_scopeConfig = $scopeConfig;
        $this->_transportBuilder = $transportBuilder;
        $this->_catalogProductHelper = $catalogProductHelper;
    }

	public function execute(\Magento\Framework\Event\Observer $observer)
	{
		//Name and Email Address of Store Owner.
		$generalName = $this->_scopeConfig->getValue('trans_email/ident_general/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $generalEmail = $this->_scopeConfig->getValue('trans_email/ident_general/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //Name and Email Address of Sales Representative.
        $salesName = $this->_scopeConfig->getValue('trans_email/ident_sales/name', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        $salesEmail = $this->_scopeConfig->getValue('trans_email/ident_sales/email', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        //Value of 'No. of minimum quantity allowed'
        $minQty = $this->_scopeConfig->getValue('cataloginventory/item_options/notify_stock_qty', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        $quote = $observer->getEvent()->getQuote();
        $order = $quote->getAllVisibleItems();

        foreach ($order as $orders) {
            //value of stock quantity of product.
            $stock = $this->_stockItem->getStockQty($orders->getProduct()->getId());

            //Value of custom email template from store.
            $emailTemplate = $this->_scopeConfig
                ->getValue('cataloginventory/item_options/custom_email_template', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
            
            $this->logger->info($minQty.','.$stock);
            $this->logger->debug($minQty.','.$stock);

            //Sending mail to customer if the stock qunatity goes below the defined minimum quantity.
            if ($stock <= $minQty && $stock != 0) {
                $productId = $orders->getProduct()->getId();
                $productName = $orders->getProduct()->getName();
                $productUrl = $this->_catalogProductHelper->getProductUrl($productId);

                //sending product name, product id and product url with the mail.
                $postObjectData = array();
                $postObjectData['name'] = $productName;
                $postObjectData['id'] = $productId;
                $postObjectData['url'] = $productUrl;
                $postObject = new \Magento\Framework\DataObject();
                $postObject->setData($postObjectData);
                $sender = [
                   'name' => $salesName,
                   'email' => $salesEmail,
                ];

                $storeScope = \Magento\Store\Model\ScopeInterface::SCOPE_STORE;
                $transport = $this->_transportBuilder
                ->setTemplateIdentifier($emailTemplate)
                ->setTemplateOptions(
                    [
                        'area' => \Magento\Framework\App\Area::AREA_FRONTEND,
                        'store' => \Magento\Store\Model\Store::DEFAULT_STORE_ID,
                    ]
                )
                ->setTemplateVars(['data' => $postObject])
                ->setFrom($sender)
                ->addTo($generalEmail)
                ->getTransport();
                $transport->sendMessage();
               // $this->logger->info($minQty);
               // $this->logger->debug($minQty);
            }
        }
	}
}
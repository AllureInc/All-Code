<?php
/**
 * MangoIt Solutions
 *
 * @category MangoIt
 * @package Dmn112_Autoinvoice
 * @author MangoIt
 * @copyright Copyright (c) 2010-2018 MangoIt Solutions Private Limited
 */

namespace Dmn112\Autoinvoice\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\ResourceConnection;

class ChangeInvoicePrefix implements ObserverInterface
{
    /**
     * Dmn112 Helper Class
     *  @var \Magento\Catalog\Helper\Data
     */
    protected $_helper;

    /**
     * ScopeConfigInterface Class
     *  @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $_scopeConfig;
 
    /**
     * Order Invoice CollectionFactory Class
     *  @var \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory
     */
    protected $_invoiceCollectionFactory;
 
    /**
     * LoggerInterface Class
     *  @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * LoggerInterface Class
     *  @var \Psr\Log\LoggerInterface
     */
    protected $sequenceManager;
    /**
     * @var \Magento\Sales\Model\Order\Email\Sender\InvoiceSender
     */
    protected $invoiceSender;

    public function __construct(
        \Magento\Sales\Model\ResourceModel\Order\Invoice\CollectionFactory $invoiceCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Dmn112\Autoinvoice\Helper\Data $helper,
        \Magento\SalesSequence\Model\Manager $sequenceManager,
        \Magento\Sales\Model\Order\Email\Sender\InvoiceSender $invoiceSender
    ) {
        $this->_invoiceCollectionFactory = $invoiceCollectionFactory;
        $this->logger = $logger;
        $this->_scopeConfig = $scopeConfig;
        $this->_helper = $helper;
        $this->sequenceManager = $sequenceManager;
        $this->invoiceSender = $invoiceSender;
    }

    /**
     * Execute when Invoice Paid
     * @param \Magento\Framework\Event\Observer $observer
     * return void
     */
    public function execute(Observer $observer)
    {
        $this->logger->debug('Invoice Paid');
        if(!$this->_helper->_isEnabledModule())
        {
            return;
        }

        $invoiceInstance = $observer->getInvoice();
        $newId = $this->sequenceManager->getSequence(
                $invoiceInstance->getEntityType(),
                $invoiceInstance->getStore()->getGroup()->getDefaultStoreId()
            )->getNextValue();

        $invoiceInstance = $observer->getInvoice();
        $orderInstance = $invoiceInstance->getOrder();

        $customerCntry = $orderInstance->getBillingAddress()->getCountryId();
        $euCountries = $this->_scopeConfig->getValue('general/country/eu_countries');
        $euCntrsArr = explode(',', $euCountries);
        $isEuropean = (in_array($customerCntry, $euCntrsArr)) ? : false;
        $shippingMethod = $orderInstance->getShippingMethod();

        $this->logger->debug('Params: isEuropean -> '. $isEuropean. ' shippingMethod -> '.$shippingMethod);

        $prefix = '';
        if($isEuropean && $shippingMethod == 'dropship_dropship') {
            $prefix = 'opCE_';
        } elseif (!$isEuropean && $shippingMethod == 'dropship_dropship') {
            $prefix = 'opCD_';
        } elseif ($isEuropean && $shippingMethod == 'warehouse_warehouse') {
            $prefix = 'opCE_';
        } elseif (!$isEuropean && $shippingMethod == 'warehouse_warehouse') {
            $prefix = 'opCD_';
        }

        $invoiceInstance->setData("increment_id", $prefix.$newId)->save();

        if($this->_helper->_isEnabledInvoiceEmail())
        {
            $this->invoiceSender->send($invoiceInstance);
        }
    }

    // public function execute(\Magento\Framework\Event\Observer $observer)
    // {
    //     if(!$this->_helper->_isEnabledModule())
    //     {
    //         return;
    //     }
    //     $orderIds = $observer->getEvent()->getOrderIds();
    //     $orderId = $orderIds[0];
    //     $order = $this->_helper->getOrderByOrderId($orderId);
    //     $this->_helper->assignInvoice($order);
    //     $this->_helper->createShipments($order);
    //     return;
    // }
}

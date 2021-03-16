<?php

namespace Cor\Customizations\Observer;

use Magento\Framework\Event\ObserverInterface;

class OrderEmailData implements ObserverInterface
{
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $objectManager = \Magento\Framework\App\ObjectManager::getInstance();
        $storeManager = $objectManager->get('\Magento\Store\Model\StoreManagerInterface');
        $baseUrl = $storeManager->getStore()->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        $transport = $observer->getTransport();

        $order = $transport['order'];

        $order_id = $order->getEntityId();
        $cor_one_time_key = $order->getCorOneTimeKey();

        //12:345678
        //order_id:cor_one_time_key
        $barcodeText = $order_id.':'.$cor_one_time_key;

        $url = $baseUrl."barcode/barcode.php?size=50&text=".$barcodeText; //."&print=true";
        $barcodeImg = "<img style='height: 90px !important; width: 100% !important' alt='".$barcodeText."' src='".$url."' />";

        $transport['barcode'] = $barcodeImg;
    }
}

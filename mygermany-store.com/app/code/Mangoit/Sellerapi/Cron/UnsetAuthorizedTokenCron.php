<?php

namespace Mangoit\Sellerapi\Cron;

use \Psr\Log\LoggerInterface;

class UnsetAuthorizedTokenCron {

  protected $_seller;
  protected $_customer;
  protected $_objectManager;
  protected $logger;

  public function __construct(
    \Webkul\Marketplace\Model\Seller $seller,
    \Magento\Customer\Model\Customer $customer,
    \Magento\Framework\ObjectManagerInterface $objectmanager,
    LoggerInterface $logger
  )
  {
    $this->_seller = $seller;
    $this->_customer = $customer;
    $this->_objectManager = $objectmanager;
    $this->logger = $logger;
  }

  public function execute()
  {
    $this->logger->info('### Seller_api_token cron started ###');
    $seller_id_array = [];
    $collection = $this->_seller->getCollection();
    $customer = $this->_objectManager->create('Magento\Customer\Model\Customer');
    
    foreach ($collection as $seller) {
      $customer->load($seller->getSellerId());
      if ($customer->getSellerApiToken()) {
        $this->logger->info('### API Token is unset for custome ID: '.$customer->getId());
        $customer->setSellerApiToken('');
        $customer->save();
        $this->logger->info('### API Token has been unset for custome ID: '.$customer->getId());
        $this->logger->info('### ### ### ### ### ### ### ### ###');
      }
      /*$customer->setCustomAttribute('seller_api_token', '');
      $customer->save();*/
    }
    $this->logger->info('### ### Cron Ended Successfully ### ###');
  }

}
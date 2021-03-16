<?php

/**
 * Copyright © 2016 Mangoit. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\Orderdispatch\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Address;

class PlaceorderAfter implements ObserverInterface 
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;
    
    protected $_addressRepository;

    protected $_objectManager;
    protected $_customer;
    protected $_customerSession; 
    
    public function __construct(ScopeConfigInterface $scopeConfig,
    \Magento\Customer\Api\AddressRepositoryInterface $addressRepository,
    \Magento\Framework\ObjectManagerInterface $objectmanager,
    \Magento\Customer\Model\Customer $_customer,
    \Magento\Customer\Model\Session $_customerSession
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->_addressRepository = $addressRepository;
        $this->_objectManager = $objectmanager;
        $this->_customer = $_customer;
        $this->_customerSession = $_customerSession;
    }
    
    public function execute(EventObserver $observer) 
    {
        if (!$this->_customerSession->getCustomer()->getComplianceCheck()) {
            $shippingMethod = $observer->getOrder()->getShippingMethod();
            if ($shippingMethod == 'warehouse_warehouse') {
                $obserObj = $observer->getEvent()->getOrder()->setState('compliance_check')->setStatus('compliance_check')
                ->addStatusToHistory('compliance_check', ' ')->save();
            } elseif ($shippingMethod == 'dropship_dropship') {
                $obserObj = $observer->getEvent()->getOrder()->setState('new')->setStatus('pending')
                ->addStatusToHistory('pending', ' ')->save();
            }
        }
    }
}
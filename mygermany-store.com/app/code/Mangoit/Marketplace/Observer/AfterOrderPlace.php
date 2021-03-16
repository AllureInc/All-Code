<?php

/**
 * Copyright © 2016 Magestore. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Mangoit\Marketplace\Observer;

use Magento\Framework\Event\Observer as EventObserver;
use Magento\Framework\Event\ObserverInterface;
use Magestore\OrderSuccess\Block\Adminhtml\Order\Button\ButtonAbstract;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Sales\Model\Order\Address;

class AfterOrderPlace implements ObserverInterface 
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
    
    /**
     * 
     * @param EventObserver $observer
     * @return $this
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute(EventObserver $observer) 
    {
        $shippingMethod = $observer->getOrder()->getShippingMethod();
        $orderObj = $observer->getOrder();

        $shippingAddObj = $observer->getOrder()->getShippingAddress();
        $billingAddObj = $observer->getOrder()->getBillingAddress();
        if ($shippingMethod == 'warehouse_warehouse') {
            $shippingAddObj->setFirstname($orderObj->getCustomerFirstname().' '.$orderObj->getCustomerLastname());
            $shippingAddObj->setLastname('myGermany');
            $shippingAddObj->setCountryId('DE');
            $shippingAddObj->setRegionId('82');
            //$shippingAddObj->setRegion('Berlin');
            $shippingAddObj->setPostcode('994272');
            $shippingAddObj->setCity('Weimar');
            $shippingAddObj->setStreet('Nordstr. 5');
            $shippingAddObj->save();
            $addressArray = $shippingAddObj->debug();
        }
        $customer = $this->_customer->load($this->_customerSession->getCustomer()->getId());
        if ( ! $customer->getDefaultShippingAddress() || (!$customer->getDefaultBillingAddress())) {
            foreach ($customer->getAddresses() as $address) {
                if (!$customer->getDefaultShippingAddress()) {
                    $address->setIsDefaultShipping('1');
                }
                if(!$customer->getDefaultBillingAddress()){
                    $address->setIsDefaultBilling('1');
                }
                continue; // we only want to set first address of the customer as default shipping address
            }
        }
        $customer->save();


        return $this;
    }    
}
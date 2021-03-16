<?php

namespace Mangoit\Dhlshipment\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    protected $_objectManager;
    protected $_webkulSalesList;
    protected $scopeConfig;
    protected $customerSession;

    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    )
    {
        $this->customerSession = $customerSession;
        $this->_objectManager = $objectmanager;
        $this->scopeConfig = $scopeConfig;

    }

    public function getDhlFees() 
    {
        $dhl_fee = $this->scopeConfig->getValue('dhl_setting/general/dhl_fee', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // $display_text2 = $this->scopeConfig->getValue('dhl_setting/general/display_text2', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // $display_text3 = $this->scopeConfig->getValue('dhl_setting/general/display_text3', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // $display_text4 = $this->scopeConfig->getValue('dhl_setting/general/display_text4', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);
        // $display_text5 = $this->scopeConfig->getValue('dhl_setting/general/display_text5', \Magento\Store\Model\ScopeInterface::SCOPE_STORE);

        return $dhl_fee;
        
    }

    public function isDhlFeesApplied($order_id)
    {
        $salelistModel = $this->_objectManager->create('Webkul\Marketplace\Model\Saleslist');
        $collection = $salelistModel->getCollection()->addFieldToFilter(
            'seller_id', $this->getSellerId()
        )->addFieldToFilter(
            'order_id', $order_id
        );
        foreach ($collection as $item) 
        {
            if($item['dhl_fees'] > 0)
            {
                $data = array('result'=> true, 'dhl_fees'=> $item['dhl_fees']);
            }
        }

        if (!isset($data['result']))
        {
            $data = array('result'=> false);
        }

        return $data;

    }

    public function getSellerId()
    {
        return $this->customerSession->getCustomerId();
    }

}
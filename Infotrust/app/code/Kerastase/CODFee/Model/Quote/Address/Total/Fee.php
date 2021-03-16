<?php

namespace Kerastase\CODFee\Model\Quote\Address\Total;

use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Quote\Api\Data\ShippingAssignmentInterface;
use Magento\Quote\Model\Quote\Address\Total;
use Magento\Quote\Model\Quote\Address;

class Fee extends \Magento\Quote\Model\Quote\Address\Total\AbstractTotal
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var PriceCurrencyInterface
     */
    protected $priceCurrency;

    protected $codHelper;
    
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        PriceCurrencyInterface $priceCurrency,
        \Kerastase\CODFee\Helper\Data $codHelper
        
    ) {
        $this->priceCurrency    = $priceCurrency;
        $this->storeManager     = $storeManager;
        $this->codHelper     = $codHelper;
        $this->setCode(\Kerastase\CODFee\Helper\Data::FEE_TOTAL_CODE);
    }

    
    public function collect(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Api\Data\ShippingAssignmentInterface $shippingAssignment,
        \Magento\Quote\Model\Quote\Address\Total $total
    ) {
        $this->codHelper->log(__METHOD__, true);

        $quote->setCodFee(0);
        $quote->setBaseCodFee(0);
        $total->setCodFee(0);
        $total->setBaseCodFee(0);

        if ($shippingAssignment->getShipping()->getAddress()->getAddressType() == Address::TYPE_SHIPPING
            && $quote->isVirtual()
        ) {
            return $this;
        }

        if (!$this->codHelper->canApplyCodFee($quote)) {
            $this->codHelper->log('collect() SKIPPED', true);
            return $this;
        }

        ###parent::collect($quote, $shippingAssignment, $total);
        $quoteStoreId = $quote->getStoreId(); 
        $baseCodFee = $this->codHelper->getCodFee($quote->getStoreId());
        $codFee     = $this->priceCurrency->convert($baseCodFee,$quoteStoreId);

        $total->addTotalAmount($this->getCode(), $codFee);
        $total->addBaseTotalAmount($this->getCode(), $baseCodFee);

        $total->setCodFee($codFee);
        $total->setBaseCodFee($baseCodFee);

        // set data on quote level?
        $quote->setCodFee($codFee);
        $quote->setBaseCodFee($baseCodFee);

        $total->setGrandTotal($total->getGrandTotal() + $codFee);
        $total->setBaseGrandTotal($total->getBaseGrandTotal() + $baseCodFee);

        $this->codHelper->log('$baseCodFee::' . $baseCodFee);
        $this->codHelper->log('$codFee::' . $codFee);
        $this->codHelper->log('setGrandTotal::' . $total->getGrandTotal());
        $this->codHelper->log('setBaseGrandTotal::' . $total->getBaseGrandTotal());
        $this->codHelper->log('setCodFee::' . $total->getCodFee());

        return $this;
    }

    /**
     * Return shopping cart total row items
     *
     * @param \Magento\Quote\Model\Quote $quote
     * @param Total $total
     * @return array|null
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function fetch(
        \Magento\Quote\Model\Quote $quote, 
        \Magento\Quote\Model\Quote\Address\Total $total    
    ) {
        $this->codHelper->log(__METHOD__, true);
        $result = null;
        if ($this->codHelper->isEnabled() && $quote->getCodFee()) {
            $result = [
                'code' => $this->getCode(),
                'title' => __('Cash on Delivery fee'),
                'value' => $quote->getCodFee()
            ];
        }
        
        $this->codHelper->log('$quote>codFee::' . $quote->getCodFee());
        $this->codHelper->log('$total>codFee::' . $total->getCodFee());
        return $result;
    }
}

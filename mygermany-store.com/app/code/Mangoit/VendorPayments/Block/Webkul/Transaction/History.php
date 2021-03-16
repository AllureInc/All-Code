<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Marketplace
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Mangoit\VendorPayments\Block\Webkul\Transaction;

use Webkul\Marketplace\Model\ResourceModel\Sellertransaction\CollectionFactory;
use Webkul\Marketplace\Model\ResourceModel\Saleperpartner\CollectionFactory as SalePerPartnerCollectionFactory;


class History extends \Webkul\Marketplace\Block\Transaction\History
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyModel;
    protected $salePerPartnerCollectionFactory;
    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param CollectionFactory                                $transactionCollectionFactory
     * @param \Magento\Sales\Model\Order                       $order
      * @param SalePerPartnerCollectionFactory $salePerPartnerCollectionFactory
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
       
        \Magento\Customer\Model\Session $customerSession,
        CollectionFactory $transactionCollectionFactory,
        \Magento\Sales\Model\Order $order,
        \Magento\Directory\Model\Currency $currencyModel,
         SalePerPartnerCollectionFactory $salePerPartnerCollectionFactory,
        array $data = []
    ) {
        $this->currencyModel = $currencyModel;
        $this->salePerPartnerCollectionFactory = $salePerPartnerCollectionFactory;
        parent::__construct(
                $context,
    
                $customerSession,
                $transactionCollectionFactory,
                $order,
                $data
            );
    }

    public function getFormatedPrice($price = 0)
    {
        $currencyCode = 'EUR';
        $currencySymbol = $this->currencyModel->load($currencyCode)->getCurrencySymbol();
        $precision = 2;   // for displaying price decimals 2 point
        //get formatted price by currency
        $formattedPrice = $this->currencyModel->format($price, ['symbol' => $currencySymbol, 'precision'=> $precision], false, false);
        return $formattedPrice;
    }
}

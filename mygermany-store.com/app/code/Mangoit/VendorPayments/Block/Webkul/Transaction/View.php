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

use Webkul\Marketplace\Model\Sellertransaction;
use Webkul\Marketplace\Helper\Data as HelperData;
use Webkul\Marketplace\Model\ResourceModel\Saleslist\CollectionFactory as SaleslistColl;
use Webkul\Marketplace\Model\ResourceModel\Orders\CollectionFactory as OrdersColl;

class View extends \Webkul\Marketplace\Block\Transaction\View
{
    /**
     * @var \Magento\Directory\Model\Currency
     */
    protected $currencyModel;

    /**
     * @var \Mangoit\VendorPayments\Helper\Data
     */
    protected $mangoHelper;

    /**
     * @param \Magento\Customer\Model\Session                  $customerSession
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Sales\Model\Order                       $order
     * @param Sellertransaction                                $sellertransaction
     * @param HelperData                                       $helper
     * @param SaleslistColl                                    $saleslistCollection
     * @param OrdersColl                                       $ordersCollection
     * @param array                                            $data
     */
    public function __construct(
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Sales\Model\Order $order,
        Sellertransaction $sellertransaction,
        HelperData $helper,
        SaleslistColl $saleslistCollection,
        OrdersColl $ordersCollection,
        \Magento\Directory\Model\Currency $currencyModel,
        \Mangoit\VendorPayments\Helper\Data $mangoHelper,
        array $data = []
    ) {
        $this->currencyModel = $currencyModel;
        $this->mangoHelper = $mangoHelper;
        parent::__construct(
                $customerSession,
                $context,
                $order,
                $sellertransaction,
                $helper,
                $saleslistCollection,
                $ordersCollection,
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
    
    /**
     * @return string
     */
    public function getHelper()
    {
        return $this->mangoHelper;
    }
}

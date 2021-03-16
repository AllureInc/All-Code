<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Block\Adminhtml;

use Webkul\Walletsystem\Model\WallettransactionFactory;
use Magento\Sales\Model\Order;

class Transaction extends \Magento\Backend\Block\Template
{
    /**
     * @var Order
     */
    private $_order;
    /**
     * @var [WallettransactionFactory]
     */
    private $walletTransaction;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $customerFactory;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $priceCurrency;
    
    /**
     *
     * @param \Magento\Backend\Block\Template\Context           $context
     * @param Order                                             $order
     * @param WallettransactionFactory                          $wallettransactionFactory
     * @param \Magento\Customer\Model\CustomerFactory           $customerFactory
     * @param \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency
     * @param array                                             $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        Order $order,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        $this->_order = $order;
        $this->walletTransaction = $wallettransactionFactory;
        $this->customerFactory = $customerFactory;
        $this->priceCurrency = $priceCurrency;
        parent::__construct($context, $data);
    }

    public function getOrder()
    {
        return $this->_order;
    }
    public function getTransactionData()
    {
        $id = $this->getRequest()->getParam('entity_id');
        return $this->walletTransaction->create()->load($id);
    }
    public function getCustomerDataById($customerId)
    {
        return $this->customerFactory->create()->load($customerId);
    }
    public function getTransactionAmount($transactionData)
    {
        $amount = $transactionData->getCurrAmount();
        $currencyCode = $transactionData->getCurrencyCode();
        $precision = 2;
        return $this->priceCurrency->format(
            $amount,
            $includeContainer = true,
            $precision,
            $scope = null,
            $currencyCode
        );
    }
    public function getFormattedDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }
}

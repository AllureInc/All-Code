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
namespace Webkul\Walletsystem\Block;

use Webkul\Walletsystem\Model\ResourceModel\Wallettransaction;
use Webkul\Walletsystem\Model\WallettransactionFactory;
use Webkul\Walletsystem\Model\ResourceModel\Walletrecord;
use Magento\Sales\Model\Order;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollection;

class Index extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Wallettransaction
     */
    private $_wallettransactionModel;
    /**
     * @var _transactioncollection
     */
    private $_transactioncollection;
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Walletrecord
     */
    private $_walletrecordModel;
    /**
     * @var Order
     */
    private $_order;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    private $_walletHelper;
    /**
     * @var Magento\Framework\Pricing\Helper\Data
     */
    private $_pricingHelper;
    /**
     * @var Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    private $_customerCollection;
    /**
     * @var Webkul\Walletsystem\Model\WallettransactionFactory
     */
    private $_walletTransaction;
    /**
     * @var \Magento\Customer\Model\CustomerFactory
     */
    private $_customerFactory;
    /**
     * @var \Magento\Framework\Pricing\PriceCurrencyInterface
     */
    private $_priceCurrency;

    /**
     * @param MagentoFrameworkViewElementTemplateContext    $context
     * @param WalletrecordCollectionFactory                 $walletrecordModel
     * @param WallettransactionCollectionFactory            $wallettransactionModel
     * @param Order                                         $order
     * @param WebkulWalletsystemHelperData                  $walletHelper
     * @param MagentoFrameworkPricingHelperData             $pricingHelper
     * @param CustomerCollection                            $customerCollection
     * @param WallettransactionFactory                      $wallettransactionFactory
     * @param MagentoCustomerModelCustomerFactory           $customerFactory
     * @param MagentoFrameworkPricingPriceCurrencyInterface $priceCurrency
     * @param [type]                                        $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        Walletrecord\CollectionFactory $walletrecordModel,
        Wallettransaction\CollectionFactory $wallettransactionModel,
        Order $order,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        CustomerCollection $customerCollection,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_walletrecordModel = $walletrecordModel;
        $this->_wallettransactionModel = $wallettransactionModel;
        $this->_order = $order;
        $this->_walletHelper = $walletHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_customerCollection = $customerCollection;
        $this->_walletTransaction = $wallettransactionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_priceCurrency = $priceCurrency;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getwalletTransactionCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'walletsystem.wallettransaction.pager'
            )
            ->setCollection(
                $this->getwalletTransactionCollection()
            );
            $this->setChild('pager', $pager);
            $this->getwalletTransactionCollection()->load();
        }

        return $this;
    }
    /**
     * @return string
     */
    public function getPagerHtml()
    {
        return $this->getChildHtml('pager');
    }

    // get remaining total of a customer
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecordCollection = $this->_walletrecordModel->create()
            ->addFieldToFilter('customer_id', ['eq' => $customerId]);
        if ($walletRecordCollection->getSize()) {
            foreach ($walletRecordCollection as $record) {
                $remainingAmount = $record->getRemainingAmount();
            }
        }
        return $this->_pricingHelper
            ->currency($remainingAmount, true, false);
    }

    // get transaction collection of a customer
    public function getwalletTransactionCollection()
    {
        if (!$this->_transactioncollection) {
            $customerId = $this->_walletHelper
                ->getCustomerId();
            $walletRecordCollection = $this->_wallettransactionModel->create()
                ->addFieldToFilter('customer_id', $customerId)
                ->setOrder('transaction_at', 'DESC');
            $this->_transactioncollection = $walletRecordCollection;
        }

        return $this->_transactioncollection;
    }
    // get order
    public function getOrder()
    {
        return $this->_order;
    }
    // get all customer collection in which logged in customer in not included
    public function getCustomerCollection()
    {
        $customerCollection = $this->_customerCollection->create()
            ->addFieldToFilter('entity_id', ['neq' => $this->_walletHelper->getCustomerId()]);
        return $customerCollection;
    }
    // load transaction with transaction id
    public function getTransactionData()
    {
        $id = $this->getRequest()->getParams();
        return $this->_walletTransaction->create()->load($id);
    }
    // load customer model by customer id
    public function getCustomerDataById($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }
    // get formatted transaction amount
    public function getTransactionAmount($transactionData)
    {
        $amount = $transactionData->getCurrAmount();
        $currencyCode = $transactionData->getCurrencyCode();
        $precision = 2;
        return $this->_priceCurrency->format(
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

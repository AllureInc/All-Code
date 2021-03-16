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
use Webkul\Walletsystem\Model\WalletPayeeFactory;

class Transfer extends \Magento\Framework\View\Element\Template
{
    /**
     * @var Webkul\Walletsystem\Model\ResourceModel\Wallettransaction
     */
    private $_wallettransactionModel;
    /**
     * @var _payeeCollection
     */
    private $_payeeCollection;
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

    private $walletRecordData = null;

    protected $walletPayee;

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
        \Webkul\Walletsystem\Model\AccountDetails $accountDetails,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        \Magento\Framework\Pricing\Helper\Data $pricingHelper,
        CustomerCollection $customerCollection,
        WallettransactionFactory $wallettransactionFactory,
        \Magento\Customer\Model\CustomerFactory $customerFactory,
        \Magento\Framework\Pricing\PriceCurrencyInterface $priceCurrency,
        WalletPayeeFactory $walletPayee,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->_walletrecordModel = $walletrecordModel;
        $this->_wallettransactionModel = $wallettransactionModel;
        $this->_order = $order;
        $this->_accountDetails = $accountDetails;
        $this->_walletHelper = $walletHelper;
        $this->_pricingHelper = $pricingHelper;
        $this->_customerCollection = $customerCollection;
        $this->_walletTransaction = $wallettransactionFactory;
        $this->_customerFactory = $customerFactory;
        $this->_priceCurrency = $priceCurrency;
        $this->walletPayee = $walletPayee;
    }

    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getWalletPayeeCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'walletsystem.walletpayee.pager'
            )
            ->setCollection(
                $this->getWalletPayeeCollection()
            );
            $this->setChild('pager', $pager);
            $this->getWalletPayeeCollection()->load();
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
    public function getWalletRecordData($customerId)
    {
        if ($this->walletRecordData==null) {
            $walletRecordCollection = $this->_walletrecordModel->create()
                ->addFieldToFilter('customer_id', ['eq' => $customerId]);
            if ($walletRecordCollection->getSize()) {
                foreach ($walletRecordCollection as $record) {
                    $this->walletRecordData = $record;
                    break;
                }
            }
        }
        return $this->walletRecordData;
    }
    // get remaining total of a customer
    public function getWalletRemainingTotal($customerId)
    {
        $remainingAmount = 0;
        $walletRecord = $this->getWalletRecordData($customerId);
        if ($walletRecord && $walletRecord->getEntityId()) {
            $remainingAmount = $walletRecord->getRemainingAmount();
            return $this->_pricingHelper
                ->currency($remainingAmount, true, false);
        }
        return $this->_pricingHelper
                ->currency(0, true, false);
    }

    // get transaction collection of a customer
    public function getWalletPayeeCollection()
    {
        if (!$this->_payeeCollection) {
            $customerId = $this->_walletHelper
                ->getCustomerId();
            $walletPayeeCollection = $this->walletPayee->create()
                ->getCollection()
                ->addFieldToFilter('customer_id', $customerId);
            $this->_payeeCollection = $walletPayeeCollection;
        }
        return $this->_payeeCollection;
    }
    // get order
    public function getOrder()
    {
        return $this->_order;
    }
    public function getEnabledPayeeCollection()
    {
        $customerId = $this->_walletHelper
            ->getCustomerId();
        $walletPayee = $this->walletPayee->create();
        $walletPayeeCollection = $this->walletPayee->create()
            ->getCollection()
            ->addFieldToFilter('customer_id', $customerId)
            ->addFieldToFilter('status', $walletPayee::PAYEE_STATUS_ENABLE);
        return $walletPayeeCollection;
    }
    // load customer model by customer id
    public function getCustomerDataById($customerId)
    {
        return $this->_customerFactory->create()->load($customerId);
    }
    public function getFormattedDate($date)
    {
        return $this->_localeDate->date(new \DateTime($date));
    }

    public function getUserAccountData()
    {
        $customerId = $this->_walletHelper->getCustomerId();
        $accountDataColection = $this->_accountDetails->getCollection()
                                ->addFieldToFilter('customer_id', $customerId)
                                ->addFieldToFilter('status', ['neq' => 0])
                                ->setOrder('entity_id', 'DSC');
        return $accountDataColection;
    }
}

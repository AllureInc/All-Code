<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Block;

/**
 * Webkul MarketplacePreorder Manage Configuration Block
 */
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Webkul\MarketplacePreorder\Model\ResourceModel\PreorderItems\CollectionFactory as PreorderItemsCollection;

/**
 *
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.CyclomaticComplexity)
 * @SuppressWarnings(PHPMD.NPathComplexity)
 */
class ManagePreorders extends \Magento\Framework\View\Element\Template
{
    /**
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customer;

    /**
     * @var \Webkul\Marketplace\Helper\Data
     */
    public $marketplaceHelper;

    /**
     * @var \Magento\Customer\Model\Customer
     */
    protected $_customerSession;

    /**
     * @param Context $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param Customer $customer
     * @param array $data
    */
    /**
    * @param \Webkul\Marketplace\Helper\Data                  $marketplaceHelper
    *
    **/
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\ObjectManagerInterface $objectManager,
        \Webkul\Marketplace\Helper\Data $marketplaceHelper,
        PreorderItemsCollection $preorderItemsCollectionFactory,
        Customer $customer,
        Session $customerSession,
        array $data = []
    ) {
        $this->_objectManager = $objectManager;
        $this->_customer = $customer;
        $this->_customerSession = $customerSession;
        $this->marketplaceHelper = $marketplaceHelper;
        $this->_itemsCollectionFactory = $preorderItemsCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getCustomerId()
    {
        return $this->marketplaceHelper->getCustomerId();
    }

    public function getPreordersCollection()
    {
        if (!($customerId = $this->getCustomerId())) {
            return false;
        }
        $paramData = $this->getRequest()->getParams();
        $filter = '';
        $filterStatus = '';
        $filterDateFrom = '';
        $filterDateTo = '';
        $from = null;
        $to = null;

        if (isset($paramData['order_id'])) {
            $filter = $paramData['order_id'] != '' ? $paramData['order_id'] : '';
        }
        if (isset($paramData['status'])) {
            $filterStatus = $paramData['status'] != '' ? $paramData['status'] : '';
        }
        if (isset($paramData['from_date'])) {
            $filterDateFrom = $paramData['from_date'] != '' ? $paramData['from_date'] : '';
        }
        if (isset($paramData['to_date'])) {
            $filterDateTo = $paramData['to_date'] != '' ? $paramData['to_date'] : '';
        }
        if ($filterDateTo) {
            $todate = date_create($filterDateTo);
            $to = date_format($todate, 'Y-m-d 23:59:59');
        }
        if (!$to) {
            $to = date('Y-m-d 23:59:59');
        }
        if ($filterDateFrom) {
            $fromdate = date_create($filterDateFrom);
            $from = date_format($fromdate, 'Y-m-d H:i:s');
        }
        $collection = $this->_itemsCollectionFactory->create();

        $collection->addFieldToFilter('seller_id', ['eq'=>$customerId]);
        if ($from && $to) {
            $collection->getSelect()->where(
                "main_table.time BETWEEN '".$from."' AND '".$to."'"
            );
        }
        if ($filterStatus) {
            $collection->getSelect()->where(
                'main_table.notify = '.$filterStatus
            );
        }
        if ($filter!=="") {
            $orderCollection = $this->_objectManager->create(
                'Magento\Sales\Model\Order'
            )->getCollection()
            ->addFieldToFilter(
                'increment_id',
                ['like'=>'%'.$filter.'%']
            )->addFieldToSelect('entity_id');

            $orderIds = $orderCollection->getColumnValues('entity_id');

            $collection->addFieldToFilter('order_id', ['in' => $orderIds]);
        }
        $collection->setOrder('id','DESC');
        return $collection;
    }
    /**
     * @return $this
     */
    protected function _prepareLayout()
    {

        parent::_prepareLayout();
        if ($this->getPreordersCollection()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'mppreorder.orders.list.pager'
            )->setCollection(
                $this->getPreordersCollection()
            );
            $this->setChild('pager', $pager);
            $this->getPreordersCollection()->load();
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

    /**
     * Product
     * @param  int $id
     * @return \Magento\Catalog\Model\Product
     */
    public function getProductData($id = '')
    {
        return $this->_objectManager->create(
            'Magento\Catalog\Model\Product'
        )->load($id);
    }

    /**
     * Order Increment ID
     * @param  int $orderId
     * @return string
     */
    public function getIncrementId($orderId)
    {
        return $this->getOrder($orderId)->getIncrementId();
    }

    /**
     * Order
     * @param  int $orderId
     * @return \Magento\Sales\Model\Order
     */
    public function getOrder($orderId)
    {
        return $this->_objectManager->create(
            'Magento\Sales\Model\Order'
        )->load($orderId);
    }
}

<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Block\Order;

use Magento\Catalog\Api\ProductRepositoryInterface;

class SyncList extends \Magento\Framework\View\Element\Template
{
    /**
     * Webkul\MpAmazonConnector\Model\ResourceModel\Productmap\CollectionFactory
     * @var $_mapedProcollection
     */
    protected $mappedProduct;

    /**
     * @param Context                                   $context
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param \Magento\Customer\Model\Session           $customerSession
     * @param CollectionFactory                         $productCollectionFactory
     * @param PriceCurrencyInterface                    $priceCurrency
     * @param array                                     $data
     */
    public function __construct(
        \Magento\Catalog\Block\Product\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Webkul\MpAmazonConnector\Api\OrderMapRepositoryInterface $mappedOrderRepository,
        \Webkul\MpAmazonConnector\Helper\Data $helper,
        \Magento\Sales\Api\Data\OrderInterface $order,
        array $data = []
    ) {
        $this->mappedOrderRepository = $mappedOrderRepository;
        $this->customerSession = $customerSession;
        $this->helper = $helper;
        $this->order = $order;
        parent::__construct($context, $data);
    }

    /**
     * getMappedProducts
     * @return array
     */
    public function getOrderList()
    {
        if (!($sellerId = $this->customerSession->getCustomerId())) {
            return false;
        }
        if (!$this->mappedProduct) {
            $this->mappedProduct = $this->mappedOrderRepository
                                    ->getByAccountId($sellerId)
                                    ->setOrder('entity_id', 'desc');
            $filter = $this->getRequest()->getParams();
            if (isset($filter['s']) && $filter['s']) {
                $this->mappedProduct->addFieldToFilter('mage_order_id', ['like' => '%'.$filter['s'].'%']);
            }
        }
        return $this->mappedProduct;
    }

    /**
     * getImportUrl
     * @return string
     */
    public function getImportUrl()
    {
        return $this->getUrl('mpamazonconnect/order/import', ['_secure' => $this->getRequest()->isSecure()]);
    }
    /**
     * getProfilerUrl
     * @return string
     */
    public function getProfilerUrl()
    {
        return $this->getUrl('mpamazonconnect/order/profiler', ['_secure' => $this->getRequest()->isSecure()]);
    }

    /**
     * @return $this
     */
    protected function _prepareLayout()
    {
        parent::_prepareLayout();
        if ($this->getOrderList()) {
            $pager = $this->getLayout()->createBlock(
                'Magento\Theme\Block\Html\Pager',
                'ebayconnect.order.list.pager'
            )->setCollection(
                $this->getOrderList()
            );
            $this->setChild('pager', $pager);
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
     * get order id by increment id
     *
     * @param int $orderIncrementId
     * @return int
     */
    public function getOrderId($orderIncrementId)
    {
        return $this->order->loadByIncrementId($orderIncrementId)->getId();
    }

    /**
     * getDeleteProUrl
     * @param int proMapId
     * @return string
     */
    public function getDeleteProMapUrl($proMapId)
    {
        return $this->getUrl(
            'mpamazonconnect/product/delete',
            ['id' => $proMapId, '_secure' => $this->getRequest()->isSecure()]
        );
    }

    /**
     * get order view url of marketplace
     *
     * @param int $orderIncrementId
     * @return string
     */
    public function getOrderViewUrl($orderIncrementId)
    {
        $orderId = $this->getOrderId($orderIncrementId);
        return $this->getUrl(
            'marketplace/order/view',
            ['id' => $orderId, '_secure' => $this->getRequest()->isSecure()]
        );
    }
}

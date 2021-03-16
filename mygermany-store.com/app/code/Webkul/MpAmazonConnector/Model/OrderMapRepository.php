<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model;

use Webkul\MpAmazonConnector\Api\Data\OrderMapInterface;
use Webkul\MpAmazonConnector\Model\ResourceModel\OrderMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderMapRepository implements \Webkul\MpAmazonConnector\Api\OrderMapRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpAmazonConnector\Model\ResourceModel\OrderMap
     */
    protected $_resourceModel;

    public function __construct(
        OrderMapFactory $orderMapFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\OrderMap\CollectionFactory $collectionFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\OrderMap $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_orderMapFactory = $orderMapFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by amazon order id
     * @param  object $amzOrderId
     * @return object
     */
    public function getByAmzOrderId($amzOrderId)
    {
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('amazon_order_id', $amzOrderId);
        return $orderMapCollection;
    }

    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getByAccountId($sellerId)
    {
        $orderMapCollection = false;
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('seller_id', $sellerId);
        return $orderMapCollection;
    }

    /**
     * get collection by order ids
     * @param  array  $ids
     * @return object
     */
    public function getByIds(array $ids)
    {
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter(
                                'entity_id',
                                ['in' => $ids]
                            );
        return $orderMapCollection;
    }

    /**
     * get collection by magento order id
     * @param  int $magentoOrderId
     * @return object
     */
    public function getByMagentoOrderId($magentoOrderId)
    {
        $orderMapCollection = false;
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('mage_order_id', $magentoOrderId);
        return $orderMapCollection;
    }
}

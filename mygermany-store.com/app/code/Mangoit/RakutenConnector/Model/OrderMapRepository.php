<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\OrderMapInterface;
use Mangoit\RakutenConnector\Model\ResourceModel\OrderMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class OrderMapRepository implements \Mangoit\RakutenConnector\Api\OrderMapRepositoryInterface
{
    /**
     * resource model
     * @var \Mangoit\RakutenConnector\Model\ResourceModel\OrderMap
     */
    protected $_resourceModel;

    public function __construct(
        OrderMapFactory $orderMapFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\OrderMap\CollectionFactory $collectionFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\OrderMap $resourceModel
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
    public function getByRktnOrderId($amzOrderId)
    {
        $orderMapCollection = $this->_collectionFactory->create()
                            ->addFieldToFilter('rakuten_order_id', $amzOrderId);
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

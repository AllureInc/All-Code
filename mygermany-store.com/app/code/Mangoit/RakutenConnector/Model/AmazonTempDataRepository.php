<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\AmazonTempDataInterface;
use Mangoit\RakutenConnector\Model\ResourceModel\AmazonTempData\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AmazonTempDataRepository implements \Mangoit\RakutenConnector\Api\AmazonTempDataRepositoryInterface
{
    /**
     * resource model
     * @var \Mangoit\RakutenConnector\Model\ResourceModel\Accounts
     */
    protected $_resourceModel;

    public function __construct(
        AmazonTempDataFactory $rakutenTempDataFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\AmazonTempData\CollectionFactory $collectionFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\AmazonTempData $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
        $this->rakutenTempDataFactory = $rakutenTempDataFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * get collection by item type and item id
     * @param  string $itemType
     * @param  string $itemId
     * @return object
     */
    public function getByItemId($itemType, $itemId)
    {
        $tempCollection = $this->collectionFactory
                        ->create()
                        ->addFieldToFilter(
                            'item_type',
                            'product'
                        )->addFieldToFilter(
                            'item_id',
                            $itemId
                        );
        return $tempCollection;
    }

    /**
     * get collection by item type and account id
     * @param  string $itemType
     * @param  int $accountId
     * @return object
     */
    public function getByAccountIdnItemType(
        $itemType,
        $sellerId,
        $complete = false
    ) {
        if ($complete) {
            $tempCollection = $this->rakutenTempDataFactory
                ->create()->getCollection()
                ->addFieldToFilter(
                    'item_type',
                    $itemType
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
        } else {
            $tempCollection = $this->rakutenTempDataFactory
                ->create()->getCollection()
                ->addFieldToFilter(
                    'item_type',
                    $itemType
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                )->setPageSize(1);
        }
        
        return $tempCollection;
    }

    /**
     * get collection by item id and item type
     * @param  int $itemId
     * @param  string $itemType
     * @return object
     */
    public function getByItemIdnItemType($itemId, $itemType)
    {
        $tempCollection = $this->collectionFactory
                ->create()
                ->addFieldToFilter(
                    'item_type',
                    $itemType
                )->addFieldToFilter(
                    'item_id',
                    $itemId
                );
        return $tempCollection;
    }
}

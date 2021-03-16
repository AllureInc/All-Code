<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model;

use Webkul\MpAmazonConnector\Api\Data\AmazonTempDataInterface;
use Webkul\MpAmazonConnector\Model\ResourceModel\AmazonTempData\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AmazonTempDataRepository implements \Webkul\MpAmazonConnector\Api\AmazonTempDataRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpAmazonConnector\Model\ResourceModel\Accounts
     */
    protected $_resourceModel;

    public function __construct(
        AmazonTempDataFactory $amazonTempDataFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\AmazonTempData\CollectionFactory $collectionFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\AmazonTempData $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
        $this->amazonTempDataFactory = $amazonTempDataFactory;
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
            $tempCollection = $this->amazonTempDataFactory
                ->create()->getCollection()
                ->addFieldToFilter(
                    'item_type',
                    $itemType
                )->addFieldToFilter(
                    'seller_id',
                    $sellerId
                );
        } else {
            $tempCollection = $this->amazonTempDataFactory
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

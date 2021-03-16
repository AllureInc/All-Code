<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\ProductMapInterface;
use Mangoit\RakutenConnector\Model\ResourceModel\ProductMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductMapRepository implements \Mangoit\RakutenConnector\Api\ProductMapRepositoryInterface
{
    /**
     * resource model
     * @var \Mangoit\RakutenConnector\Model\ResourceModel\OrderMap
     */
    protected $_resourceModel;

    public function __construct(
        ProductMapFactory $productmapFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\ProductMap\CollectionFactory $collectionFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\ProductMap $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_productmapFactory = $productmapFactory;
        $this->_collectionFactory = $collectionFactory;
    }

    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getByAccountId($sellerId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'seller_id',
                                $sellerId
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by entity ids
     * @param  array $ids
     * @return object
     */
    public function getByIds($ids, $sellerId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'entity_id',
                                [
                                'in' => $ids
                                ]
                            )->addFieldToFilter(
                                'seller_id',
                                [
                                'eq' => $sellerId
                                ]
                            );
        return $mappedProductCollection;
    }

        /**
         * get  by rakuten product id
         * @param  string $rktProductId
         * @return object
         */
    public function getByMageProIds($mageProIds = [], $sellerId)
    {
        $mappedProductCollection = $this->_collectionFactory
            ->create()
            ->addFieldToFilter(
                'magento_pro_id',
                [
                'in' => $mageProIds
                ]
            )->addFieldToFilter(
                'seller_id',
                [
                'eq' => $sellerId
                ]
            );
        return $mappedProductCollection;
    }
    /**
     * get collection by rakuten product id
     * @param  string $rktProductId
     * @return object
     */
    public function getByRktProductId($rktProductId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'rakuten_pro_id',
                                [
                                'eq' => $rktProductId
                                ]
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by submission id
     * @param  string $submissionId
     * @return object
     */
    public function getBySubmissionId($submissionId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'feedsubmission_id',
                                [
                                'eq' => $submissionId
                                ]
                            );
        return $mappedProductCollection;
    }

    /**
     * get collection by product Sku
     * @param  string $submissionId
     * @return object
     */
    public function getBySku($sku)
    {
        $mappedProductCollection = $this->_collectionFactory
                                ->create()
                                ->addFieldToFilter(
                                    'product_sku',
                                    [
                                    'eq' => $sku
                                    ]
                                );
        return $mappedProductCollection;
    }
}

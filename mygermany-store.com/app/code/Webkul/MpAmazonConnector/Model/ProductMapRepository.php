<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model;

use Webkul\MpAmazonConnector\Api\Data\ProductMapInterface;
use Webkul\MpAmazonConnector\Model\ResourceModel\ProductMap\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class ProductMapRepository implements \Webkul\MpAmazonConnector\Api\ProductMapRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpAmazonConnector\Model\ResourceModel\OrderMap
     */
    protected $_resourceModel;

    public function __construct(
        ProductMapFactory $productmapFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\ProductMap\CollectionFactory $collectionFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\ProductMap $resourceModel
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
         * get  by amaz product id
         * @param  string $amzProductId
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
     * get collection by amaz product id
     * @param  string $amzProductId
     * @return object
     */
    public function getByAmzProductId($amzProductId)
    {
        $mappedProductCollection = $this->_collectionFactory
                            ->create()
                            ->addFieldToFilter(
                                'amazon_pro_id',
                                [
                                'eq' => $amzProductId
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

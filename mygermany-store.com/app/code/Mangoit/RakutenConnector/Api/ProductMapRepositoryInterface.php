<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Api;

/**
 * @api
 */
interface ProductMapRepositoryInterface
{
    /**
     * get  by account id
     * @param  int $accountId
     * @return object
     */
    public function getByAccountId($accountId);

    /**
     * get  by entity ids
     * @param  array $ids
     * @return object
     */
    public function getByIds($ids, $sellerId);

    /**
     * get  by rakuten product id
     * @param  string $rktProductId
     * @return object
     */
    public function getByRktProductId($rktProductId);

    /**
     * get  by submission id
     * @param  string $submissionId
     * @return object
     */
    public function getBySubmissionId($submissionId);

    /**
     * get  by rakuten product id
     * @param  string $rktProductId
     * @return object
     */
    public function getByMageProIds($mageProIds = [], $sellerId);

    /**
     * get collection by product Sku
     * @param  string $submissionId
     * @return object
     */
    public function getBySku($sku);
}

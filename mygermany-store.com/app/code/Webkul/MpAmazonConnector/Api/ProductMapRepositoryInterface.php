<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Api;

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
     * get  by amaz product id
     * @param  string $amzProductId
     * @return object
     */
    public function getByAmzProductId($amzProductId);

    /**
     * get  by submission id
     * @param  string $submissionId
     * @return object
     */
    public function getBySubmissionId($submissionId);

    /**
     * get  by amaz product id
     * @param  string $amzProductId
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

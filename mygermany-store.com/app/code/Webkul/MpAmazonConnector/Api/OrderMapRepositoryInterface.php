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
interface OrderMapRepositoryInterface
{
    /**
     * get collection by account id
     * @param  int $accountId
     * @return object
     */
    public function getByAccountId($accountId);

    /**
     * get collection by amazon order id
     * @param  object $amzOrderId
     * @return object
     */
    public function getByAmzOrderId($amzOrderId);

    /**
     * get collection by order ids
     * @param  array  $ids
     * @return object
     */
    public function getByIds(array $ids);

    /**
     * get collection by magento order id
     * @param  int $magentoOrderId
     * @return object
     */
    public function getByMagentoOrderId($magentoOrderId);
}

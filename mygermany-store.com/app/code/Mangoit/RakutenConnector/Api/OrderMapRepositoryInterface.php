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
    public function getByRktnOrderId($amzOrderId);

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

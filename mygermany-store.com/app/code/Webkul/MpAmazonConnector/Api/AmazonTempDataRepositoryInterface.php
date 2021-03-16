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
interface AmazonTempDataRepositoryInterface
{
    /**
     * get collection by item type and item id
     * @param  string $itemType
     * @param  string $itemId
     * @return object
     */
    public function getByItemId($itemType, $itemId);

    /**
     * get collection by item type and account id
     * @param  string $itemType
     * @param  int $accountId
     * @return object
     */
    public function getByAccountIdnItemType($itemType, $accountId);

    /**
     * get collection by item id and item type
     * @param  int $itemId
     * @param  string $itemType
     * @return object
     */
    public function getByItemIdnItemType($itemId, $itemType);
}

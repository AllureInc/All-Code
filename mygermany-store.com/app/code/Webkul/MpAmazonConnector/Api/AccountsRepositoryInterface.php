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
interface AccountsRepositoryInterface
{
    /**
     * get collection by seller id
     * @param  int $accountId
     * @return object
     */
    public function getById($sellerId);
}

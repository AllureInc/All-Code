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
interface AccountsRepositoryInterface
{
    /**
     * get collection by seller id
     * @param  int $accountId
     * @return object
     */
    public function getById($sellerId);
}

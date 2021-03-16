<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model;

use Webkul\MpAmazonConnector\Api\Data\AccountsInterface;
use Webkul\MpAmazonConnector\Model\ResourceModel\Accounts\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AccountsRepository implements \Webkul\MpAmazonConnector\Api\AccountsRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpAmazonConnector\Model\ResourceModel\Accounts
     */
    protected $resourceModel;

    public function __construct(
        AccountsFactory $accountsFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\Accounts\CollectionFactory $collectionFactory,
        \Webkul\MpAmazonConnector\Model\ResourceModel\Accounts $resourceModel
    ) {
        $this->resourceModel = $resourceModel;
        $this->accountsFactory = $accountsFactory;
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * get collection by account id
     * @param  int $sellerId
     * @return object
     */
    public function getById($sellerId)
    {
        $accountDetails = $this->accountsFactory->create()
                            ->getCollection()
                            ->addFieldToFilter('seller_id', ['eq'=>$sellerId]);
        if ($accountDetails->getSize()) {
            foreach ($accountDetails as $credentials) {
                return $credentials;
            }
        } else {
            return $accountDetails;
        }
    }
}

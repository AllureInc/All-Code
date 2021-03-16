<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Model;

use Mangoit\RakutenConnector\Api\Data\AccountsInterface;
use Mangoit\RakutenConnector\Model\ResourceModel\Accounts\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class AccountsRepository implements \Mangoit\RakutenConnector\Api\AccountsRepositoryInterface
{
    /**
     * resource model
     * @var \Mangoit\RakutenConnector\Model\ResourceModel\Accounts
     */
    protected $resourceModel;

    public function __construct(
        AccountsFactory $accountsFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\Accounts\CollectionFactory $collectionFactory,
        \Mangoit\RakutenConnector\Model\ResourceModel\Accounts $resourceModel
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

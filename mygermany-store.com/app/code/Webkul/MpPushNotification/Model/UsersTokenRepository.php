<?php
/**
 * @category   Webkul
 * @package    Webkul_MpPushNotification
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c) Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpPushNotification\Model;

use Webkul\MpPushNotification\Api\Data\UsersTokenInterface;
use Webkul\MpPushNotification\Model\ResourceModel\UsersToken\Collection;

/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class UsersTokenRepository implements \Webkul\MpPushNotification\Api\UsersTokenRepositoryInterface
{
    /**
     * resource model
     * @var \Webkul\MpPushNotification\Model\ResourceModel\Ebayaccounts
     */
    protected $_resourceModel;

    public function __construct(
        UsersTokenFactory $usersTokenFactory,
        \Webkul\MpPushNotification\Model\ResourceModel\UsersToken\CollectionFactory $collectionFactory,
        \Webkul\MpPushNotification\Model\ResourceModel\UsersToken $resourceModel
    ) {
        $this->_resourceModel = $resourceModel;
        $this->_usersTokenFactory = $usersTokenFactory;
        $this->_collectionFactory = $collectionFactory;
    }
    
    /**
     * get by token id
     * @param  string $token
     * @return object
     */
    public function getByToken($token)
    {
        return $this->_collectionFactory->create()->addFieldToFilter('token', $token);
    }

    /**
     * get by ids
     * @param  array  $ids
     * @return UsersTokenInterface
     */
    public function getByIds(array $ids)
    {
        return $this->_collectionFactory
            ->create()
            ->addFieldToFilter('entity_id', ['in'=>$ids]);
    }
}

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
use Magento\Framework\DataObject\IdentityInterface;

class UsersToken extends \Magento\Framework\Model\AbstractModel implements UsersTokenInterface
{
    /**
     * CMS page cache tag.
     */
    const CACHE_TAG = 'mp_pushnotification_users_token';

    /**
     * @var string
     */
    protected $_cacheTag = 'mp_pushnotification_users_token';

    /**
     * Prefix of model events names.
     *
     * @var string
     */
    protected $_eventPrefix = 'mp_pushnotification_users_token';

    /**
     * Initialize resource model.
     */
    protected function _construct()
    {
        $this->_init('Webkul\MpPushNotification\Model\ResourceModel\UsersToken');
    }

    /**
     * get id
     * @return string
     */
    public function getId()
    {
        return $this->getData(self::ID);
    }

    /**
     * set id
     * @param int $id
     */
    public function setId($id)
    {
        return $this->setData(self::ID, $id);
    }

    /**
     * get token
     * @return string
     */
    public function getToken()
    {
        return $this->getData(self::TOKEN);
    }

    /**
     * set token
     * @param string $token
     */
    public function setToken($token)
    {
        return $this->setData(self::TOKEN, $token);
    }

    /**
     * get browser
     * @return string
     */
    public function getBrowser()
    {
        return $this->getData(self::BROWSER);
    }

    /**
     * set browser
     * @param string $browser
     */
    public function setBrowser($browser)
    {
        return $this->setData(self::BROWSER, $browser);
    }

    /**
     * get created time
     * @return timestamp
     */
    public function getcreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * set created time
     * @param timestamp $createdAt
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * get name
     * @return string
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * set name
     * @param name $name
     * @return  UsersTokenInterface
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }
}

<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model;

use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;

use Plenty\Core\Api\Data\AuthInterface;
use Plenty\Core\Helper\Data as Helper;

/**
 * Class Auth
 * @package Plenty\Core\Model
 *
 * @method ResourceModel\Auth getResource()
 * @method ResourceModel\Auth\Collection getCollection()
 */
class Auth extends CoreAbstractModel implements AuthInterface,
    IdentityInterface
{
    const CACHE_TAG                 = 'plenty_core_auth';

    protected $_cacheTag            = 'plenty_core_auth';
    protected $_eventPrefix         = 'plenty_core_auth';
    protected $_collectionFactory   = null;

    /**
     * Resource constructor
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Auth::class);
    }

    /**
     * Auth constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $dateTime, $helper, $logger, $resource, $resourceCollection, $serializer, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    /**
     * @return mixed
     */
    public function getAccessToken()
    {
        return $this->getData(self::ACCESS_TOKEN);
    }

    /**
     * @param $token
     * @return Auth
     */
    public function setAccessToken($token)
    {
        return $this->setData(self::ACCESS_TOKEN, $token);
    }

    /**
     * @return string|null
     */
    public function getRefreshToken()
    {
        return $this->getData(self::REFRESH_TOKEN);
    }

    /**
     * @param $token
     * @return Auth
     */
    public function setRefreshToken($token)
    {
        return $this->setData(self::REFRESH_TOKEN, $token);
    }

    /**
     * @return string|null
     */
    public function getTokenType()
    {
        return $this->getData(self::TOKEN_TYPE);
    }

    /**
     * @param $tokenType
     * @return Auth
     */
    public function setTokenType($tokenType)
    {
        return $this->setData(self::TOKEN_TYPE, $tokenType);
    }

    /**
     * @return mixed
     */
    public function getExpiresIn()
    {
        return $this->getData(self::TOKEN_EXPIRES_IN);
    }

    /**
     * @param $expiresIn
     * @return Auth
     */
    public function setExpiresIn($expiresIn)
    {
        return $this->setData(self::TOKEN_EXPIRES_IN, $expiresIn);
    }

    /**
     * @return mixed
     */
    public function getMessage()
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @param $message
     * @return Auth
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @return mixed
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param $createdAt
     * @return Auth
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return mixed
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param $updatedAt
     * @return Auth
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }
}
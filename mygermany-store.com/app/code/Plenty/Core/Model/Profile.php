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
use Magento\Framework\Exception\LocalizedException;

use Plenty\Core\App\Config\ScopeConfigInterface;
use Plenty\Core\Api\Data\ProfileInterface;
use Plenty\Core\Helper\Data as Helper;

/**
 * Class Profile
 * @package Plenty\Core\Model
 *
 * @method ResourceModel\Auth getResource()
 * @method ResourceModel\Auth\Collection getCollection()
 */
class Profile extends CoreAbstractModel implements ProfileInterface,
    IdentityInterface
{
    const CACHE_TAG                 = 'plenty_core_profile';

    /**#@+
     * Profile's Statuses
     */
    const STATUS_ENABLED            = 1;
    const STATUS_DISABLED           = 0;
    /**#@-*/

    /**
     * @var null
     */
    protected $_config;

    /**
     * @var ScopeConfigInterface|null
     */
    protected $_scopeConfig;

    /**
     * @var null
     */
    protected $_configCache;


    protected $_typeInstance;

    /**
     * Profile type instance as singleton
     */
    protected $_typeInstanceSingleton;

    protected $_cacheTag                = 'plenty_core_profile';
    protected $_eventPrefix             = 'plenty_core_profile';

    /**
     * @var Profile\Type
     */
    protected $_profileEntityType;

    /**
     * Resource Constructor.
     */
    protected function _construct()
    {
        $this->_init(ResourceModel\Profile::class);
    }

    /**
     * Profile constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param ScopeConfigInterface $scopeConfig
     * @param Profile\Type $profileEntityType
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param Json|null $serializer
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry$registry,
        DateTime $dateTime,
        Helper $helper,
        Logger $logger,
        ScopeConfigInterface $scopeConfig,
        Profile\Type $profileEntityType,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null,
        array $data = []
    ) {
        $this->_scopeConfig = $scopeConfig;
        $this->_profileEntityType = $profileEntityType;
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
     * @param $path
     * @param null $store
     * @return mixed
     */
    public function getStoreConfig($path, $store = null)
    {
        return $this->_scopeConfig->getValue($this->getId(), $path, ScopeConfigInterface::SCOPE_STORE, $store);
    }

    /**
     * @return Profile\Type\AbstractType
     * @throws LocalizedException
     */
    public function getTypeInstance()
    {
        if (null === $this->_typeInstance) {
            $this->_typeInstance = $this->_profileEntityType->factory($this);
        }

        return $this->_typeInstance;
    }

    /**
     * @param $instance
     * @return $this
     */
    public function setTypeInstance($instance)
    {
        $this->_typeInstance = $instance;
        return $this;
    }

    /**
     * @return Profile\Type\AbstractType
     * @throws LocalizedException
     */
    public function execute()
    {
        $typeInstance = $this->getTypeInstance();
        return $typeInstance->execute();
    }

    /**
     * @return int
     */
    public function getId()
    {
        return $this->getData(self::ENTITY_ID);
    }

    /**
     * @return mixed
     */
    public function getName()
    {
        return $this->getData(self::NAME);
    }

    /**
     * @param $name
     * @return mixed
     */
    public function setName($name)
    {
        return $this->setData(self::NAME, $name);
    }

    /**
     * @return mixed
     */
    public function getIsActive()
    {
        return $this->getData(self::IS_ACTIVE);
    }

    /**
     * @param $status
     * @return mixed
     */
    public function setIsActive($status)
    {
        return $this->setData(self::IS_ACTIVE, $status);
    }

    /**
     * @return mixed
     */
    public function getAdaptor()
    {
        return $this->getData(self::ADAPTOR);
    }

    /**
     * @param $type
     * @return mixed
     */
    public function setAdaptor($type)
    {
        return $this->setData(self::ADAPTOR, $type);
    }

    /**
     * @return mixed
     */
    public function getStatus()
    {
        return $this->getData(self::STATUS);
    }

    /**
     * @param $status
     * @return mixed
     */
    public function setStatus($status)
    {
        return $this->setData(self::STATUS, $status);
    }

    /**
     * @return mixed
     */
    public function getEntity()
    {
        return $this->getData(self::ENTITY);
    }

    /**
     * @param $entityType
     * @return mixed
     */
    public function setEntity($entityType)
    {
        return $this->setData(self::ENTITY, $entityType);
    }

    /**
     * @return mixed
     */
    public function getCrontab()
    {
        return $this->getData(self::CRONTAB);
    }

    /**
     * @param $crontab
     * @return mixed|Profile
     */
    public function setCrontab($crontab)
    {
        return $this->setData(self::CRONTAB, $crontab);
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
     * @return mixed|Profile
     */
    public function setMessage($message)
    {
        return $this->setData(self::MESSAGE, $message);
    }

    /**
     * @return mixed|string|null
     */
    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @param string $createdAt
     * @return \Plenty\Core\Api\Data\ProfileInterface|Profile
     */
    public function setCreatedAt($createdAt)
    {
        return $this->setData(self::CREATED_AT, $createdAt);
    }

    /**
     * @return mixed|string|null
     */
    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @param string $updatedAt
     * @return \Plenty\Core\Api\Data\ProfileInterface|Profile
     */
    public function setUpdatedAt($updatedAt)
    {
        return $this->setData(self::UPDATED_AT, $updatedAt);
    }

    /**
     * Prepare page's statuses.
     * Available event cms_page_get_available_statuses to customize statuses.
     *
     * @return array
     */
    public function getAvailableStatuses()
    {
        return [self::STATUS_ENABLED => __('Enabled'), self::STATUS_DISABLED => __('Disabled')];
    }
}
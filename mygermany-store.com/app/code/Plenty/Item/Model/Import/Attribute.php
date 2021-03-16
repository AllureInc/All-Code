<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import;

use Magento\Framework\Data\Collection;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\DataObject\IdentityInterface;
use Plenty\Core\Model\Source\Status;
use Plenty\Item\Helper\Data as Helper;
use Plenty\Item\Model\Logger;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Rest\Attribute as AttributeClient;
use Plenty\Core\Model\Profile\Config\Source\Behaviour;
use Plenty\Item\Api\Data\Import\AttributeInterface;
use Plenty\Item\Api\Data\Profile\ProductImportInterface;

/**
 * Class Attribute
 * @package Plenty\Item\Model\Import
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Attribute getResource()
 * @method \Plenty\Item\Model\ResourceModel\Import\Attribute\Collection getCollection()
 *
 * @method ProductImportInterface getProfileEntity()
 * @method ProductImportInterface setProfileEntity(object $value)
 */
class Attribute extends ImportExportAbstract implements AttributeInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_attribute';
    protected $_cacheTag        = 'plenty_item_import_attribute';
    protected $_eventPrefix     = 'plenty_item_import_attribute';

    /**
     * @var array
     */
    private $_collectionResult;

    /**
     *
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Attribute::class);
    }

    /**
     * Attribute constructor.
     * @param Context $context
     * @param Registry $registry
     * @param DateTime $dateTime
     * @param Helper $helper
     * @param Logger $logger
     * @param AttributeClient $restAttributeClient
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
        AttributeClient $restAttributeClient,
        ?AbstractResource $resource = null,
        ?AbstractDb $resourceCollection = null,
        ?Json $serializer = null, array
        $data = []
    ) {
        $this->_api = $restAttributeClient;
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
     * @return int
     */
    public function getProfileId() : int
    {
        return $this->getData(self::PROFILE_ID);
    }

    /**
     * @return string
     */
    public function getProfileAttributeId() : string
    {
        return $this->getData(self::PROFILE_ATTRIBUTE_ID);
    }

    /**
     * @return string
     */
    public function getType() : string
    {
        return $this->getData(self::TYPE);
    }

    /**
     * @return int|null
     */
    public function getPosition() : ?int
    {
        return $this->getData(self::POSITION);
    }

    /**
     * @return int|null
     */
    public function getAttributeId() : ?int
    {
        return $this->getData(self::ATTRIBUTE_ID);
    }

    /**
     * @return int|null
     */
    public function getPropertyId() : ?int
    {
        return $this->getData(self::PROPERTY_ID);
    }

    /**
     * @return int|null
     */
    public function getManufacturerId() : ?int
    {
        return $this->getData(self::MANUFACTURER_ID);
    }

    /**
     * @return string|null
     */
    public function getAttributeCode() : ?string
    {
        return $this->getData(self::ATTRIBUTE_CODE);
    }

    /**
     * @return string|null
     */
    public function getPropertyCode() : ?string
    {
        return $this->getData(self::PROPERTY_CODE);
    }

    /**
     * @return int|null
     */
    public function getPropertyGroupId() : ?int
    {
        return $this->getData(self::PROPERTY_GROUP_ID);
    }

    /**
     * @return string|null
     */
    public function getValueType() : ?string
    {
        return $this->getData(self::VALUE_TYPE);
    }

    /**
     * @return array
     */
    public function getEntries() : array
    {
        if (!$entries = $this->getData(self::ENTRIES)) {
            return [];
        }
        return $this->_serializer->unserialize($entries);
    }

    /**
     * @return array
     */
    public function getNames() : array
    {
        if (!$names = $this->getData(self::NAMES)) {
            return [];
        }
        return $this->_serializer->unserialize($names);
    }

    /**
     * @return string|null
     */
    public function getManufacturerName() : ?string
    {
        return $this->getData(self::NAMES);
    }

    /**
     * @return array
     */
    public function getValues() : array
    {
        if (!$values = $this->getData(self::VALUES)) {
            return [];
        }
        return $this->_serializer->unserialize($values);
    }

    /**
     * @return array
     */
    public function getValueNames() : array
    {
        if (!$valueNames = $this->getData(self::VALUE_NAMES)) {
            return [];
        }
        return $this->_serializer->unserialize($valueNames);
    }

    /**
     * @return array
     */
    public function getSelections() : array
    {
        if (!$selections = $this->getData(self::SELECTIONS)) {
            return [];
        }
        return $this->_serializer->unserialize($selections);
    }

    /**
     * @return array
     */
    public function getMaps() : array
    {
        if (!$maps = $this->getData(self::MAPS)) {
            return [];
        }
        return $this->_serializer->unserialize($maps);
    }

    /**
     * @return array
     */
    public function getGroup() : array
    {
        if (!$group = $this->getData(self::GROUP)) {
            return [];
        }
        return $this->_serializer->unserialize($group);
    }

    /**
     * @return array
     */
    public function getMarketComponents() : array
    {
        if (!$marketComponents = $this->getData(self::MARKET_COMPONENTS)) {
            return [];
        }
        return $this->_serializer->unserialize($marketComponents);
    }

    /**
     * @return string|null
     */
    public function getMessage() : ?string
    {
        return $this->getData(self::MESSAGE);
    }

    /**
     * @return string|null
     */
    public function getCreatedAt() : ?string
    {
        return $this->getData(self::CREATED_AT);
    }

    /**
     * @return string|null
     */
    public function getUpdatedAt() : ?string
    {
        return $this->getData(self::UPDATED_AT);
    }

    /**
     * @return string|null
     */
    public function getCollectedAt() : ?string
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param $attributeCode
     * @return Attribute
     */
    public function getAttributeByCode($attributeCode) : Attribute
    {
        $this->getResource()
            ->load($this, $attributeCode, 'attribute_code');
        return $this;
    }

    /**
     * @param $attributeCode
     * @param null $profileId
     * @return int|null
     */
    public function getAttributeIdByCode($attributeCode, $profileId = null)
    {
        return $this->getCollection()
            // ->addProfileFilter($profileId === null ? $this->getProfileEntity()->getProfile()->getId() : $profileId)
            ->addFieldToFilter('attribute_code', $attributeCode)
            ->getColumnValues('attribute_id');
    }

    /**
     * @param $attributeId
     * @return array
     */
    public function getAttributeNamesById($attributeId)
    {
        $this->getResource()
            ->load($this, $attributeId, 'attribute_id');

        return $this->getNames();
    }

    /**
     * @param $attributeId
     * @return array|mixed
     */
    public function getAttributeValuesById($attributeId) : ?array
    {
        $this->getResource()
            ->load($this, $attributeId, 'attribute_id');

        return $this->getValues();
    }

    /**
     * @param $attributeId
     * @param $attributeValueId
     * @return mixed|null
     */
    public function getAttributeValueNamesById($attributeId, $attributeValueId)
    {
        $this->getResource()
            ->load($this, $attributeId, 'attribute_id');

        return isset($this->getValueNames()[$attributeValueId])
            ? $this->getValueNames()[$attributeValueId]
            : [];
    }

    /**
     * @param $id
     * @return Attribute|\Plenty\Item\Model\ResourceModel\Import\Attribute
     */
    public function getManufacturerById($id)
    {
        $this->getResource()
            ->load($this, (int) $id, 'manufacturer_id');
        return $this;
    }

    /**
     * @param $name
     * @return mixed|\Plenty\Item\Model\ResourceModel\Import\Attribute
     */
    public function getManufacturerByName($name)
    {
        $this->getResource()
            ->load($this, $name, 'names');
        return $this;
    }

    /**
     * @return $this
     * @throws \Exception
     */
    public function collect()
    {
        $this->_response = [];
        $lastUpdatedAt = $this->_getLastUpdatedAt();
        $this->collectAttributes($lastUpdatedAt)
            ->collectProperties(null, $lastUpdatedAt)
            ->collectManufacturers($lastUpdatedAt);

        return $this;
    }


    /**
     * @param null $updatedBetween
     * @return $this
     * @throws \Exception
     */
    public function collectAttributes($updatedBetween = null)
    {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        $this->_collectionResult = [];
        $page = 1;
        $with = 'names,values,maps';

        do {
            $response = $this->_api()->getSearchAttributes($page, $updatedBetween, $with, true);
            if (!$response->getSize()) {
                return $this;
            }

            $this->_saveAttributes($response);

            $result = $response->getColumnValues(AttributeInterface::ATTRIBUTE_CODE);
            $this->_collectionResult = array_merge($this->_collectionResult, $result);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->_response[Status::SUCCESS][] = __('Attributes have been collected. Effected attribute(s): %1',
            implode(', ', $this->_collectionResult));

        return $this;
    }

    /**
     * @param null $id
     * @param null $lastUpdatedAt
     * @return $this
     * @throws \Exception
     */
    public function collectProperties(
        $id = null,
        $lastUpdatedAt = null
    ) {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        $this->_collectionResult = [];
        $page = 1;
        $with = 'names,group,marketComponents,selections';

        do {
            $response = $this->_api()->getSearchProperties($page, $with, $id);
            if (!$response->getSize()) {
                return $this;
            }

            $this->_saveProperties($response);

            $result = $response->getColumnValues(AttributeInterface::PROPERTY_CODE);
            $this->_collectionResult = array_merge($this->_collectionResult, $result);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->_response[Status::SUCCESS][] = __('Properties have been collected. Effected property(s): %1',
            implode(', ', $this->_collectionResult));

        return $this;
    }

    /**
     * @param null $updatedAt
     * @param null $id
     * @return $this
     * @throws \Exception
     */
    public function collectManufacturers(
        $updatedAt = null,
        $id = null
    ) {
        if (!$this->getProfileEntity()) {
            throw new \Exception(__('Profile entity is not set.'));
        }

        if (!$this->getProfileEntity()->getManufacturerMapping()) {
            return $this;
        }

        $this->_collectionResult = [];
        $page = 1;

        do {
            $response = $this->_api()->getSearchManufacturers($page, $updatedAt, $id);
            if (!$response->getSize()) {
                return $this;
            }

            $this->_saveManufacturers($response);

            $result = $response->getColumnValues(AttributeInterface::NAMES);
            $this->_collectionResult = array_merge($this->_collectionResult, $result);

            $page = $response->getFlag('page');
            $last = $response->getFlag('lastPageNumber');
            $page++;
        } while ($page <= $last);

        $this->_response[Status::SUCCESS][] = __('Manufacturers have been collected. Effected manufacturer(s): %1',
            implode(', ', $this->_collectionResult));

        return $this;
    }

    /**
     * @return AttributeClient
     */
    protected function _api()
    {
        if (!$this->_api->getProfileEntity()) {
            $this->_api->setProfileEntity($this->getProfileEntity());
        }
        return $this->_api;
    }

    /**
     * @return string|null
     * @throws \Exception
     */
    protected function _getLastUpdatedAt()
    {
        $updatedBetween = null;
        if ($this->getProfileEntity()->getApiBehaviour() !== Behaviour::APPEND) {
            return $updatedBetween;
        }

        /** @var Attribute $lastUpdatedEntry */
        $lastUpdatedEntry = $this->getCollection()
            ->addFieldToFilter(self::PROFILE_ID, $this->getProfileEntity()->getProfile()->getId())
            ->addFieldToSelect(self::COLLECTED_AT)
            ->setOrder(self::COLLECTED_AT, 'desc')
            ->setPageSize(1)
            ->getFirstItem();

        if (strtotime($lastUpdatedEntry->getCollectedAt()) > 0) {
            $updatedBetween = $this->_helper()
                ->getDateTimeLocale($lastUpdatedEntry->getCollectedAt());
        }

        return $updatedBetween;
    }

    /**
     * @param Collection $attributes
     * @return $this
     * @throws \Exception
     */
    protected function _saveAttributes(Collection $attributes)
    {
        $this->getResource()
            ->saveAttributes($this->getProfileEntity()->getProfile(), $attributes);

        return $this;
    }

    /**
     * @param Collection $manufacturer
     * @return $this
     * @throws \Exception
     */
    protected function _saveManufacturers(Collection $manufacturer)
    {
        $this->getResource()
            ->saveManufacturers($this->getProfileEntity()->getProfile(), $manufacturer);

        return $this;
    }

    /**
     * @param Collection $properties
     * @return $this
     * @throws \Exception
     */
    protected function _saveProperties(Collection $properties)
    {
        $this->getResource()
            ->saveProperties($this->getProfileEntity()->getProfile(), $properties);

        return $this;
    }
}
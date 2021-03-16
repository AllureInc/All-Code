<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\ResourceModel\Import;

use Plenty\Core\Model\Profile;
use Plenty\Item\Model\ResourceModel\ImportExportAbstract;
use Plenty\Item\Api\Data\Import\AttributeInterface;
use Magento\Framework\Data\Collection;
use Magento\Framework\DataObject;

/**
 * Class Attribute
 * @package Plenty\Item\Model\ResourceModel\Import
 */
class Attribute extends ImportExportAbstract
{
    protected function _construct()
    {
        $this->_init('plenty_item_import_attribute', 'entity_id');
    }

    /**
     * @param DataObject $attribute
     * @return array|DataObject
     */
    protected function _prepareAttributeData(DataObject $attribute): array
    {
        if (!$profileId = $attribute->getData(AttributeInterface::PROFILE_ID)) {
            return [];
        }

        return [
            AttributeInterface::PROFILE_ID              => $profileId,
            AttributeInterface::PROFILE_ATTRIBUTE_ID    => "attr_{$attribute->getData(AttributeInterface::ATTRIBUTE_CODE)}_{$profileId}",
            AttributeInterface::TYPE                    => AttributeInterface::ENTITY_ATTRIBUTE,
            AttributeInterface::ATTRIBUTE_ID            => $attribute->getData(AttributeInterface::ATTRIBUTE_ID),
            AttributeInterface::POSITION                => $attribute->getData(AttributeInterface::POSITION),
            AttributeInterface::ATTRIBUTE_CODE          => $attribute->getData(AttributeInterface::ATTRIBUTE_CODE),
            AttributeInterface::NAMES                   => $this->_serializer->serialize($attribute->getData(AttributeInterface::NAMES)),
            AttributeInterface::VALUES                  => $this->_serializer->serialize($attribute->getData(AttributeInterface::VALUES)),
            AttributeInterface::VALUE_NAMES             => $this->_serializer->serialize($attribute->getData(AttributeInterface::VALUE_NAMES)),
            AttributeInterface::MAPS                    => $this->_serializer->serialize($attribute->getData(AttributeInterface::MAPS)),
            AttributeInterface::ENTRIES                 => $this->_serializer->serialize($attribute->getData(AttributeInterface::ENTRIES)),
            AttributeInterface::CREATED_AT              => $this->_date->gmtDate(),
            AttributeInterface::UPDATED_AT              => $attribute->getData(AttributeInterface::UPDATED_AT),
            AttributeInterface::COLLECTED_AT            => $this->_date->gmtDate(),
            AttributeInterface::MESSAGE                 => __('Collected.')
        ];
    }

    /**
     * @param DataObject $property
     * @return array
     */
    protected function _preparePropertyData(DataObject $property): array
    {
        if (!$profileId = $property->getData(AttributeInterface::PROFILE_ID)) {
            return [];
        }

        return [
            AttributeInterface::PROFILE_ID              => $profileId,
            AttributeInterface::PROFILE_ATTRIBUTE_ID    => "prop_{$property->getData(AttributeInterface::PROPERTY_ID)}_{$profileId}",
            AttributeInterface::PROPERTY_ID             => $property->getData(AttributeInterface::PROPERTY_ID),
            AttributeInterface::POSITION                => $property->getData(AttributeInterface::POSITION),
            AttributeInterface::TYPE                    => AttributeInterface::ENTITY_PROPERTY,
            AttributeInterface::PROPERTY_CODE           => $property->getData(AttributeInterface::PROPERTY_CODE),
            AttributeInterface::PROPERTY_GROUP_ID       => $property->getData(AttributeInterface::PROPERTY_GROUP_ID),
            AttributeInterface::VALUE_TYPE              => $property->getData(AttributeInterface::VALUE_TYPE),
            AttributeInterface::NAMES                   => $this->_serializer->serialize($property->getData(AttributeInterface::NAMES)),
            AttributeInterface::GROUP                   => $this->_serializer->serialize($property->getData(AttributeInterface::GROUP)),
            AttributeInterface::MARKET_COMPONENTS       => $this->_serializer->serialize($property->getData(AttributeInterface::MARKET_COMPONENTS)),
            AttributeInterface::ENTRIES                 => $this->_serializer->serialize($property->getData(AttributeInterface::ENTRIES)),
            AttributeInterface::CREATED_AT              => $this->_date->gmtDate(),
            AttributeInterface::UPDATED_AT              => $property->getData(AttributeInterface::UPDATED_AT),
            AttributeInterface::COLLECTED_AT            => $this->_date->gmtDate(),
            AttributeInterface::MESSAGE                 => __('Collected.')
        ];
    }

    /**
     * @param DataObject $manufacturer
     * @return array
     */
    protected function _prepareManufacturerData(DataObject $manufacturer): array
    {
        if (!$profileId = $manufacturer->getData(AttributeInterface::PROFILE_ID)) {
            return [];
        }

        return [
            AttributeInterface::PROFILE_ID              => $profileId,
            AttributeInterface::PROFILE_ATTRIBUTE_ID    => "man_{$manufacturer->getData(AttributeInterface::MANUFACTURER_ID)}_{$profileId}",
            AttributeInterface::TYPE                    => AttributeInterface::ENTITY_MANUFACTURER,
            AttributeInterface::POSITION                => $manufacturer->getData(AttributeInterface::POSITION),
            AttributeInterface::MANUFACTURER_ID         => $manufacturer->getData(AttributeInterface::MANUFACTURER_ID),
            AttributeInterface::NAME                    => $manufacturer->getData(AttributeInterface::NAME),
            AttributeInterface::VALUES                  => $manufacturer->getData(AttributeInterface::VALUES),
            AttributeInterface::ENTRIES                 => $this->_serializer->serialize($manufacturer->getData(AttributeInterface::ENTRIES)),
            AttributeInterface::CREATED_AT              => $this->_date->gmtDate(),
            AttributeInterface::UPDATED_AT              => $manufacturer->getData(AttributeInterface::UPDATED_AT),
            AttributeInterface::COLLECTED_AT            => $this->_date->gmtDate(),
            AttributeInterface::MESSAGE                 => __('Collected.')
        ];
    }

    /**
     * @param Profile $profile
     * @param Collection $data
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    public function saveAttributes(
        Profile $profile, Collection $data, array $fields = []
    ) {
        $attributes = [];
        /** @var DataObject $item */
        foreach ($data as $item) {
            $item->setData(AttributeInterface::PROFILE_ID, $profile->getId());
            $attributes[] = $this->_prepareAttributeData($item);
        }

        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $attributes, $fields);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param Profile $profile
     * @param Collection $data
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    public function saveProperties(
        Profile $profile, Collection $data, array $fields = []
    ) {
        $properties = [];
        foreach ($data as $item) {
            $item->setData(AttributeInterface::PROFILE_ID, $profile->getId());
            $properties[] = $this->_preparePropertyData($item);
        }

        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $properties, $fields);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param Profile $profile
     * @param Collection $data
     * @param array $fields
     * @return $this
     * @throws \Exception
     */
    public function saveManufacturers(
        Profile $profile, Collection $data, array $fields = []
    ) {
        /** @var DataObject $item */
        $manufacturers = [];
        foreach ($data as $item) {
            $item->setData(AttributeInterface::PROFILE_ID, $profile->getId());
            $manufacturers[] = $this->_prepareManufacturerData($item);
        }

        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $manufacturers, $fields);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param $table
     * @return $this
     */
    public function _truncateTable($table)
    {
        if ($this->getConnection()->getTransactionLevel() > 0) {
            $this->getConnection()->delete($table);
        } else {
            $this->getConnection()->truncateTable($table);
        }
        return $this;
    }
}
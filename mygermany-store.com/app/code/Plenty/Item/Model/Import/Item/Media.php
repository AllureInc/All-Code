<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Magento\Framework\DataObject\IdentityInterface;
use Plenty\Item\Model\ImportExportAbstract;
use Plenty\Item\Model\Import\Item;
use Plenty\Item\Model\ResourceModel\Import\Item\Media\Collection;
use Plenty\Item\Api\Data\Import\Item\MediaInterface;

/**
 * Class Media
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Media getResource()
 * @method Collection getCollection()
 */
class Media extends ImportExportAbstract implements MediaInterface,
    IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_media';
    protected $_cacheTag        = 'plenty_item_import_item_media';
    protected $_eventPrefix     = 'plenty_item_import_item_media';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Media::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }

    public function getItemId()
    {
        return $this->getData(self::ITEM_ID);
    }

    public function getVariationId()
    {
        return $this->getData(self::VARIATION_ID);
    }

    public function getExternalId()
    {
        return $this->getData(self::EXTERNAL_ID);
    }

    public function getSku()
    {
        return $this->getData(self::SKU);
    }

    public function getMediaId()
    {
        return $this->getData(self::MEDIA_ID);
    }

    public function getMediaType()
    {
        return $this->getData(self::MEDIA_TYPE);
    }

    public function getIsLinkedToVariation()
    {
        return $this->getData(self::IS_LINKED_TO_VARIATION);
    }

    public function getType()
    {
        return $this->getData(self::TYPE);
    }

    public function getFileType()
    {
        return $this->getData(self::FILE_TYPE);
    }

    public function getPath()
    {
        return $this->getData(self::PATH);
    }

    public function getPosition()
    {
        return $this->getData(self::POSITION);
    }

    public function getMd5Checksum()
    {
        return $this->getData(self::MD5_CHECKSUM);
    }

    public function getMd5ChecksumOriginal()
    {
        return $this->getData(self::MD5_CHECKSUM_ORIGINAL);
    }

    public function getWidth()
    {
        return $this->getData(self::WIDTH);
    }

    public function getHeight()
    {
        return $this->getData(self::HEIGHT);
    }

    public function getSize()
    {
        return $this->getData(self::SIZE);
    }

    public function getStorageProviderId()
    {
        return $this->getData(self::STORAGE_PROVIDER_ID);
    }

    public function getCleanImageName()
    {
        return $this->getData(self::CLEAN_IMAGE_NAME);
    }

    public function getUrl()
    {
        return $this->getData(self::URL);
    }

    public function getUrlMiddle()
    {
        return $this->getData(self::URL_MIDDLE);
    }

    public function getUrlPreview()
    {
        return $this->getData(self::URL_PREVIEW);
    }

    public function getUrlSecondaryPreview()
    {
        return $this->getData(self::URL_SECONDARY_PREVIEW);
    }

    public function getDocumentUploadPath()
    {
        return $this->getData(self::DOCUMENT_UPLOAD_PATH);
    }

    public function getDocumentUploadPathPreview()
    {
        return $this->getData(self::DOCUMENT_UPLOAD_PATH_PREVIEW);
    }

    public function getDocumentUploadPreviewWidth()
    {
        return $this->getData(self::DOCUMENT_UPLOAD_PREVIEW_WIDTH);
    }

    public function getDocumentUploadPreviewHeight()
    {
        return $this->getData(self::DOCUMENT_UPLOAD_PREVIEW_HEIGHT);
    }

    public function getAvailabilities()
    {
        return $this->_serializer->unserialize($this->getData(self::AVAILABILITIES));
    }

    public function getNames()
    {
        return $this->_serializer->unserialize($this->getData(self::NAMES));
    }

    public function getCreatedAt()
    {
        return $this->getData(self::CREATED_AT);
    }

    public function getUpdatedAt()
    {
        return $this->getData(self::UPDATED_AT);
    }

    public function getCollectedAt()
    {
        return $this->getData(self::COLLECTED_AT);
    }

    /**
     * @param Item $item
     * @return Collection
     */
    public function getItemImages(Item $item) : Collection
    {
        return $this->getCollection()
            ->addFieldToFilter(self::ITEM_ID, $item->getItemId())
            ->addUniqueFilter()
            ->setOrder('position', 'ASC');
    }

    /**
     * @param $itemId
     * @param bool $unique
     * @return Collection
     */
    public function getItemImagesByItemId($itemId, $unique = false) : Collection
    {
        $collection = $this->getCollection()
            ->addFieldToFilter(self::ITEM_ID, $itemId);

        if ($unique) {
            $collection->addUniqueFilter();
        }

        $collection->setOrder('position', 'ASC');

        return $collection;
    }
}
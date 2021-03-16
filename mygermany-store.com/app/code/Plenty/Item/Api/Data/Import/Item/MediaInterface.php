<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Api\Data\Import\Item;

use Plenty\Item\Model\ResourceModel\Import\Item\Media\Collection;

/**
 * Interface MediaInterface
 * @package Plenty\Item\Api\Data\Import\Item
 */
interface MediaInterface
{
    const MEDIA_TYPE_ITEM                       = 'item';
    const MEDIA_TYPE_VARIATION                  = 'variation';

    const ENTITY_ID                             = 'entity_id';
    const ITEM_ID                               = 'item_id';
    const VARIATION_ID                          = 'variation_id';
    const EXTERNAL_ID                           = 'external_id';
    const SKU                                   = 'sku';
    const MEDIA_ID                              = 'media_id';
    const MEDIA_TYPE                            = 'media_type';
    const IS_LINKED_TO_VARIATION                = 'is_linked_to_variation';
    const TYPE                                  = 'type';
    const FILE_TYPE                             = 'file_type';
    const PATH                                  = 'path';
    const POSITION                              = 'position';
    const MD5_CHECKSUM                          = 'md5_checksum';
    const MD5_CHECKSUM_ORIGINAL                 = 'md5_checksum_original';
    const WIDTH                                 = 'width';
    const HEIGHT                                = 'height';
    const SIZE                                  = 'size';
    const STORAGE_PROVIDER_ID                   = 'storage_provider_id';
    const CLEAN_IMAGE_NAME                      = 'clean_image_name';
    const URL                                   = 'url';
    const URL_MIDDLE                            = 'url_middle';
    const URL_PREVIEW                           = 'url_preview';
    const URL_SECONDARY_PREVIEW                 = 'url_secondary_preview';
    const DOCUMENT_UPLOAD_PATH                  = 'document_upload_path';
    const DOCUMENT_UPLOAD_PATH_PREVIEW          = 'document_upload_path_preview';
    const DOCUMENT_UPLOAD_PREVIEW_WIDTH         = 'document_upload_preview_width';
    const DOCUMENT_UPLOAD_PREVIEW_HEIGHT        = 'document_upload_preview_height';
    // []
    const AVAILABILITIES                        = 'availabilities';
    const IMAGE_ID                              = 'imageId';
    const VALUE                                 = 'value';
    // []
    const NAMES                                 = 'names';
    const LANG                                  = 'lang';
    const NAME                                  = 'name';
    const ALTERNATE                             = 'alternate';
    const CREATED_AT                            = 'created_at';
    const UPDATED_AT                            = 'updated_at';
    const COLLECTED_AT                          = 'collected_at';
    const PROCESSED_AT                          = 'processed_at';

    public function getItemId();

    public function getVariationId();

    public function getExternalId();

    public function getSku();

    public function getMediaId();

    public function getMediaType();

    public function getIsLinkedToVariation();

    public function getType();

    public function getFileType();

    public function getPath();

    public function getPosition();

    public function getMd5Checksum();

    public function getMd5ChecksumOriginal();

    public function getWidth();

    public function getHeight();

    public function getSize();

    public function getStorageProviderId();

    public function getCleanImageName();

    public function getUrl();

    public function getUrlMiddle();

    public function getUrlPreview();

    public function getUrlSecondaryPreview();

    public function getDocumentUploadPath();

    public function getDocumentUploadPathPreview();

    public function getDocumentUploadPreviewWidth();

    public function getDocumentUploadPreviewHeight();

    public function getAvailabilities();

    public function getNames();

    public function getCreatedAt();

    public function getUpdatedAt();

    public function getCollectedAt();

    /**
     * @param \Plenty\Item\Model\Import\Item $item
     * @return Collection
     */
    public function getItemImages(\Plenty\Item\Model\Import\Item $item) : Collection;

    /**
     * @param $itemId
     * @return Collection
     */
    public function getItemImagesByItemId($itemId) : Collection;
}
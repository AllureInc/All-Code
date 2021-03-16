<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData\Item;

/**
 * Interface MediaDataInterface
 * @package Plenty\Item\Rest\AbstractData\Item
 */
interface MediaDataInterface
{
    const ID                                    = 'id';
    const ITEM_ID                               = 'itemId';
    const VARIATION_ID                          = 'variationId';
    const TYPE                                  = 'type';
    const FILE_TYPE                             = 'fileType';
    const PATH                                  = 'path';
    const POSITION                              = 'position';
    const MD5_CHECKSUM                          = 'md5Checksum';
    const MD5_CHECKSUM_ORIGINAL                 = 'md5ChecksumOriginal';
    const WIDTH                                 = 'width';
    const HEIGHT                                = 'height';
    const SIZE                                  = 'size';
    const STORAGE_PROVIDER_ID                   = 'storageProviderId';
    const CLEAN_IMAGE_NAME                      = 'cleanImageName';
    const URL                                   = 'url';
    const URL_MIDDLE                            = 'urlMiddle';
    const URL_PREVIEW                           = 'urlPreview';
    const URL_SECONDARY_PREVIEW                 = 'urlSecondPreview';
    const DOCUMENT_UPLOAD_PATH                  = 'documentUploadPath';
    const DOCUMENT_UPLOAD_PATH_PREVIEW          = 'documentUploadPathPreview';
    const DOCUMENT_UPLOAD_PREVIEW_WIDTH         = 'documentUploadPreviewWidth';
    const DOCUMENT_UPLOAD_PREVIEW_HEIGHT        = 'documentUploadPreviewHeight';
    const AVAILABILITIES                        = 'availabilities';
    const IMAGE_ID                              = 'imageId';
    const VALUE                                 = 'value';
    const NAMES                                 = 'names';
    const CREATED_AT                            = 'insert';
    const UPDATED_AT                            = 'lastUpdate';
}
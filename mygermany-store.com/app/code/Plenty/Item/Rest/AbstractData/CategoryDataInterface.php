<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest\AbstractData;

/**
 * Interface CategoryDataInterface
 * @package Plenty\Item\Rest\AbstractData
 */
interface CategoryDataInterface
{
    const CATEGORY_TYPE_ITEM                = 'item';
    const CATEGORY_TYPE_CONTAINER           = 'container';
    const CATEGORY_TYPE_CONTENT             = 'content';
    const CATEGORY_TYPE_BLOG                = 'blog';

    // ATTRIBUTES
    const ID                                = 'id';
    const PARENT_CATEGORY_ID                = 'parentCategoryId';
    const LEVEL                             = 'level';
    const TYPE                              = 'type';
    const LINK_LIST                         = 'linklist';
    const RIGHT                             = 'right';
    const SITEMAP                           = 'sitemap';
    const HAS_CHILDREN                      = 'hasChildren';
    const DETAILS                           = 'details';
    const NAME                              = 'name';
    const LANG                              = 'lang';
    const DESCRIPTION                       = 'description';
    const DESCRIPTION2                      = 'description2';
    const SHORT_DESCRIPTION                 = 'shortDescription';
    const META_KEYWORDS                     = 'metaKeywords';
    const META_DESCRIPTION                  = 'metaDescription';
    const NAME_URL                          = 'nameUrl';
    const META_TITLE                        = 'metaTitle';
    const POSITION                          = 'position';
    const UPDATED_AT                        = 'updatedAt';
    const UPDATED_BY                        = 'updatedBy';
    const ITEM_LIST_VIEW                    = 'itemListView';
    const SINGLE_ITEM_VIEW                  = 'singleItemView';
    const PAGE_VIEW                         = 'pageView';
    const FULL_TEXT                         = 'fulltext';
    const META_ROBOTS                       = 'metaRobots';
    const CANONICAL_LINK                    = 'canonicalLink';
    const PREVIEW_URL                       = 'previewUrl';
    const IMAGE                             = 'image';
    const IMAGE_PATH                        = 'imagePath';
    const IMAGE2                            = 'image2';
    const IMAGE2_PATH                       = 'image2Path';
    const PLENTY_ID                         = 'plentyId';
}
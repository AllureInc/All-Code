<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Rest;

/**
 * Interface ItemInterface
 * @package Plenty\Item\Rest
 */
interface ItemInterface
{
    /**
     * @param int $page
     * @param null $itemId
     * @param null $with
     * @param null $flagOne
     * @param null $flagTwo
     * @param null $lang
     * @param null $updatedBetween
     * @param null $variationUpdatedBetween
     * @return mixed
     */
    public function getSearchItems(
        $page = 1,
        $itemId = null,
        $with = null,
        $flagOne = null,
        $flagTwo = null,
        $lang = null,
        $updatedBetween = null,
        $variationUpdatedBetween = null
    );

    public function getItemImages($itemId);


    /**
     * @param array $params
     * @param null $itemId
     * @return mixed
     */
    public function createItem(array $params, $itemId = null);

    /**
     * @param $itemId
     * @param array $params
     * @param null $imageId
     * @return mixed
     */
    public function createItemImages($itemId, array $params, $imageId = null);

    /**
     * @param $itemId
     * @param $imageId
     * @param array $params
     * @return mixed
     */
    public function deleteItemImages($itemId, $imageId, array $params);
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Item\Model\Import\Item;

use Plenty\Item\Model\ImportExportAbstract;

/**
 * Class Crosssells
 * @package Plenty\Item\Model\Import\Item
 *
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Crosssells getResource()
 * @method \Plenty\Item\Model\ResourceModel\Import\Item\Crosssells\Collection getCollection()
 */
class Crosssells extends ImportExportAbstract implements \Plenty\Item\Api\Data\Import\Item\CrosssellsInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG             = 'plenty_item_import_item_crosssells';
    protected $_cacheTag        = 'plenty_item_import_item_crosssells';
    protected $_eventPrefix     = 'plenty_item_import_item_crosssells';

    protected function _construct()
    {
        $this->_init(\Plenty\Item\Model\ResourceModel\Import\Item\Crosssells::class);
    }

    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
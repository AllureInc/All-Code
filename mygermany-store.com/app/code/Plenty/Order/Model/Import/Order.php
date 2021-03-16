<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Order\Model\Import;

/**
 * @method \Plenty\Order\Model\ResourceModel\Import\Order getResource()
 * @method \Plenty\Order\Model\ResourceModel\Import\Order\Collection getCollection()
 */
class Order extends \Magento\Framework\Model\AbstractModel implements \Plenty\Order\Api\Data\Import\OrderInterface,
    \Magento\Framework\DataObject\IdentityInterface
{
    const CACHE_TAG = 'plenty_order_import_order';
    protected $_cacheTag = 'plenty_order_import_order';
    protected $_eventPrefix = 'plenty_order_import_order';

    protected function _construct()
    {
        $this->_init(\Plenty\Order\Model\ResourceModel\Import\Order::class);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [self::CACHE_TAG . '_' . $this->getId()];
    }
}
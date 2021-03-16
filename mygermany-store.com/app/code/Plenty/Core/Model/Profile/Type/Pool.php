<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Type;

use Magento\Framework\ObjectManagerInterface;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class Pool
 * @package Plenty\Core\Model\Profile\Type
 */
class Pool
{
    /**
     * Object Manager
     *
     * @var ObjectManagerInterface
     */
    protected $_objectManager;

    /**
     * Construct
     *
     * @param ObjectManagerInterface $objectManager
     */
    public function __construct(ObjectManagerInterface $objectManager)
    {
        $this->_objectManager = $objectManager;
    }

    /**
     * @param $className
     * @return mixed
     * @throws LocalizedException
     */
    public function get($className)
    {
        $profile = $this->_objectManager->get($className);
        if (!$profile instanceof AbstractType) {
            throw new LocalizedException(__('%1 doesn\'t extend %2', AbstractType::class, $className));
        }
        return $profile;
    }
}
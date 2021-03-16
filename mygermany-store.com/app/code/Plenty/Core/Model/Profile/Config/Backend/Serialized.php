<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\Profile\Config\Backend;

use Magento\Framework\App\ObjectManager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Math\Random;
use Plenty\Core\App\Config\ScopeConfigInterface;

/**
 * Class Serialized
 * @package Plenty\Core\Model\Profile\Config\Backend
 */
class Serialized extends \Plenty\Core\App\Config\Value
{
    /**
     * @var Json
     */
    private $serializer;

    /**
     * @var \Magento\Framework\Math\Random
     */
    protected $mathRandom;

    /**
     * Serialized constructor.
     * @param \Magento\Framework\Model\Context $context
     * @param \Magento\Framework\Registry $registry
     * @param ScopeConfigInterface $config
     * @param \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList
     * @param Random $mathRandom
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource|null $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb|null $resourceCollection
     * @param array $data
     * @param Json|null $serializer
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        Random $mathRandom,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = [],
        Json $serializer = null
    ) {
        $this->mathRandom = $mathRandom;
        $this->serializer = $serializer ?: ObjectManager::getInstance()->get(Json::class);
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Process data after load
     *
     * @return $this
     */
    public function afterLoad()
    {
        if (!$this->getValue() || is_array($this->getValue())) {
            return $this;
        }

        $value = $this->serializer->unserialize($this->getValue());
        if (!is_array($value)) {
            return $this;
        }

        unset($value['__empty']);
        $this->setValue($value);
        return $this;
    }

    /**
     * @return $this
     */
    public function beforeSave()
    {
        $value = $this->getValue();
        if (is_array($value)) {
            if (isset($value['__empty'])) {
                unset($value['__empty']);
            }

            if (!empty($value)) {
                $this->setValue($this->serializer->serialize($value));
            }
        }
        parent::beforeSave();
        return $this;
    }
}

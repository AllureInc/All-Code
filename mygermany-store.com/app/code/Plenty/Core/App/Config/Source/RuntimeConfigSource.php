<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */

namespace Plenty\Core\App\Config\Source;

// use Magento\Framework\App\Config\ConfigSourceInterface;
use Plenty\Core\App\Config\ConfigSourceInterface;
use Plenty\Core\App\Config\ScopeConfigInterface;
use Plenty\Core\Model\ResourceModel\Profile\Config\CollectionFactory;
use Magento\Framework\App\Config\ScopeCodeResolver;
use Magento\Framework\App\Config\Scope\Converter;
use Magento\Framework\DataObject;


/**
 * Class RuntimeConfigSource
 * @package Plenty\Core\App\Config\Source
 */
class RuntimeConfigSource implements ConfigSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Converter
     */
    private $converter;

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var null|int
     */
    private $_profileId   = null;

    /**
     * RuntimeConfigSource constructor.
     * @param CollectionFactory $collectionFactory
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param Converter $converter
     */
    public function __construct(
        CollectionFactory $collectionFactory,
        ScopeCodeResolver $scopeCodeResolver,
        Converter $converter
    ) {
        $this->collectionFactory = $collectionFactory;
        $this->converter = $converter;
        $this->scopeCodeResolver = $scopeCodeResolver;
    }

    /**
     * @return int|null
     */
    public function getProfileId()
    {
        return $this->_profileId;
    }

    /**
     * @param $id
     * @return int|null
     */
    public function setProfileId($id)
    {
        return $this->_profileId = $id;
    }

    /**
     * Get initial data.
     *
     * @param (int) $profileId
     * @param string $path
     * @return array|string
     */
    public function get($path = '')
    {
        $data = new DataObject($this->loadConfig());
        return $data->getData($path) ?: [];
    }

    /**
     * Load collection from db and presents it in array with path keys.
     * E.G: scope/key/key
     *
     * @param (int) $profileId
     * @return array
     */
    private function loadConfig()
    {
        try {
            $collection = $this->collectionFactory->create();
        } catch (\DomainException $e) {
            $collection = [];
        }

        $collection->addProfileFilter($this->getProfileId());

        $config = [];
        foreach ($collection as $item) {
            if ($item->getScope() === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $config[$item->getScope()][$item->getPath()] = $item->getValue();
            } else {
                $code = $this->scopeCodeResolver->resolve($item->getScope(), $item->getScopeId());
                $config[$item->getScope()][$code][$item->getPath()] = $item->getValue();
            }
        }

        foreach ($config as $scope => $item) {
            if ($scope === ScopeConfigInterface::SCOPE_TYPE_DEFAULT) {
                $config[$scope] = $this->converter->convert($item);
            } else {
                foreach ($item as $scopeCode => $scopeItems) {
                    $config[$scope][$scopeCode] = $this->converter->convert($scopeItems);
                }
            }
        }
        return $config;
    }
}

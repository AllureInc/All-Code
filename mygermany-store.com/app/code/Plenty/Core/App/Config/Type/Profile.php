<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config\Type;

use Plenty\Core\App\Config\ConfigTypeInterface;
use Plenty\Core\App\Config\ConfigSourceInterface;
use Plenty\Core\App\Config\Type\Profile\Reader;

use Magento\Framework\App\Config\Spi\PostProcessorInterface;
use Magento\Framework\App\Config\Spi\PreProcessorInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\App\ScopeInterface;
use Magento\Framework\Cache\FrontendInterface;
use Magento\Framework\Serialize\SerializerInterface;
use Magento\Store\Model\Config\Processor\Fallback;
use Magento\Store\Model\ScopeInterface as StoreScope;
use Magento\Framework\Encryption\Encryptor;

/**
 * Class Profile
 * @package Plenty\Core\App\Config\Type
 */
class Profile implements ConfigTypeInterface
{
    const CACHE_TAG         = 'plenty_config_scopes';

    const CONFIG_TYPE       = 'plenty_profile';

    /**
     * @var null|int
     */
    private $_profileId     = null;

    /**
     * @var array
     */
    private $data           = [];

    /**
     * @var \Magento\Framework\App\Config\Spi\PostProcessorInterface
     */
    private $postProcessor;

    /**
     * @var \Magento\Framework\Cache\FrontendInterface
     */
    private $cache;

    /**
     * @var SerializerInterface
     */
    private $serializer;

    /**
     * The type of config.
     *
     * @var string
     */
    private $configType;

    /**
     * @var Reader
     */
    private $reader;

    /**
     * List of scopes that were retrieved from configuration storage
     * Is used to make sure that we don't try to load non-existing configuration scopes.
     *
     * @var array
     */
    private $availableDataScopes;

    /**
     * @var Encryptor
     */
    private $encryptor;

    /**
     * Profile constructor.
     * @param ConfigSourceInterface $source
     * @param PostProcessorInterface $postProcessor
     * @param Fallback $fallback
     * @param FrontendInterface $cache
     * @param SerializerInterface $serializer
     * @param PreProcessorInterface $preProcessor
     * @param int $cachingNestedLevel
     * @param string $configType
     * @param Reader|null $reader
     * @param Encryptor|null $encryptor
     */
    public function __construct(
        ConfigSourceInterface $source,
        PostProcessorInterface $postProcessor,
        Fallback $fallback,
        FrontendInterface $cache,
        SerializerInterface $serializer,
        PreProcessorInterface $preProcessor,
        $cachingNestedLevel = 1,
        $configType = self::CONFIG_TYPE,
        Reader $reader = null,
        Encryptor $encryptor = null
    ) {
        $this->postProcessor = $postProcessor;
        $this->cache = $cache;
        $this->serializer = $serializer;
        $this->configType = $configType;
        $this->reader = $reader ?: ObjectManager::getInstance()->get(Reader::class);
        $this->encryptor = $encryptor ?: ObjectManager::getInstance()->get(Encryptor::class);
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
     * Profile configuration is separated by scopes (default, websites, stores). Configuration of a scope is inherited
     * from its parent scope (store inherits website).
     *
     * Because there can be many scopes on single instance of application, the configuration data can be pretty large,
     * so it does not make sense to load all of it on every application request. That is why we cache configuration
     * data by scope and only load configuration scope when a value from that scope is requested.
     *
     * Possible path values:
     * '' - will return whole system configuration (default scope + all other scopes)
     * 'default' - will return all default scope configuration values
     * '{scopeType}' - will return data from all scopes of a specified {scopeType} (websites, stores)
     * '{scopeType}/{scopeCode}' - will return data for all values of the scope specified by {scopeCode} and scope type
     * '{scopeType}/{scopeCode}/some/config/variable' - will return value of the config variable in the specified scope
     *
     * @param (int) $profileId
     * @param string $path
     * @return array|bool|int|mixed|string|null
     * @throws \Exception
     */
    public function get($path = '')
    {
        if ($path === '') {
            $this->data = array_replace_recursive($this->loadAllData(), $this->data);
            return $this->data;
        }

        return $this->getWithParts($path);
    }

    /**
     * Proceed with parts extraction from path.
     *
     * @param (int) $profileId
     * @param $path
     * @return mixed|null
     * @throws \Exception
     */
    private function getWithParts($path)
    {
        $pathParts = explode('/', $path);
        if (count($pathParts) === 1 && $pathParts[0] !== ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$pathParts[0]])) {
                $data = $this->readData();
                $this->data = array_replace_recursive($data, $this->data);
            }
            return $this->data[$pathParts[0]];
        }

        $scopeType = array_shift($pathParts);
        if ($scopeType === ScopeInterface::SCOPE_DEFAULT) {
            if (!isset($this->data[$scopeType])) {
                $this->data = array_replace_recursive($this->loadDefaultScopeData($scopeType), $this->data);
            }
            return $this->getDataByPathParts($this->data[$scopeType], $pathParts);
        }

        $scopeId = array_shift($pathParts);

        if (!isset($this->data[$scopeType][$scopeId])) {
            $scopeData = $this->loadScopeData($scopeType, $scopeId);
            if (!isset($this->data[$scopeType][$scopeId])) {
                $this->data = array_replace_recursive($scopeData, $this->data);
            }
        }

        return isset($this->data[$scopeType][$scopeId])
            ? $this->getDataByPathParts($this->data[$scopeType][$scopeId], $pathParts)
            : null;
    }

    /**
     * Load configuration data for all scopes
     *
     * @param (int) $profileId
     * @return array|bool|float|int|string|null
     * @throws \Exception
     */
    private function loadAllData()
    {
        $cachedData = $this->cache->load($this->configType);
        if ($cachedData === false) {
            $data = $this->readData();
        } else {
            $data = $this->serializer->unserialize($this->encryptor->decrypt($cachedData));
        }

        return $data;
    }

    /**
     * Load configuration data for default scope
     *
     * @param (int) $profileId
     * @param $scopeType
     * @return array
     * @throws \Exception
     */
    private function loadDefaultScopeData($scopeType)
    {
        $cachedData = $this->cache->load($this->configType . '_' . $this->getProfileId() . '_' . $scopeType);
        if ($cachedData === false) {
            $data = $this->readData();
            $this->cacheData($data);
        } else {
            $data = [$scopeType => $this->serializer->unserialize($this->encryptor->decrypt($cachedData))];
        }

        return $data;
    }

    /**
     * Load configuration data for a specified scope
     *
     * @param (int) $profileId
     * @param $scopeType
     * @param $scopeId
     * @return array
     * @throws \Exception
     */
    private function loadScopeData($scopeType, $scopeId)
    {
        $cachedData = $this->cache->load($this->configType . '_' . $this->getProfileId() . '_' . $scopeId);

        if ($cachedData === false) {
            if ($this->availableDataScopes === null) {
                $cachedScopeData = $this->cache->load($this->configType . '_scopes');
                if ($cachedScopeData !== false) {
                    $serializedCachedData = $this->encryptor->decrypt($cachedScopeData);
                    $this->availableDataScopes = $this->serializer->unserialize($serializedCachedData);
                }
            }
            if (is_array($this->availableDataScopes) && !isset($this->availableDataScopes[$scopeType][$scopeId])) {
                return [$scopeType => [$scopeId => []]];
            }
            $data = $this->readData();
            $this->cacheData($data);
        } else {
            $serializedCachedData = $this->encryptor->decrypt($cachedData);
            $data = [$scopeType => [$scopeId => $this->serializer->unserialize($serializedCachedData)]];
        }

        return $data;
    }

    /**
     * Cache configuration data.
     * Caches data per scope to avoid reading data for all scopes on every request
     *
     * @param array $data
     */
    private function cacheData(array $data)
    {
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data)),
            $this->configType,
            [self::CACHE_TAG]
        );
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($data['default'])),
            $this->configType . '_default',
            [self::CACHE_TAG]
        );
        $scopes = [];
        foreach ([StoreScope::SCOPE_WEBSITES, StoreScope::SCOPE_STORES] as $curScopeType) {
            foreach ($data[$curScopeType] ?? [] as $curScopeId => $curScopeData) {
                $scopes[$curScopeType][$curScopeId] = 1;
                $this->cache->save(
                    $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($curScopeData)),
                    $this->configType . '_' . $curScopeType . '_' . $curScopeId,
                    [self::CACHE_TAG]
                );
            }
        }
        $this->cache->save(
            $this->encryptor->encryptWithFastestAvailableAlgorithm($this->serializer->serialize($scopes)),
            $this->configType . '_scopes',
            [self::CACHE_TAG]
        );
    }

    /**
     * Walk nested hash map by keys from $pathParts
     *
     * @param array $data to walk in
     * @param array $pathParts keys path
     * @return mixed
     */
    private function getDataByPathParts($data, $pathParts)
    {
        foreach ($pathParts as $key) {
            if ((array)$data === $data && isset($data[$key])) {
                $data = $data[$key];
            } elseif ($data instanceof \Magento\Framework\DataObject) {
                $data = $data->getDataByKey($key);
            } else {
                return null;
            }
        }

        return $data;
    }

    /**
     * The freshly read data.
     *
     * @param (int) $profileId
     * @return array
     */
    private function readData(): array
    {
        $this->reader->setProfileId($this->getProfileId());
        $this->data = $this->reader->read();
        $this->data = $this->postProcessor->process($this->data);

        return $this->data;
    }

    /**
     * Clean cache and global variables cache
     *
     * Next items cleared:
     * - Internal property intended to store already loaded configuration data
     * - All records in cache storage tagged with CACHE_TAG
     *
     * @return void
     * @since 100.1.2
     */
    public function clean()
    {
        $this->data = [];
        $this->cache->clean(\Zend_Cache::CLEANING_MODE_MATCHING_TAG, [self::CACHE_TAG]);
    }
}

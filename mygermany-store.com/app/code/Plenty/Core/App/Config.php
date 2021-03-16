<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App;

use Plenty\Core\App\Config\ConfigTypeInterface;
use Plenty\Core\App\Config\ScopeConfigInterface;
// use Magento\Framework\App\Config\ConfigTypeInterface;
use Magento\Framework\App\Config\ScopeCodeResolver;

/**
 * Class Config
 * @package Plenty\Core\App
 */
class Config implements ScopeConfigInterface
{
    /**
     * Config cache tag
     */
    const CACHE_TAG = 'PLENTY_CONFIG';

    /**
     * @var ScopeCodeResolver
     */
    private $scopeCodeResolver;

    /**
     * @var ConfigTypeInterface[]
     */
    private $types;

    /**
     * Config constructor.
     *
     * @param ScopeCodeResolver $scopeCodeResolver
     * @param array $types
     */
    public function __construct(
        ScopeCodeResolver $scopeCodeResolver,
        array $types = []
    ) {
        $this->scopeCodeResolver = $scopeCodeResolver;
        $this->types = $types;
    }

    /**
     * Retrieve config value by profile, path and scope
     *
     * @param $profileId
     * @param null $path
     * @param string $scope
     * @param null $scopeCode
     * @return array|mixed
     */
    public function getValue(
        $profileId,
        $path = null,
        $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT,
        $scopeCode = null
    ) {
        if (!$profileId || !is_numeric($profileId)) {
            return false;
        }

        if ($scope === 'store') {
            $scope = 'stores';
        } elseif ($scope === 'website') {
            $scope = 'websites';
        }

        $configPath = $scope;
        if ($scope !== 'default') {
            if (is_numeric($scopeCode) || $scopeCode === null) {
                $scopeCode = $this->scopeCodeResolver->resolve($scope, $scopeCode);
            } elseif ($scopeCode instanceof \Magento\Framework\App\ScopeInterface) {
                $scopeCode = $scopeCode->getCode();
            }
            if ($scopeCode) {
                $configPath .= '/' . $scopeCode;
            }
        }
        if ($path) {
            $configPath .= '/' . $path;
        }

        return $this->get($profileId, 'profile', $configPath);
    }

    /**
     * Retrieve config flag
     *
     * @param string $path
     * @param string $scope
     * @param null|string $scopeCode
     * @return bool
     */
    public function isSetFlag($path, $scope = ScopeConfigInterface::SCOPE_TYPE_DEFAULT, $scopeCode = null)
    {
        return !!$this->getValue($path, $scope, $scopeCode);
    }

    /**
     * Invalidate cache by type
     * Clean scopeCodeResolver
     *
     * @return void
     */
    public function clean()
    {
        foreach ($this->types as $type) {
            $type->clean();
        }
        $this->scopeCodeResolver->clean();
    }

    /**
     * Retrieve profile configuration.
     *
     * ('scopes', 'websites/base') - base website data
     * ('scopes', 'stores/default') - default store data
     *
     * ('profile', 'default/web/seo/use_rewrites') - default system configuration data
     * ('profile', 'websites/base/web/seo/use_rewrites') - 'base' website system configuration data
     *
     * @param (int) $profileId
     * @param string $configType
     * @param string|null $path
     * @param mixed|null $default
     * @return array
     */
    public function get($profileId, $configType, $path = '', $default = null)
    {
        $result = null;
        if (isset($this->types[$configType])) {
            $this->types[$configType]->setProfileId($profileId);
            $result = $this->types[$configType]->get($path);
        }

        return $result !== null ? $result : $default;
    }
}

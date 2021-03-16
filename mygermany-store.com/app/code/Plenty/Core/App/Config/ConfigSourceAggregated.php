<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config;

/**
 * Class ConfigSourceAggregated
 * @package Plenty\Core\App\Config
 */
class ConfigSourceAggregated implements ConfigSourceInterface
{
    /**
     * @var ConfigSourceInterface[]
     */
    private $sources;

    /**
     * @var null|int
     */
    private $_profileId   = null;

    /**
     * ConfigSourceAggregated constructor.
     *
     * @param array $sources
     */
    public function __construct(array $sources = [])
    {
        $this->sources = $sources;
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
     * Retrieve aggregated configuration from all available sources.
     *
     * @param string $path
     * @return string|array
     */
    public function get($path = '')
    {
        $this->sortSources();
        $data = [];
        foreach ($this->sources as $sourceConfig) {
            /** @var ConfigSourceInterface $source */
            $source = $sourceConfig['source'];
            $source->setProfileId($this->getProfileId());
            $configData = $source->get($path);
            if (!is_array($configData)) {
                $data = $configData;
            } elseif (!empty($configData)) {
                $data = array_replace_recursive(is_array($data) ? $data : [], $configData);
            }
        }

        return $data;
    }

    /**
     * Sort sources
     *
     * @return void
     */
    private function sortSources()
    {
        uasort($this->sources, function ($firstItem, $secondItem) {
            return $firstItem['sortOrder'] > $secondItem['sortOrder'];
        });
    }
}

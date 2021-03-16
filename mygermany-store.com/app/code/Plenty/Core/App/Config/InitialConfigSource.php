<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config;

use Magento\Framework\DataObject;
use Magento\Framework\App\DeploymentConfig\Reader;

/**
 * Class InitialConfigSource
 * @package Plenty\Core\App\Config
 */
class InitialConfigSource implements ConfigSourceInterface
{
    /**
     * @var Reader
     */
    private $reader;

    /**
     * @var string
     */
    private $configType;

    /**
     * @var string
     * @deprecated 101.0.0 Initial configs can not be separated since 2.2.0 version
     */
    private $fileKey;

    /**
     * @var null|int
     */
    private $_profileId   = null;

    /**
     * DataProvider constructor.
     *
     * @param Reader $reader
     * @param string $configType
     * @param string $fileKey
     */
    public function __construct(Reader $reader, $configType, $fileKey = null)
    {
        $this->reader = $reader;
        $this->configType = $configType;
        $this->fileKey = $fileKey;
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
     * @param string $path
     * @return array|string
     * @throws \Magento\Framework\Exception\FileSystemException
     * @throws \Magento\Framework\Exception\RuntimeException
     */
    public function get($path = '')
    {
        $data = new DataObject($this->reader->load());
        if ($path !== '' && $path !== null) {
            $path = '/' . $path;
        }

        return $data->getData($this->configType . $path) ?: [];
    }
}

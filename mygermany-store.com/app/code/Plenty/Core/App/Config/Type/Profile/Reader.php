<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\App\Config\Type\Profile;

/**
 * Plenty profile configuration reader.
 * Created this class to encapsulate the complexity of configuration data retrieval.
 * All clients of this class can use its proxy to avoid instantiation when configuration is cached.
 *
 * Class Reader
 * @package Plenty\Core\App\Config\Type\Profile
 */
class Reader
{
    /**
     * @var \Magento\Framework\App\Config\ConfigSourceInterface
     */
    private $source;

    /**
     * @var \Magento\Store\Model\Config\Processor\Fallback
     */
    private $fallback;

    /**
     * @var \Magento\Framework\App\Config\Spi\PreProcessorInterface
     */
    private $preProcessor;

    /**
     * @var null|int
     */
    private $_profileId   = null;

    /**
     * Reader constructor.
     * @param \Plenty\Core\App\Config\ConfigSourceInterface $source
     * @param \Magento\Store\Model\Config\Processor\Fallback $fallback
     * @param \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor
     * @param \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
     */
    public function __construct(
        \Plenty\Core\App\Config\ConfigSourceInterface $source,
        // \Magento\Framework\App\Config\ConfigSourceInterface $source,
        \Magento\Store\Model\Config\Processor\Fallback $fallback,
        \Magento\Framework\App\Config\Spi\PreProcessorInterface $preProcessor,
        \Magento\Framework\App\Config\Spi\PostProcessorInterface $postProcessor
    ) {
        $this->source = $source;
        $this->fallback = $fallback;
        $this->preProcessor = $preProcessor;
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
     * Retrieve and process profile configuration data
     * Processing includes configuration fallback (default, website, store) and placeholder replacement
     *
     * @return array
     */
    public function read()
    {
        $this->source->setProfileId($this->getProfileId());
        return $this->fallback->process(
            $this->preProcessor->process(
                $this->source->get()
            )
        );
    }
}

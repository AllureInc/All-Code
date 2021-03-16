<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model\Config;

/**
 * Class SourceFactory
 * @package Scommerce\CookiePopup\Model\Config
 */
class SourceFactory
{
    /* @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager
    ) {
        $this->objectManager = $objectManager;
    }

    /**
     * @param string $name
     * @param array $data
     * @return \Magento\Framework\Option\ArrayInterface
     */
    public function create($name, array $data = [])
    {
        return $this->objectManager->create($name, $data);
    }
}
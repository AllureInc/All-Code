<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model;

/**
 * Class LinkFactory
 * @package Scommerce\CookiePopup\Model
 */
class LinkFactory
{
    /* @var \Magento\Framework\ObjectManagerInterface */
    private $objectManager;

    /* @var string Instance name to create */
    private $instanceName;

    /**
     * @param \Magento\Framework\ObjectManagerInterface $objectManager
     * @param string $instanceName
     */
    public function __construct(
        \Magento\Framework\ObjectManagerInterface $objectManager,
        $instanceName = 'Scommerce\CookiePopup\Api\Data\LinkInterface'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return \Scommerce\CookiePopup\Api\Data\LinkInterface|\Scommerce\CookiePopup\Model\Data\Link
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}

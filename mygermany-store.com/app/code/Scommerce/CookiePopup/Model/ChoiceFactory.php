<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model;

/**
 * Class ChoiceFactory
 * @package Scommerce\CookiePopup\Model
 */
class ChoiceFactory
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
        $instanceName = 'Scommerce\CookiePopup\Api\Data\ChoiceInterface'
    ) {
        $this->objectManager = $objectManager;
        $this->instanceName = $instanceName;
    }

    /**
     * @param array $data
     * @return \Scommerce\CookiePopup\Api\Data\ChoiceInterface|\Scommerce\CookiePopup\Model\Data\Choice
     */
    public function create(array $data = [])
    {
        return $this->objectManager->create($this->instanceName, $data);
    }
}

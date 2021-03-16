<?php
/**
 * Copyright © 2018 Scommerce Mage. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Scommerce\CookiePopup\Model;

/**
 * Uses for get/set current choice model in registry
 * Instead $coreRegistry->registry('always_forgotten_text_key') use $choiceRegistry->get()
 * Instead $coreRegistry->register('some_key', $model) use $choiceRegistry->set($model)
 * Instead $coreRegistry->unregister('always_forgotten_text_key') use $choiceRegistry->remove()
 *
 * Class ChoiceRegistry
 * @package Scommerce\CookiePopup\Model
 */
class ChoiceRegistry implements \Scommerce\CookiePopup\Api\ChoiceRegistryInterface
{
    /** @var \Magento\Framework\Registry */
    private $registry;

    /**
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Framework\Registry $registry
    ) {
        $this->registry = $registry;
    }

    /**
     * @inheritdoc
     */
    public function get()
    {
        return $this->registry->registry(self::KEY);
    }

    /**
     * @inheritdoc
     */
    public function set(\Scommerce\CookiePopup\Api\Data\ChoiceInterface $model, $graceful = false)
    {
        $this->registry->register(self::KEY, $model, $graceful);
    }

    /**
     * @inheritdoc
     */
    public function remove()
    {
        $this->registry->unregister(self::KEY);
    }

    /**
     * @return \Magento\Framework\Registry
     */
    public function getRegistry()
    {
        return $this->registry;
    }
}

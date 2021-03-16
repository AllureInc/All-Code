<?php
/**
 * Copyright © Cor, Inc. All rights reserved.
 */
namespace Cor\Customizations\Model;

use Magento\Store\Model\StoreManagerInterface;

/**
 * Template model
 */
class Template extends \Magento\Email\Model\Template
{
    /**
     * Store the area associated with a template so that it will be returned by getDesignConfig and getDesignParams
     *
     * @param string $templateId
     * @return $this
     * @throws \Magento\Framework\Exception\MailException
     */
    public function setForcedArea($templateId)
    {
        if (!isset($this->area)) {
            $this->area = $this->emailConfig->getTemplateArea($templateId);
        }
        return $this;
    }
}

<?php
/**
 * Module: Cor_Productmanagement
 * Backend Attribute Model
 * Displays events.
 */
namespace Cor\Productmanagement\Model\Attribute\Source;

class Events extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * Get all options
     * @return array
     */
    public function getAllOptions()
    {
        return $this->_options;
    }
}
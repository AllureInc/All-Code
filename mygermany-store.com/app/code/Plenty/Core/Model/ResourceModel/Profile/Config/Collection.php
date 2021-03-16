<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Profile\Config;

use Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection;
use Plenty\Core\Api\Data\Profile\ConfigInterface;

/**
 * Class Collection
 * @package Plenty\Core\Model\ResourceModel\Profile\Config
 */
class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';

    /**
     * Constructor
     */
    protected function _construct()
    {
        $this->_init(\Plenty\Core\Model\Profile\Config::class, \Plenty\Core\Model\ResourceModel\Profile\Config::class);
    }

    /**
     * Add scope filter to collection
     *
     * @param $scope
     * @param $scopeId
     * @param $section
     * @param null $profileId
     * @return $this
     */
    public function addScopeFilter($scope, $scopeId, $section, $profileId)
    {
        $this->addFieldToFilter(ConfigInterface::PROFILE_ID, (int) $profileId);
        $this->addFieldToFilter(ConfigInterface::SCOPE, $scope);
        $this->addFieldToFilter(ConfigInterface::SCOPE_ID, $scopeId);
        $this->addFieldToFilter(ConfigInterface::PATH, ['like' => $section . '/%']);
        return $this;
    }

    /**
     *  Add path filter
     *
     * @param string $section
     * @return $this
     */
    public function addPathFilter($section)
    {
        $this->addFieldToFilter(ConfigInterface::PATH, ['like' => $section . '/%']);
        return $this;
    }

    /**
     * Add value filter
     *
     * @param int|string $value
     * @return $this
     */
    public function addValueFilter($value)
    {
        $this->addFieldToFilter(ConfigInterface::VALUE, ['like' => $value]);
        return $this;
    }

    /**
     * Add profile filter
     *
     * @param $profileId
     * @return $this
     */
    public function addProfileFilter($profileId)
    {
        $this->addFieldToFilter(ConfigInterface::PROFILE_ID, (int) $profileId);
        return $this;
    }
}
<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Model\ResourceModel\Config;

use Magento\Framework\Model\ResourceModel\Db\AbstractDb;
use Magento\Framework\Model\ResourceModel\Db\Context;
use Magento\Framework\Data\Collection;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Plenty\Core\Model\Config\Source as ConfigSourceModel;
use Plenty\Core\Setup\SchemaInterface;
use Plenty\Core\Api\Data\Config\SourceInterface;

/**
 * Class Source
 * @package Plenty\Core\Model\ResourceModel\Config
 */
class Source extends AbstractDb
{
    /**
     * @var DateTime
     */
    protected $_date;

    protected function _construct()
    {
        $this->_init(SchemaInterface::CORE_CONFIG_SOURCE, SourceInterface::ENTITY_ID);
    }

    /**
     * Source constructor.
     * @param Context $context
     * @param DateTime $date
     * @param string|null $connectionName
     */
    public function __construct(
        Context $context,
        DateTime $date,
        ?string $connectionName = null
    ) {
        $this->_date = $date;
        parent::__construct($context, $connectionName);
    }

    /**
     * @param Collection $responseData
     * @return $this
     * @throws \Exception
     */
    public function saveConfigs(Collection $responseData)
    {
        $configSourceData = [];
        /** @var ConfigSourceModel $item */
        foreach ($responseData as $item) {
            $configSourceData[] = $this->_prepareConfigSourceData($item);
        }

        try {
            $this->getConnection()
                ->insertOnDuplicate($this->getMainTable(), $configSourceData);
        } catch (\Exception $e) {
            throw new \Exception($e->getMessage());
        }

        return $this;
    }

    /**
     * @param ConfigSourceModel $item
     * @return array
     */
    private function _prepareConfigSourceData($item)
    {
        return [
            SourceInterface::ENTRY_ID => $item->getData(SourceInterface::ENTRY_ID),
            SourceInterface::CONFIG_SOURCE => $item->getData(SourceInterface::CONFIG_SOURCE),
            SourceInterface::CONFIG_ENTRIES => serialize($item->getData(SourceInterface::CONFIG_ENTRIES)),
            SourceInterface::CREATED_AT => $item->getData(SourceInterface::CREATED_AT)
                ? $item->getData(SourceInterface::CREATED_AT)
                : $this->_date->gmtDate(),
            SourceInterface::UPDATED_AT => $item->getData(SourceInterface::UPDATED_AT)
                ? $item->getData(SourceInterface::UPDATED_AT)
                : $this->_date->gmtDate(),
            SourceInterface::COLLECTED_AT => $this->_date->gmtDate()
        ];
    }
}
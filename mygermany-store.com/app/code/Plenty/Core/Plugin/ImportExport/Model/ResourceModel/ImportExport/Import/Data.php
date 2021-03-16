<?php
/**
 * Copyright © Soft Commerce Ltd. All rights reserved.
 * See LICENSE.txt for license details.
 */
namespace Plenty\Core\Plugin\ImportExport\Model\ResourceModel\ImportExport\Import;

use Magento\Framework\App\Config\ScopeConfigInterface;

/**
 * Class Data
 * @package Plenty\Core\Plugin\ImportExport\Model\ResourceModel\ImportExport\Import
 */
class Data extends \Magento\ImportExport\Model\ResourceModel\Import\Data implements \IteratorAggregate
{
    /**
     * @var ScopeConfigInterface
     */
    private $_scopeConfig;

    /**
     * Data constructor.
     * @param \Magento\Framework\Model\ResourceModel\Db\Context $context
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     * @param ScopeConfigInterface $scopeConfig
     * @param string|null $connectionName
     */
    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Magento\Framework\Json\Helper\Data $jsonHelper,
        ScopeConfigInterface $scopeConfig,
        ?string $connectionName = null
    ) {
        $this->_scopeConfig = $scopeConfig;
        parent::__construct($context, $jsonHelper, $connectionName);
    }

    /**
     * Resource initialization
     *
     * @return void
     */
    protected function _construct()
    {
        /*
        if ($this->_scopeConfig->isSetFlag('option to log data to temporary db table')) {
            $this->getConnection()->createTemporaryTableLike(
                'importexport_importdata_tmp',
                'importexport_importdata',
                true
            );
            $this->_init('importexport_importdata_tmp', 'id');
        } else {
            parent::_construct();
        } */
        parent::_construct();
    }
}
<?php
/**
 * Mirasvit
 *
 * This source file is subject to the Mirasvit Software License, which is available at https://mirasvit.com/license/.
 * Do not edit or add to this file if you wish to upgrade the to newer versions in the future.
 * If you wish to customize this module for your needs.
 * Please refer to http://www.magentocommerce.com for more information.
 *
 * @category  Mirasvit
 * @package   mirasvit/module-reports
 * @version   1.3.20
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Reports\Reports\Customers;

use Magento\Framework\Stdlib\DateTime;
use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Context;
use Mirasvit\Reports\Model\Config;

class Customers extends AbstractReport
{
    /**
     * @var Config
     */
    protected $config;

    public function __construct(
        Config $config,
        Context $context
    ) {
        $this->config = $config;

        parent::__construct($context);
    }

    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Customers');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('customer_entity');

        //                $this->addFastFilters([
        //                    'customer_entity|entity_id',
        //                ]);

        $this->addDefaultColumns([
            'customer_entity|email',
            'customer_entity|firstname',
            'customer_entity|lastname',
            'customer_entity|created_at',
            'customer_entity|group_id',
            'sales_order|entity_id__cnt',
            'sales_order|products',
        ]);

        //it is used in filter by Products column
        $this->setRequiredColumns([
            'customer_entity|entity_id',
            //            'sales_order|orders_products',
        ]);

        $this->addColumns([
            'customer_entity|entity_id',
            'customer_entity|gender',
            'customer_entity|taxvat',
            'customer_entity|store_id',
            //            'sales_order|last_order_at',
            //            'sales_order|products',
            'sales_order|total_qty_ordered__sum',
            'sales_order|grand_total__sum',
        ]);

        $this->setDimensions(
            ['customer_entity|entity_id']
        )->setDefaultDimension(
            'customer_entity|entity_id'
        );
    }
}
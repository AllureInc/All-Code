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



namespace Mirasvit\Reports\Reports\Catalog;

use Mirasvit\Report\Model\AbstractReport;

class Attribute extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Sales by Attribute');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('catalog_product_entity');

        $this->addFastFilters([
            'sales_order|created_at',
            'sales_order|store_id',
        ]);

        $this->addAvailableFilters([
            'sales_order|status'
        ]);

        $this->setRequiredColumns(['catalog_product_entity|entity_id']);

        $this->setDefaultColumns([
            'catalog_product_entity|attribute',
            'sales_order_item|qty_ordered__sum',
            'sales_order_item|tax_amount__sum',
            'sales_order_item|discount_amount__sum',
            'sales_order_item|amount_refunded__sum',
            'sales_order_item|row_total__sum',
        ]);

        $this->setDefaultDimension('catalog_product_entity|status');

        $this->addDimensions($this->context->getProvider()->getSimpleColumns('catalog_product_entity'));

        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order'));
        $this->addColumns($this->context->getProvider()->getComplexColumns('sales_order_item'));
        $this->addColumns($this->context->getProvider()->getComplexColumns('catalog_product_entity'));

        $this->getGridConfig()
            ->disablePagination();

        $this->getChartConfig()
            ->setDefaultColumns([
                'sales_order_item|row_total__sum',
            ])
            ->setType('pie');
    }
}
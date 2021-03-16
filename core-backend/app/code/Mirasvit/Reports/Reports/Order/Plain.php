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



namespace Mirasvit\Reports\Reports\Order;

use Magento\Framework\DataObject;
use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Query\Select;

class Plain extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Orders');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('sales_order');

        $this->addFastFilters([
            'sales_order|created_at',
            'sales_order|store_id',
        ]);

        $this->setRequiredColumns([
            'sales_order|entity_id',
            'sales_order|customer_id',
        ]);

        $this->setDefaultColumns([
            'sales_order|increment_id',
            'sales_order|customer_name',
            'sales_order|customer_group_id',
            'sales_order|created_at',
            'sales_order|status',
            'sales_order_payment|method',
            'sales_order|total_qty_ordered',
            'sales_order|discount_amount',
            'sales_order|shipping_amount',
            'sales_order|tax_amount',
            'sales_order|gross_margin',
            'sales_order|grand_total',
            'sales_order|products',
        ]);

        $this->setDefaultDimension('sales_order|increment_id');
    }

    /**
     * {@inheritdoc}
     */
    public function getActions($item)
    {
        return [
            [
                'label' => __('View Order'),
                'href'  => $this->context->urlManager->getUrl(
                    'sales/order/view',
                    ['order_id' => $item['sales_order|entity_id']]
                ),
            ],
            [
                'label' => __('View Customer'),
                'href'  => $this->context->urlManager->getUrl(
                    'customer/index/edit',
                    ['id' => $item['sales_order|customer_id']]
                ),
            ],
        ];
    }
}
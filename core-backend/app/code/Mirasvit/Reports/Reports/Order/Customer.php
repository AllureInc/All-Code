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
use Mirasvit\Report\Model\Query\Select;

class Customer extends Overview
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Sales by Customer');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        parent::init();

        $this->setRequiredColumns(['customer_entity|entity_id']);

        $this->setDimensions(['customer_entity|email'])
            ->setDefaultDimension(
                'customer_entity|email'
            );

        $this->getChartConfig()
            ->setType(false);

        return $this;
    }

    /**
     * {@inheritdoc}
     */
    public function getActions($item)
    {
        return [
            [
                'label' => __('View Customer'),
                'href'  => $this->context->urlManager->getUrl(
                    'customer/index/edit',
                    ['id' => $item['customer_entity|entity_id']]
                ),
            ],
            [
                'label' => __('View Orders'),
                'href'  => $this->context->urlManager->getUrl(
                    'reports/report/view',
                    [
                        'report' => 'Order_Plain',
                        '_query' => [
                            'filters[sales_order|customer_id][from]' => $item['customer_entity|entity_id'],
                            'filters[sales_order|customer_id][to]'   => $item['customer_entity|entity_id'],
                        ],
                    ]
                ),
            ],
        ];
    }
}

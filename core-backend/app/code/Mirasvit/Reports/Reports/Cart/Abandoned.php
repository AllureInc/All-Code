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



namespace Mirasvit\Reports\Reports\Cart;

use Magento\Framework\DataObject;
use Mirasvit\Report\Api\Data\Query\ColumnInterface;
use Mirasvit\Report\Model\AbstractReport;
use Mirasvit\Report\Model\Query\Select;

class Abandoned extends AbstractReport
{
    /**
     * {@inheritdoc}
     */
    public function getName()
    {
        return __('Abandoned Carts');
    }

    /**
     * {@inheritdoc}
     */
    public function init()
    {
        $this->setTable('quote');

        $this->addFastFilters([
            'quote|created_at',
            'quote|store_id',
        ]);

        $this->setRequiredColumns([
            'quote|entity_id',
            'quote|customer_id',
            'quote|is_active',
        ]);

        $this->setDefaultColumns([
            'quote|customer_name',
            'quote|created_at',
            'quote|items_qty',
            'quote|products'
        ]);

        $this->addAvailableFilters([
            'quote_item|product_id'
        ]);

        $this->setDefaultDimension('quote|entity_id');

        $this->setDefaultFilters([
            ['quote|is_active', '1', 'eq'],
        ]);
    }

    /**
     * {@inheritdoc}
     */
    public function getActions($item)
    {
        if (isset($item['quote|customer_id'])) {
            return [
                [
                    'label' => __('View Customer'),
                    'href'  => $this->context->urlManager->getUrl(
                        'customer/index/edit',
                        ['id' => $item['quote|customer_id']]
                    ),
                ],
            ];
        }

    }
}
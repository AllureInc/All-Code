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
 * @package   mirasvit/module-dashboard
 * @version   1.2.22
 * @copyright Copyright (C) 2018 Mirasvit (https://mirasvit.com/)
 */



namespace Mirasvit\Dashboard\Ui\Block\Form\Modifier;

use Magento\Ui\DataProvider\Modifier\ModifierInterface;
use Magento\Framework\Stdlib\ArrayManager;
use Mirasvit\Dashboard\DataSource\Report\Source\ReportsColumn;
use Mirasvit\Dashboard\Ui\ComponentTrait;

class ReportRendererModifier implements ModifierInterface
{
    use ComponentTrait;

    /**
     * @var ArrayManager
     */
    private $arrayManager;
    /**
     * @var ReportsColumn
     */
    private $columnSource;

    public function __construct(
        ReportsColumn $columnSource,
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
        $this->columnSource = $columnSource;
    }

    public function modifyData(array $data)
    {
        return $data;
    }

    public function modifyMeta(array $meta)
    {
        $meta = $this->arrayManager->merge(
            'reports/children/container/children/report/children/report.data.columns/arguments/data',
            $meta,
            [
                'config' => [
                    'columns' => $this->columnSource->toOptionArray()
                ],
            ]
        );

        return $meta;
    }
}
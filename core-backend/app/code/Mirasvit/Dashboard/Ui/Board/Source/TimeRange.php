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



namespace Mirasvit\Dashboard\Ui\Board\Source;

use Magento\Framework\Option\ArrayInterface;
use Mirasvit\Report\Service\DateService;

class TimeRange implements ArrayInterface
{
    /**
     * @var DateService
     */
    private $dateService;

    public function __construct(
        DateService $dateService
    ) {
        $this->dateService = $dateService;
    }

    /**
     * {@inheritdoc}
     */
    public function toOptionArray()
    {
        $data = [];
        foreach ($this->dateService->getIntervals(true, true) as $identifier => $label) {
            $data[] = [
                'label' => $label,
                'value' => $identifier,
            ];
        }

        return $data;
    }
}

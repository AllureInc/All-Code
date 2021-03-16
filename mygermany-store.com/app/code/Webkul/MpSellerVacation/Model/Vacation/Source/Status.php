<?php

/**
 * Webkul Software
 *
 * @category  Webkul
 * @package   Webkul_MpSellervacation
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MpSellerVacation\Model\Vacation\Source;

use Magento\Framework\Data\OptionSourceInterface;

class Status implements OptionSourceInterface
{

    /**
     * @var Webkul\MpSellerVacation\Model\Vacation
     */
    protected $_vacation;

    /**
     *
     * @param \Webkul\MpSellerVacation\Model\Vacation $vacation
     */
    public function __construct(\Webkul\MpSellerVacation\Model\Vacation $vacation)
    {
        $this->_vacation = $vacation;
    }


    /**
     * Get options
     *
     * @return array
     */
    public function toOptionArray()
    {
        $availableOptions = $this->_vacation->getAvailableStatuses();
        $options = [];
        foreach ($availableOptions as $key => $value) {
            $options[] = [
                'label' => $value,
                'value' => $key,
            ];
        }
        return $options;
    }
}

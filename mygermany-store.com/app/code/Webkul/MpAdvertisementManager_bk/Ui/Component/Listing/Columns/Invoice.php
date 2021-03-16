<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_GiftCard
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Invoice extends Column
{

    /**
     * Constructor.
     *
     * @param ContextInterface                         $context
     * @param UiComponentFactory                       $uiComponentFactory
     * @param array                                    $components
     * @param array                                    $data
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = []
    ) {
        parent::__construct($context, $uiComponentFactory, $components, $data);
    }

    /**
     * Prepare Data Source.
     *
     * @param array $dataSource
     *
     * @return array
     */
    public function prepareDataSource(array $dataSource)
    {
        if (isset($dataSource['data']['items'])) {
            foreach ($dataSource['data']['items'] as &$item) {
                if ($item['invoice_generated'] == 0) {
                    $item['invoice_generated'] = '<div style="background:#f9d4d4;border:1px solid;border-color:#e22626;padding: 0 7px;text-align:center;text-transform: uppercase;color:#e22626;font-weight:bold;" title="Invoice has been not generated">No</div>';
                } elseif ($item['invoice_generated'] == 1) {
                    $item['invoice_generated'] = '<div style="background:#d0e5a9;border:1px solid;border-color:#5b8116;padding: 0 7px;text-align:center;text-transform: uppercase;color:#185b00;font-weight:bold;" title="Invoice has been generated">Yes</div>';
                }
            }
        }
        return $dataSource;
    }
}

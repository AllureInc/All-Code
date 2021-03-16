<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul
 * @copyright Copyright (c) 2010-2016 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Ui\Component\Listing\Columns;

use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\UiComponent\ContextInterface;
use Magento\Framework\View\Element\UiComponentFactory;
use Magento\Ui\Component\Listing\Columns\Column;

class Expire extends Column
{
    /**
     * @var \Webkul\MpAdvertisementManager\Helper\Order
     */
    
    protected $_orderHelper;
    /**
     * Constructor.
     *
     * @param ContextInterface                            $context
     * @param UiComponentFactory                          $uiComponentFactory
     * @param array                                       $components
     * @param array                                       $data
     * @param \Webkul\MpAdvertisementManager\Helper\Order $orderHelper
     */
    public function __construct(
        ContextInterface $context,
        UiComponentFactory $uiComponentFactory,
        array $components = [],
        array $data = [],
        \Webkul\MpAdvertisementManager\Helper\Order $orderHelper
    ) {
        $this->_orderHelper = $orderHelper;
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
                $val = $this->_orderHelper->validateDate($item['created_at'], $item['valid_for']);
                if (!$val) {
                    $item['expire'] = '<div style="background:#f9d4d4;border:1px solid;border-color:#e22626;padding: 0 7px;text-align:center;text-transform: uppercase;color:#e22626;font-weight:bold;" title="Ad is expired">Expire</div>';
                } else {
                    $item['expire'] = '<div style="background:#d0e5a9;border:1px solid;border-color:#5b8116;padding: 0 7px;text-align:center;text-transform: uppercase;color:#185b00;font-weight:bold;" title="Ad is active">Active</div>';
                }
            }
        }
        return $dataSource;
    }
}

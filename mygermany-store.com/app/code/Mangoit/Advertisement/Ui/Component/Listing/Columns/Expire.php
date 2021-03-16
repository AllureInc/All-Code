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

namespace Mangoit\Advertisement\Ui\Component\Listing\Columns;


class Expire extends \Webkul\MpAdvertisementManager\Ui\Component\Listing\Columns\Expire
{

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
                $val = $this->_orderHelper->validateDate($item['mis_approval_date'], $item['valid_for']);
                if ($val && $item['mis_approval_status'] == 1) {
                    $item['expire'] = '<div style="background:#d0e5a9;border:1px solid;border-color:#5b8116;padding: 0 7px;text-align:center;text-transform: uppercase;color:#185b00;font-weight:bold;" title="Ad is active">Active</div>';
                } elseif($item['mis_approval_status'] == 0) {
                    $item['expire'] = '<div style="background:#f9ead4;border:1px solid;border-color:#e26b26;padding: 0 7px;text-align:center;text-transform: uppercase;color:#e26b26;font-weight:bold;" title="Ad approval is pending.">Pending</div>';
                } else {
                    $item['expire'] = '<div style="background:#f9d4d4;border:1px solid;border-color:#e22626;padding: 0 7px;text-align:center;text-transform: uppercase;color:#e22626;font-weight:bold;" title="Ad is expired">Expire</div>';
                }
            }
        }
        return $dataSource;
    }
}

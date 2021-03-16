<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */

namespace Solrbridge\Search\Helper;

/**
 * Contact base helper
 */
class Index extends \Magento\Search\Helper\Data
{
    /**
     * Calculate percent finished
     * @param int $total
     * @param int $part
     * @return float
     */
    public static function calculatePercent($total, $part)
    {
        if ($total > 0) {
            return round((($part * 100) / $total));
        }
        return 0;
    }
}

<?php
/**
 * Copyright © 2015 Solrbridge. All rights reserved.
 * See LICENSE.txt for more details.
 */

namespace Solrbridge\Search\Helper;

use Solrbridge\Search\Helper\System;

/**
 * This class is used widely in the module to get System resources like
 * Object manager, registry, store manager,....
 */
class Filter
{
    public static function getParam($key)
    {
        $request = System::getRequest();
        $value = null;
        $filterQuery = $request->getParam('fq');
        if (is_array($filterQuery) && isset($filterQuery[$key])) {
            $value = $filterQuery[$key];
        }
        return $value;
    }
}

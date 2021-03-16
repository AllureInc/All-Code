<?php
/**
 * Webkul Software
 *
 * @category Webkul
 * @package Webkul_Walletsystem
 * @author Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license https://store.webkul.com/license.html
 */

namespace Webkul\Walletsystem\Model\ResourceModel\Walletcreditamount;

use \Webkul\Walletsystem\Model\ResourceModel\AbstractCollection;

class Collection extends AbstractCollection
{
    /**
     * @var string
     */
    protected $_idFieldName = 'entity_id';
    /**
     *
     * @return void
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\Walletsystem\Model\Walletcreditamount',
            'Webkul\Walletsystem\Model\ResourceModel\Walletcreditamount'
        );
        $this->_map['fields']['entity_id'] = 'main_table.entity_id';
    }

    public function addStoreFilter($store, $withAdmin = true)
    {
        if (!$this->getFlag('store_filter_added')) {
            $this->performAddStoreFilter($store, $withAdmin);
        }
        return $this;
    }
}

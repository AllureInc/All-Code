<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Model\ResourceModel\Accounts;

class Collection extends \Magento\Framework\Model\ResourceModel\Db\Collection\AbstractCollection
{
    /**
     * @var string
     */

    protected $_idFieldName = 'entity_id';
    
    /**
     * Define resource model.
     */
    protected function _construct()
    {
        $this->_init(
            'Webkul\MpAmazonConnector\Model\Accounts',
            'Webkul\MpAmazonConnector\Model\ResourceModel\Accounts'
        );
    }
}

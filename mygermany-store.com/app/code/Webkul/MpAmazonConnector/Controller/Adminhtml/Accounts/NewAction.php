<?php
/**
 * @category   Webkul
 * @package    Webkul_MpAmazonConnector
 * @author     Webkul Software Private Limited
 * @copyright  Copyright (c)  Webkul Software Private Limited (https://webkul.com)
 * @license    https://store.webkul.com/license.html
 */
namespace Webkul\MpAmazonConnector\Controller\Adminhtml\Accounts;

use Webkul\MpAmazonConnector\Controller\Adminhtml\Accounts;

class NewAction extends Accounts
{
    /**
     * @return void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}

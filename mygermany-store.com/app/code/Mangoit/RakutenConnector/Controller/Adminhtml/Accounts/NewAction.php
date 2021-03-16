<?php
/**
 * @category   Package
 * @package    Package_RakutenConnector
 * @author     Author
 * @copyright  Copyright (c)  Author
 * @license    license
 */
namespace Mangoit\RakutenConnector\Controller\Adminhtml\Accounts;

use Mangoit\RakutenConnector\Controller\Adminhtml\Accounts;

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

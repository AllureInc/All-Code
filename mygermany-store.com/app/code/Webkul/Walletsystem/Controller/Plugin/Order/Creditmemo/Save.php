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

namespace Webkul\Walletsystem\Controller\Plugin\Order\Creditmemo;

class Save
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;

    /**
     * @param MagentoFrameworkAppRequestHttp $request
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request
    ) {
        $this->_request = $request;
    }
    public function beforeExecute(
        \Magento\Sales\Controller\Adminhtml\Order\Creditmemo\Save $subject
    ) {
        $params = $this->_request->getPost();
        $params->creditmemo['do_offline']=1;
        $this->_request->setPost($params);
        $params = $this->_request->getPost();
        return;
    }
}

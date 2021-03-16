<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MarketplacePreorder
 * @author    Webkul
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\MarketplacePreorder\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\App\RequestInterface;

class PreorderConfigSaveAfter implements ObserverInterface
{
    const CRON_STRING_PATH = 'mppreorder/cron/schedule';
    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var \Webkul\MarketplacePreorder\Helper\Data
     */
    protected $_preorderHelper;

    /**
     * @var \Magento\Config\Model\ResourceModel\Config
     */
    protected $_resourceConfig;

    /**
     * @param RequestInterface $request
     * @param \Webkul\Preorder\Helper\Data $preorderHelper
     */
    public function __construct(
        RequestInterface $request,
        \Webkul\MarketplacePreorder\Helper\Data $preorderHelper,
        \Magento\Config\Model\ResourceModel\Config $resourceConfig
    ) {
        $this->_request = $request;
        $this->_preorderHelper = $preorderHelper;
        $this->_resourceConfig = $resourceConfig;
    }

    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $data = $this->_request->getParams();
        $helper = $this->_preorderHelper;
        if ($data['section'] == 'mppreorder') {
            $this->_resourceConfig->saveConfig(
                self::CRON_STRING_PATH,
                0,
                'default',
                0
            );
        }

    }
}

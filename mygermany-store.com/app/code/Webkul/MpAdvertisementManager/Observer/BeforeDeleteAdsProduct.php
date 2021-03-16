<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_MpAdvertisementManager
 * @author    Webkul Software Private Limited
 * @copyright Copyright (c) 2010-2017 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */

namespace Webkul\MpAdvertisementManager\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Event\Observer;
use Magento\Framework\App\RequestInterface;

class BeforeDeleteAdsProduct implements ObserverInterface
{

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;

    /**
     * @var \Magento\Framework\App\State
     */
    private $state;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * __construct function
     *
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     * @param \Magento\Framework\App\ResponseFactory        $responseFactory
     * @param \Magento\Framework\UrlInterface               $url
     * @param \Magento\Framework\App\State                  $state
     */
    public function __construct(
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\App\State $state
    ) {
        $this->_messageManager = $messageManager;
        $this->responseFactory = $responseFactory;
        $this->url = $url;
        $this->state = $state;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        if ($observer->getObject()->getSku() == 'wk_mp_ads_plan' && $this->state->getAreaCode() == 'adminhtml') {
            $this->_messageManager->addError(__('Delete operation is forbidden for product under sku name wk_mp_ads_plan'));
            $redirectUrl = $this->url->getUrl('catalog/product/index');
            $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
            $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
            return $this;
        }
    }
}

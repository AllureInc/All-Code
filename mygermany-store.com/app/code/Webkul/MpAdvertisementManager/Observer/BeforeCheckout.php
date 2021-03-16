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

class BeforeCheckout implements ObserverInterface
{

    /**
     * @var \Webkul\MpAdvertisementManager\Model\BlockFactory
     */
    private $block;

    /**
     * @var \Magento\Framework\App\ResponseFactory
     */
    private $responseFactory;

    /**
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;

    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $url;

    /**
     * @var \Magento\Framework\Json\Helper\Data
     */
    private $jsonHelper;

    /**
     * __construct function
     *
     * @param \Webkul\MpAdvertisementManager\Model\BlockFactory $block
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param \Magento\Checkout\Model\Cart $cart
     * @param \Magento\Framework\App\ResponseFactory $responseFactory
     * @param \Magento\Framework\UrlInterface $url
     * @param \Magento\Framework\Json\Helper\Data $jsonHelper
     */
    public function __construct(
        \Webkul\MpAdvertisementManager\Model\BlockFactory $block,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Checkout\Model\Cart $cart,
        \Magento\Framework\App\ResponseFactory $responseFactory,
        \Magento\Framework\UrlInterface $url,
        \Magento\Framework\Json\Helper\Data $jsonHelper
    ) {
        $this->block = $block;
        $this->responseFactory = $responseFactory;
        $this->cart = $cart;
        $this->messageManager = $messageManager;
        $this->url = $url;
        $this->jsonHelper = $jsonHelper;
    }

    /**
     * This is the method that fires when the event runs.
     *
     * @param Observer $observer
     */
    public function execute(Observer $observer)
    {
        try{
            foreach($this->cart->getQuote()->getAllVisibleItems() as $item){
                if ($item->getSku() == 'wk_mp_ads_plan') {
                    $option = $this->jsonHelper->jsonDecode($item->getOptionByCode('info_buyRequest')->getValue());
                    $blockId = '';
                    foreach($option as $data) {
                        $blockId = $data['block'];
                    }
                    if (!$this->checkBlockExist($blockId)) {
                        $errorMessage = $this->createErrorMessage($item);
                        $this->messageManager->addError(__($errorMessage));
                        $redirectUrl = $this->url->getUrl('checkout/cart/index');
                        $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
                        $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
                        return $this;
                    }
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addError(__($e->getMessage()));
            $redirectUrl = $this->url->getUrl('checkout/cart/index');
            $this->responseFactory->create()->setRedirect($redirectUrl)->sendResponse();
            $observer->getControllerAction()->getResponse()->setRedirect($redirectUrl);
            return $this;
        }
    }

    /**
     * checkBlockExist function check purchased block exist or not.
     *
     * @param string $blockId
     * @return boolean
     */
    public function checkBlockExist($blockId = ''){
        return $this->block->create()->getCollection()->addFieldToFilter(
            'id', ['in' => $blockId])->getSize();
    }

    /**
     * createErrorMessage function
     *
     * @param [type] $item
     * @return void
     */
    public function createErrorMessage($item){
        foreach($item->getOptions() as $option){
            $itemOptions = json_decode($option['value'], true);
            break;
        }
        $AdsPosition = '';
        $blockName = '';
        foreach($itemOptions as $option) {
            if ($option['label'] == 'Ad Position') {
                $AdsPosition = $option['value'];
            }
            if ($option['label'] == 'Block') {
                $blockName = $option['value'];
            }
        }
        return "The Ads block $blockName does not exist for ads position $AdsPosition";
    }
}

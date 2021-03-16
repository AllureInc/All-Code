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

namespace Webkul\Walletsystem\Observer;

use Magento\Framework\Event\ObserverInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;

class WebsiteSaveAfter implements ObserverInterface
{
    /**
     * @var \Magento\Framework\App\Request\Http
     */
    protected $_request;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    protected $_messageManager;
    /**
     * @var Webkul\Walletsystem\Helper\Data
     */
    protected $_walletHelper;
    /**
     * @var ProductRepositoryInterface
     */
    protected $productRepo;
    /**
     * @var \Magento\Store\Model\WebsiteRepository
     */
    protected $websiteRepo;

    /**
     * @param \Magento\Framework\App\Request\Http           $request
     * @param \Magento\Framework\Message\ManagerInterface   $messageManager
     * @param \Webkul\Walletsystem\Helper\Data              $walletHelper
     * @param ProductRepositoryInterface                    $productRepo
     * @param \Magento\Store\Model\WebsiteRepository        $websiteRepo
     */
    public function __construct(
        \Magento\Framework\App\Request\Http $request,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        ProductRepositoryInterface $productRepo,
        \Magento\Store\Model\WebsiteRepository $websiteRepo
    ) {
        $this->_request = $request;
        $this->_messageManager = $messageManager;
        $this->_walletHelper = $walletHelper;
        $this->productRepo = $productRepo;
        $this->websiteRepo = $websiteRepo;
    }
    /**
     * add website id to wallet amount product.
     *
     * @param \Magento\Framework\Event\Observer $observer
     */
    public function execute(\Magento\Framework\Event\Observer $observer)
    {
        $helper = $this->_walletHelper;
        $paramData = $this->_request->getParams();
        $website = $this->websiteRepo->get($paramData['website']['code']);
        if (!empty($paramData['store_type']) || !empty($paramData['store_action'])) {
            if ($paramData['store_type'] == 'website' && !empty($website)) {
                if ($website->getWebsiteId()) {
                    $websiteId = $website->getWebsiteId();
                    $walletProductSku = $helper::WALLET_PRODUCT_SKU;
                    $product = $this->productRepo->get($walletProductSku);
                    $websiteIds = $product->getWebsiteIds();
                    if (!in_array($websiteId, $websiteIds)) {
                        $websiteIds[] = $websiteId;
                        $product->setWebsiteIds($websiteIds);
                        $product->save();
                    }
                }
            }
        }
    }
}

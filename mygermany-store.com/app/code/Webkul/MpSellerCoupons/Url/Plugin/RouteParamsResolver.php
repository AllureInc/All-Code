<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Webkul\MpSellerCoupons\Url\Plugin;

use \Magento\Store\Model\Store;
use \Magento\Store\Model\ScopeInterface as StoreScopeInterface;

/**
 * Plugin for \Magento\Framework\Url\RouteParamsResolver
 */
class RouteParamsResolver
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var \Magento\Framework\Url\QueryParamsResolverInterface
     */
    protected $queryParamsResolver;

    /**
     * Initialize dependencies.
     *
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\App\RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Webkul\MpSellerCoupons\Helper\Data $helper,
        \Magento\Framework\Url\QueryParamsResolverInterface $queryParamsResolver
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->helper = $helper;
        $this->storeManager = $storeManager;
        $this->request = $request;
        $this->queryParamsResolver = $queryParamsResolver;
    }
    public function beforeSetRouteParams(
        \Magento\Framework\Url\RouteParamsResolver $subject,
        array $data,
        $unsetOldParams = true
    ) {
        $data = $this->request->getPost();
        if (isset($data['___store'])) {
            $toStore = $data['___store'];
            $stores = $this->storeManager->getStores(true, false);
            foreach ($stores as $store) {
                if ($store->getCode() === $toStore) {
                    $currency = $store->getCurrentCurrency()->getCode();
                    $this->helper->updateCouponValue($currency);
                }
            }
        }
    }
}

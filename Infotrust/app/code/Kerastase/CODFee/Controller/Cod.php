<?php

namespace Kerastase\CODFee\Controller;

use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Checkout\Model\Cart as CustomerCart;

abstract class Cod extends \Magento\Framework\App\Action\Action
{
    /**
     * Enabled config path
     */
    const XML_PATH_ENABLED = 'payment/cashondelivery/active';
    protected $cart;
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        CustomerCart $cart

    ) {
        parent::__construct($context);
        $this->scopeConfig          = $scopeConfig;
        $this->cart = $cart;
    }

    /**
     * Dispatch request
     *
     * @param RequestInterface $request
     * @return \Magento\Framework\App\ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function dispatch(RequestInterface $request)
    {
        if ( ! $this->scopeConfig->isSetFlag(self::XML_PATH_ENABLED, ScopeInterface::SCOPE_STORE)) {
            throw new NotFoundException(__('Page not found.'));
        }
        return parent::dispatch($request);
    }
}
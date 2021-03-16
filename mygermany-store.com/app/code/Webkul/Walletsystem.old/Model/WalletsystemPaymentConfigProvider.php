<?php
/**
 * Webkul Software.
 *
 * @category  Webkul
 * @package   Webkul_Walletsystem
 * @author    Webkul
 * @copyright Copyright (c) 2010-2018 Webkul Software Private Limited (https://webkul.com)
 * @license   https://store.webkul.com/license.html
 */
namespace Webkul\Walletsystem\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\Escaper;
use Magento\Payment\Helper\Data as PaymentHelper;
/**
 * @SuppressWarnings(PHPMD.CouplingBetweenObjects)
 * @SuppressWarnings(PHPMD.TooManyFields)
 */
class WalletsystemPaymentConfigProvider implements WalletPaymentConfigProviderInterface
{
    /**
     * @var methodCode
     */
    protected $methodCode = PaymentMethod::CODE;
    /**
     * @var _method
     */
    protected $_method;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $_urlBuilder;
    /**
     * @var Escaper
     */
    protected $escaper;

    /**
     * @param PaymentHelper                    $paymentHelper
     * @param \Webkul\Walletsystem\Helper\Data $helper
     * @param \Magento\Framework\UrlInterface  $urlBuilder
     * @param Escaper                          $escaper
     */
    public function __construct(
        PaymentHelper $paymentHelper,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\UrlInterface $urlBuilder,
        Escaper $escaper
    ) {
        $this->escaper = $escaper;
        $this->_helper = $helper;
        $this->_urlBuilder = $urlBuilder;
        $this->_method = $paymentHelper->getMethodInstance($this->methodCode);
    }

    public function getConfig()
    {
        $config = [];
        $image = '';
        $walletamount = '';
        $ajaxUrl = '';
        $walletstatus = '';
        $leftToPay = '';
        $leftinwallet = '';
        $currencysymbol = '';
        $getcurrentcode = '';
        $walletformatamount = '';
        $grandTotal = '';
        if ($this->_method->isAvailable()) {
            $image = $this->getLoaderImage();
            $walletformatamount = $this->_helper->getformattedPrice($this->getWalletamount());
            $walletamount = $this->getWalletamount();
            $ajaxUrl = $this->getAjaxUrl();
            $walletstatus = $this->getWalletStatus();
            $leftToPay = $this->getLeftToPay();
            $leftinwallet = $this->getLeftInWallet();
            $currencysymbol = $this->getCurrencySymbol();
            $getcurrentcode = $this->_helper->getCurrentCurrencyCode();
            $grandTotal = $this->_helper->getGrandTotal();
        }
        $config['walletsystem']['loaderimage'] = $image;
        $config['walletsystem']['getcurrentcode'] = $getcurrentcode;
        $config['walletsystem']['walletformatamount'] = $walletformatamount;
        $config['walletsystem']['walletamount'] = $walletamount;
        $config['walletsystem']['ajaxurl'] = $ajaxUrl;
        $config['walletsystem']['walletstatus'] = $walletstatus;
        $config['walletsystem']['leftamount'] = $leftToPay;
        $config['walletsystem']['leftinwallet'] = $leftinwallet;
        $config['walletsystem']['currencysymbol'] = $currencysymbol;
        $config['walletsystem']['grand_total'] = $grandTotal;

        return $config;
    }
    protected function getLoaderImage()
    {
        return $this->_method->getLoaderImage();
    }
    protected function getWalletamount()
    {
        return $this->_helper->getWalletTotalAmount(0);
    }
    protected function getAjaxUrl()
    {
        return $this->_urlBuilder->getUrl('walletsystem/index/applypaymentamount');
    }
    protected function getWalletStatus()
    {
        return $this->_helper->getWalletStatus();
    }
    protected function getLeftToPay()
    {
        return $this->_helper->getlefToPayAmount();
    }
    protected function getLeftInWallet()
    {
        return $this->_helper->getLeftInWallet();
    }
    protected function getCurrencySymbol()
    {
        return $this->_helper->getCurrencySymbol($this->_helper->getCurrentCurrencyCode());
    }
}

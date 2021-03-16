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

namespace Webkul\Walletsystem\Model;

use Magento\Payment\Model\Method\AbstractMethod;
use Magento\Sales\Model\Order\Payment;
use Magento\Store\Model\ScopeInterface;

class PaymentMethod extends AbstractMethod
{
    const CODE = 'walletsystem';

    /**
     * @var string
     */
    protected $_code = self::CODE;
    /**
     * @var \Webkul\Walletsystem\Helper\Data
     */
    protected $_helper;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canAuthorize = true;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_isInitializeNeeded = true;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefund = true;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canRefundInvoicePartial = true;
    /**
     * Availability option.
     *
     * @var bool
     */
    protected $_canUseInternal = false;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    protected $_storeManager;
    /**
     * Bank Transfer payment block paths
     *
     * @var string
     */
    protected $_formBlockType = 'Webkul\Walletsystem\Block\Form\Walletsystem';

    /**
     * @param \Magento\Framework\Model\Context                        $context
     * @param \Magento\Framework\Registry                             $registry
     * @param \Magento\Framework\Api\ExtensionAttributesFactory       $extensionFactory
     * @param \Magento\Framework\Api\AttributeValueFactory            $customAttributeFactory
     * @param \Magento\Payment\Helper\Data                            $paymentData
     * @param \Magento\Framework\App\Config\ScopeConfigInterface      $scopeConfig
     * @param \Magento\Payment\Model\Method\Logger                    $logger
     * @param \Magento\Store\Model\StoreManagerInterface              $storeManager
     * @param \Magento\Framework\Model\ResourceModel\AbstractResource $resource
     * @param \Magento\Framework\Data\Collection\AbstractDb           $resourceCollection
     * @param array                                                   $data
     */
    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Webkul\Walletsystem\Helper\Data $helper,
        \Magento\Framework\Api\ExtensionAttributesFactory $extensionFactory,
        \Magento\Framework\Api\AttributeValueFactory $customAttributeFactory,
        \Magento\Payment\Helper\Data $paymentData,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Payment\Model\Method\Logger $logger,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct(
            $context,
            $registry,
            $extensionFactory,
            $customAttributeFactory,
            $paymentData,
            $scopeConfig,
            $logger,
            $resource,
            $resourceCollection,
            $data
        );
        $this->_helper = $helper;
        $this->_storeManager = $storeManager;
    }

    /**
     * Authorize payment.
     *
     * @param \Magento\Framework\DataObject|\Magento\Payment\Model\InfoInterface|Payment $payment
     * @param float                                                                      $amount
     *
     * @return $this
     */
    public function authorize(\Magento\Payment\Model\InfoInterface $payment, $amount)
    {
        return $this;
    }
    public function getConfigPaymentAction()
    {
        return $this->_scopeConfig->getValue(
            'payment/walletsystem/payment_action',
            ScopeInterface::SCOPE_STORE,
            $this->_storeManager->getStore()->getStoreId()
        );
    }

    public function isAvailable(\Magento\Quote\Api\Data\CartInterface $quote = null)
    {
        return $this->_helper->getPaymentisEnabled();
    }
    public function getLoaderImage()
    {
        return $this->getViewFileUrl('Webkul_Walletsystem::images/loader.gif');
    }
}

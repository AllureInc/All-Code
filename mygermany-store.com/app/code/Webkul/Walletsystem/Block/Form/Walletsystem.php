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
namespace Webkul\Walletsystem\Block\Form;

/**
 * Block for Bank Transfer payment method form
 */
class Walletsystem extends \Magento\OfflinePayments\Block\Form\AbstractInstruction
{
    /**
     * Bank transfer template
     *
     * @var string
     */
    protected $_template = 'form/walletsystem.phtml';
    /**
     * @var array
     */
    protected $jsLayout;

    /**
     * @var \Webkul\Knockout\Model\WalletPaymentConfigProvider
     */
    protected $configProvider;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Webkul\Walletsystem\Model\WalletPaymentConfigProvider $configProvider,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->jsLayout = isset($data['jsLayout']) && is_array($data['jsLayout']) ? $data['jsLayout'] : [];
        $this->configProvider = $configProvider;
    }

    /**
     * @return string
     */
    public function getJsLayout()
    {
        return \Zend_Json::encode($this->jsLayout);
    }

    public function getCustomConfig()
    {
        return $this->configProvider->getConfig();
    }
}

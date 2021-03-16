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
namespace Webkul\Walletsystem\Ui\Component\DataProvider;

use Magento\Framework\Api\AttributeValueFactory;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\App\ObjectManager;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;
use Webkul\Walletsystem\Model\Walletcreditrules;

/**
 * Class Document
 */
class DocumentCreditRule extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     */
    private static $statusAttributeCode = 'status';
    /**
     * @var string
     */
    private static $basedOnAttributeCode = 'based_on';

    /**
     * @var walletHelper
     */
    private $walletHelper;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Document constructor.
     * @param AttributeValueFactory $attributeValueFactory
     * @param walletHelper $walletHelper
     * @param StoreManagerInterface $storeManager
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        AttributeValueFactory $attributeValueFactory,
        \Webkul\Walletsystem\Helper\Data $walletHelper,
        StoreManagerInterface $storeManager,
        ScopeConfigInterface $scopeConfig = null
    ) {
        parent::__construct($attributeValueFactory);
        $this->walletHelper = $walletHelper;
        $this->storeManager = $storeManager;
        $this->scopeConfig = $scopeConfig ?: ObjectManager::getInstance()->create(ScopeConfigInterface::class);
    }

    /**
     * @inheritdoc
     */
    public function getCustomAttribute($attributeCode)
    {
        switch ($attributeCode) {
            case self::$statusAttributeCode:
                $this->setStatusValue();
                break;
            case self::$basedOnAttributeCode:
                $this->setBasedOnValue();
                break;
        }
        return parent::getCustomAttribute($attributeCode);
    }

    private function setStatusValue()
    {
        $value = $this->getData(self::$statusAttributeCode);
        $valueText = $value == Walletcreditrules::WALLET_CREDIT_RULE_STATUS_ENABLE ? __('Enabled') : __('Disabled');
        $this->setCustomAttribute(self::$statusAttributeCode, $valueText);
    }
    private function setBasedOnValue()
    {
        $value = $this->getData(self::$basedOnAttributeCode);
        $valueText = $value == Walletcreditrules::WALLET_CREDIT_RULE_BASED_ON_PRODUCT ? __('On Product') : __('On Cart');
        $this->setCustomAttribute(self::$basedOnAttributeCode, $valueText);
    }
}

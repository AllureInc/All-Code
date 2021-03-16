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
use Webkul\Walletsystem\Model\Wallettransaction;

/**
 * Class Document
 */
class Document extends \Magento\Framework\View\Element\UiComponent\DataProvider\Document
{
    /**
     * @var string
     */
    private static $referenceAttributeCode = 'increment_id';

    /**
     * @var string
     */
    private static $statusAttributeCode = 'status';

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
            case self::$referenceAttributeCode:
                $this->setReferenceValue();
                break;
            case self::$statusAttributeCode:
                $this->setStatusValue();
                break;
        }
        return parent::getCustomAttribute($attributeCode);
    }

    /**
     * Update customer gender value
     * Method set gender label instead of id value
     * @return void
     */
    private function setReferenceValue()
    {
        $value = $this->getData(self::$referenceAttributeCode);
        $senderType = $this->getData('sender_type');
        $actionType = $this->getData('action');
        $prefix = $this->walletHelper->getTransactionPrefix(
            $senderType,
            $actionType
        );
        $this->setCustomAttribute(self::$referenceAttributeCode, $prefix);
    }
    private function setStatusValue()
    {
        $value = $this->getData(self::$statusAttributeCode);
        $valueText = $value == Wallettransaction::WALLET_TRANS_STATE_APPROVE ? __('Applied') : __('Pending');
        $this->setCustomAttribute(self::$statusAttributeCode, $valueText);
    }
}

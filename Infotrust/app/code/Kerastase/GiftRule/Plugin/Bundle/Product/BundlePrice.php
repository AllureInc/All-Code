<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Plugin\Bundle\Product;

use Magento\Customer\Api\GroupManagementInterface;
use Magento\Framework\Pricing\PriceCurrencyInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\Data\ProductTierPriceExtensionFactory;

class BundlePrice extends \Magento\Bundle\Model\Product\Price
{
    /**
     * Fixed bundle price type
     */
    const PRICE_TYPE_FIXED = 1;

    /**
     * Dynamic bundle price type
     */
    const PRICE_TYPE_DYNAMIC = 0;

    /**
     * Flag which indicates - is min/max prices have been calculated by index
     *
     * @var bool
     */
    protected $_isPricesCalculatedByIndex;

    /**
     * Catalog data
     *
     * @var \Magento\Catalog\Helper\Data
     */
    protected $_catalogData = null;

    /**
     * Serializer interface instance.
     *
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    private $serializer;

    /**
     *
     * @var \Magento\Checkout\Model\Cart
     */
    private $cart;

    /**
     *
     * @var \Kerastase\GiftRule\Helper\Data
     */
    protected $_dataHelper;
    protected $customerSession;

    /**
     * Constructor
     *
     * @param \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param PriceCurrencyInterface $priceCurrency
     * @param GroupManagementInterface $groupManagement
     * @param \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $config
     * @param \Magento\Catalog\Helper\Data $catalogData
     * @param \Magento\Framework\Serialize\Serializer\Json|null $serializer
     * @param ProductTierPriceExtensionFactory|null $tierPriceExtensionFactory
     * @SuppressWarnings(PHPMD.ExcessiveParameterList)
     */
    public function __construct(
        \Magento\CatalogRule\Model\ResourceModel\RuleFactory $ruleFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        PriceCurrencyInterface $priceCurrency,
        GroupManagementInterface $groupManagement,
        \Magento\Catalog\Api\Data\ProductTierPriceInterfaceFactory $tierPriceFactory,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Catalog\Helper\Data $catalogData,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ProductTierPriceExtensionFactory $tierPriceExtensionFactory = null
    ) {
        $this->_catalogData = $catalogData;
        $this->customerSession = $customerSession;
        $this->serializer = $serializer ?: ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->cart = ObjectManager::getInstance()->get(\Magento\Checkout\Model\Cart::class);
        $this->_dataHelper = ObjectManager::getInstance()->get(\Kerastase\GiftRule\Helper\Data::class);

        parent::__construct(
            $ruleFactory,
            $storeManager,
            $localeDate,
            $customerSession,
            $eventManager,
            $priceCurrency,
            $groupManagement,
            $tierPriceFactory,
            $config,
            $catalogData,
            $serializer,
            $tierPriceExtensionFactory
        );
    }

    /**
     * Calculate final price of selection with take into account tier price
     *
     * @param \Magento\Catalog\Model\Product $bundleProduct
     * @param \Magento\Catalog\Model\Product $selectionProduct
     * @param float $bundleQty
     * @param float $selectionQty
     * @param bool $multiplyQty
     * @param bool $takeTierPrice
     * @return float
     */
    public function getSelectionFinalTotalPrice(
        $bundleProduct,
        $selectionProduct,
        $bundleQty,
        $selectionQty,
        $multiplyQty = true,
        $takeTierPrice = true
    ) {
        $is_free = false;
        $bundleProductSkuForCart = $bundleProduct->getData('sku');
        $isGiftRuleApplied = false;
        $isGiftRuleApplied = '';
        $skuArray = [];
        $bundleProductsku = '';
         $this->_dataHelper->log('line 140 '.$this->customerSession->getIsGiftRuleApplied());
        if($this->customerSession->getIsGiftRuleApplied()){
            $this->_dataHelper->log('line 142 ');
            $isGiftRuleApplied = $this->customerSession->getIsGiftRuleApplied();
            $giftRuleSku = $this->customerSession->getGiftRuleSku();
            $giftRuleSku        = str_replace(' ,', ',', str_replace(', ', ',', $giftRuleSku));
            $skuArray   = explode(',', $giftRuleSku);
            $this->_dataHelper->log('sku array line 147: '.print_r($skuArray,true));
            if (!empty($skuArray)) {
                $this->_dataHelper->log('line 149 ');
                foreach ($skuArray as $key => $value) {
                     $this->_dataHelper->log('line 151 value: '.$value);
                    if (strpos($value, '|') !== false) {
                        $bundleProductsku        = str_replace(' |', '|', str_replace('| ', '|', $value));
                        $bundleSkuArray   = explode('|', $value);
                        $bundleProductsku        = $bundleSkuArray[0];
                         $this->_dataHelper->log('line 156: '.$bundleProductsku);
                        if($bundleProductSkuForCart == $bundleProductsku) {
                            break;
                        }
                    }
                }
            }
        }
        $this->_dataHelper->log('line 158 cart sku '.$bundleProductSkuForCart);
        $this->_dataHelper->log('line 159 free sku '.$bundleProductsku);
        if($bundleProductSkuForCart == $bundleProductsku) {
            $is_free = true;
        }
        
        if (null === $bundleQty) {
            $bundleQty = 1.;
        }
        if ($selectionQty === null) {
            $selectionQty = $selectionProduct->getSelectionQty();
        }

        if ($bundleProduct->getPriceType() == self::PRICE_TYPE_DYNAMIC) {
            $totalQty = $bundleQty * $selectionQty;
            if (!$takeTierPrice || $totalQty === 0) {
                $totalQty = 1;
            }
            $price = $selectionProduct->getFinalPrice($totalQty);
        } else {
            if ($selectionProduct->getSelectionPriceType()) {
                // percent
                $product = clone $bundleProduct;
                $product->setFinalPrice($this->getPrice($product));
                $this->_eventManager->dispatch(
                    'catalog_product_get_final_price',
                    ['product' => $product, 'qty' => $bundleQty]
                );
                $price = $product->getData('final_price') * ($selectionProduct->getSelectionPriceValue() / 100);
            } else {
                // fixed
                $price = $selectionProduct->getSelectionPriceValue();
            }
        }

        if ($multiplyQty) {
            $price *= $selectionQty;
        }
        if ($is_free) {
            return min(
                0.0,
                $this->_applyTierPrice($bundleProduct, $bundleQty, 0.0),
                $this->_applySpecialPrice($bundleProduct, 0.0)
            );
        }
        return min(
            $price,
            $this->_applyTierPrice($bundleProduct, $bundleQty, $price),
            $this->_applySpecialPrice($bundleProduct, $price)
        );
    }
}

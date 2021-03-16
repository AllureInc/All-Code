<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
namespace Kerastase\GiftRule\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Checkout\Model\Cart;
use Magento\TestFramework\Helper\Bootstrap;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\App\RequestInterface;
use Magento\Store\Model\ScopeInterface;

class SalesruleValidatorProcess implements ObserverInterface
{
    /**
     * @var \Kerastase\GiftRule\Helper\Data
     */
    protected $_giftruleHelper;

    /**
     * @var \Magento\Framework\DataObjectFactory
     */
    protected $dataObjectFactory;

    /**
     * @var \Magento\Catalog\Model\ProductFactory
     */
    protected $catalogProductFactory;

    /**
     * @var \Magento\Quote\Model\Quote\ItemFactory
     */
    private $quoteItemFactory;

    /**
     * @var \Magento\CatalogInventory\Api\StockStateInterface
     */
    private $sourceItemsBySku;

    /**
     * @var integer
     */
    private $freeGiftQuantity;

    /**
      * @var productRepository
      */
    protected $_productRepository;

    /**
     * @var RequestInterface
     */
    protected $_request;

    /**
     * @var storeManager
     */
    protected $_storeManager;

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var CustomerSession
     */
    protected $_customerSession;

    const XML_PATH_FOR_CODE_MAPPINGS = "kerastase_giftrule/mapp_codes/code_map_field";

    public function __construct(
        \Kerastase\GiftRule\Helper\Data $giftruleHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        \Magento\Catalog\Api\ProductRepositoryInterface $catalogProductFactory,
        \Magento\Quote\Model\Quote\ItemFactory $quoteItemFactory,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku,
        \Magento\Framework\Serialize\Serializer\Json $serializer = null,
        ProductRepository $productRepository,
        RequestInterface $request,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Customer\Model\Session $customerSession
    ) {
        $this->_giftruleHelper          = $giftruleHelper;
        $this->dataObjectFactory        = $dataObjectFactory;
        $this->catalogProductFactory    = $catalogProductFactory;
        $this->quoteItemFactory         = $quoteItemFactory;
        $this->sourceItemsBySku         = $sourceItemsBySku;
        $this->_request                 = $request;
        $this->serializer = $serializer ?: \Magento\Framework\App\ObjectManager::getInstance()
            ->get(\Magento\Framework\Serialize\Serializer\Json::class);
        $this->_productRepository = $productRepository;
        $this->_storeManager = $storeManager;
        $this->_scopeConfig = $scopeConfig;
        $this->_customerSession         = $customerSession;
    }

    public function execute(Observer $observer)
    {
        if (!$this->_giftruleHelper->isEnabled()) {
            return;
        }

        $this->_giftruleHelper->log(__METHOD__, true);

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $observer->getEvent()->getQuote();

        /** @var \Magento\Quote\Model\Quote\Item $item */
        $item = $observer->getEvent()->getItem();

        /** @var Magento\SalesRule\Model\Rule $rule */
        $rule = $observer->getEvent()->getRule();
        $postProductId = $this->_customerSession->getParentProductId();

        $this->_giftruleHelper->log(
            'Rule of get simple action::' . $rule->getSimpleAction()
            . ', RuleAction::' . \Kerastase\GiftRule\Helper\Data::BUY_X_GET_N_FREE_RULE
            . ', IsFreeProduct::' . $item->getIsFreeProduct()
        );

        if ($rule->getSimpleAction() == \Kerastase\GiftRule\Helper\Data::BUY_X_GET_N_FREE_RULE
            && !$item->getIsFreeProduct()
        ) {
            //$this->_handleFreeItem($quote, $item, $rule);
            $this->_giftruleHelper->log('Free gift rule matched: ' . $rule->getId());

            // check if the rule needs to be skipped
            if ($this->_shouldSkipFreeGiftRule($quote, $rule)) {
                return;
            }
            // Handle free gift item
            $this->_handleFreeGift($quote, $item, $rule, $postProductId);
        }
    }

    /**
     * Funtion for skipping free gift rules if any one condition is true
     * @return boolean
     */
    
    protected function _shouldSkipFreeGiftRule(
        \Magento\Quote\Model\Quote $quote,
        \Magento\SalesRule\Model\Rule $rule
    ) {

        $shouldSkipFreeGiftRule = false;
        $freeGiftSku  = $this->getFreeSkuItems($rule);
        $freeGiftIds  = $this->getFreeGiftIds($rule);
        $freeGiftQty = (integer) $rule->getDiscountAmount();
        $this->freeGiftQuantity = (integer) $rule->getDiscountAmount();

        // has cart already free gift?
        $freeGiftInCart = false;
        foreach ($quote->getAllVisibleItems() as $_checkItem) {
            $productType = $_checkItem->getProductType();
            if ($productType == 'simple') {
                if (in_array($_checkItem->getSku(), $freeGiftSku)) {
                    $freeGiftInCart         = true;
                    break;
                }
            } elseif ($productType == 'bundle') {
                if (in_array($_checkItem->getProductId(), $freeGiftIds)) {
                    $freeGiftInCart         = true;
                    break;
                }
            }
        }
        if ($freeGiftInCart) {
            $shouldSkipFreeGiftRule = true;
        }

        // is rule applied?
        if ($freeGiftInCart && $rule->getIsApplied()) {
            $this->_giftruleHelper->log('Free gift rule already applied. SKIPPING...');
            $shouldSkipFreeGiftRule = true;
            return;
        }

        // is Qty > 0
        if (empty($freeGiftQty)) {
            $this->_giftruleHelper->log('Free gift qty is not valid::' . $freeGiftQty . '. SKIPPING...');
            $shouldSkipFreeGiftRule = true;
        }

        return $shouldSkipFreeGiftRule;
    }

    /**
     * Funtion for getting skus of free prodicts
     */
    public function getFreeSkuItems($rule)
    {
        $temp_array = [];
        $sku_array = [];
        $temp_array_bundle = [];
        $freeGiftSku = $rule->getGiftSku();
        // Check "," comma separated first
        if (strpos($freeGiftSku, ',') !== false) {
            $temp_array = explode(',', $freeGiftSku);
        } else {
            $temp_array[] = $freeGiftSku;
        }
        foreach ($temp_array as $_checkItem) {
            if (strpos($_checkItem, '|') !== false) {
                $temp_array_bundle = explode('|', $_checkItem);
                $sku_array[] =  $temp_array_bundle[0];
            } else {
                $temp_array = $_checkItem;
                $sku_array[] =  $temp_array;
            }
        }

        return $sku_array;
    }

    /**
     * Funtion for getting product ids of free products
     * @return array
     */
    public function getFreeGiftIds($rule)
    {
        $temp_array = [];
        $product_id_array = [];
        $temp_array_bundle = [];
        $freeGiftSku = $rule->getGiftSku();
        // Check "," comma separated first
        if (strpos($freeGiftSku, ',') !== false) {
            $temp_array = explode(',', $freeGiftSku);
        } else {
            $temp_array[] = $freeGiftSku;
        }
        foreach ($temp_array as $_checkItem) {
            if (strpos($_checkItem, '|') !== false) {
                $temp_array_bundle = explode('|', $_checkItem);
                $_product = $this->_productRepository->get($temp_array_bundle[0]);
                $product_id_array[] = $_product->getEntityId();
            } else {
                $temp_array = $_checkItem;
                $_product = $this->_productRepository->get($_checkItem);
                $product_id_array[] = $_product->getEntityId();
            }
        }

        return $product_id_array;
    }
    /**
     * Funtion for adding free gift item into the cart
     * @return array
     */
    protected function _handleFreeGift(
        \Magento\Quote\Model\Quote $quote,
        \Magento\Quote\Model\Quote\Item $item,
        \Magento\SalesRule\Model\Rule $rule,
        $postProductId
    ) {
        $qty        = (integer) $rule->getDiscountAmount();
        $sku        = $rule->getGiftSku();
        $storeId    = $item->getStoreId();
        /* Remove extra space and unwanted chars */
        $sku        = str_replace(' ,', ',', str_replace(', ', ',', $sku));
        $skuArray   = explode(',', $sku);

        $this->_customerSession->setIsGiftRuleApplied(true);
        $this->_giftruleHelper->log('Session | isGiftRuleApplied | True: ');
        $this->_customerSession->setGiftRuleSku($sku);
        $this->_giftruleHelper->log('Session | GiftRuleSku | gift sku string: '.$sku);

        /* Remove extra space and unwanted chars ends*/
        try {
            if (!empty($skuArray)) {
                foreach ($skuArray as $key => $value) {

                    if (strpos($value, '|') !== false) {
                        if ($this->isGiftProductAlreadyExist($quote, $value, 0) != true) {
                                $this->addBundleProductToCart($value, $quote, $qty, $rule, $storeId, $postProductId);
                        }
                    } else {
                        if ($this->isGiftProductAlreadyExist($quote, $value, 1) != true) {
                                $this->addSimpleProductToCart($value, $quote, $qty, $rule, $storeId, $postProductId);
                        }
                    }
                    if (!$this->_giftruleHelper->getGiftItemConfig()) {
                        break;
                    }
                }
            }
        } catch (Exception $e) {
            $this->_giftruleHelper->log('Exception: ' . $e->getMessage());
        }
    }
    
    /**
     * Funtion for checking if the gift product is already exist into the cart
     * @return boolean
     */
    public function isGiftProductAlreadyExist($quote, $sku, $count)
    {
        if ($count == 0) {
            $sku        = str_replace(' |', '|', str_replace('| ', '|', $sku));
            $skuArray   = explode('|', $sku);
            $sku        = $skuArray[0];
        }

        $freeGiftInCart = false;
        $gift_array = $this->_giftruleHelper->getGiftProductSkuData($quote);
        $gift_array_id = $this->_giftruleHelper->getGiftProductData($quote);

        foreach ($quote->getAllItems() as $_checkItem) {
            if ($count == 1) {
                if (in_array($_checkItem->getSku(), $gift_array) && $_checkItem->getPrice() == 0) {
                    $freeGiftInCart         = true;
                    return true;
                } else {
                    return false;
                }
            } elseif ($count == 0) {
                if (in_array($_checkItem->getProductId(), $gift_array_id)) {
                    $freeGiftInCart         = true;
                    return true;
                } else {
                    return false;
                }
            }
        }

        return false;
    }

    /**
     * Funtion for adding bundle product as a gift into the cart
     */
    public function addBundleProductToCart($sku, $quote, $qty, $rule, $storeId, $postProductId)
    {
        $this->_giftruleHelper->log('Inside bundle product to cart line 339');
        /* Remove extra space and unwanted chars */
        $sku        = str_replace(' |', '|', str_replace('| ', '|', $sku));
        $skuArray   = explode('|', $sku);
        $checkSkuExist = $this->checkSkuExist($skuArray[0], $quote);
        $this->_giftruleHelper->log('check sku exist line 344');
        /* Remove extra space and unwanted chars ends*/
        if(!$checkSkuExist){
            $this->_giftruleHelper->log('inside if line 346');
            try {
                if (!empty($skuArray)) {
                    $this->_giftruleHelper->log('inside if line 350');
                    $product     = $this->catalogProductFactory->get($skuArray[0]);
                    // Get all child product sku which is going to add as gift
                    $childProductSkuArray = [];
                    foreach ($skuArray as $key => $value) {
                        if ($key != 0) {
                            $childProductSkuArray[$key] = $value;
                        }
                    }
                    $typeInstance = $product->getTypeInstance(true);
                    //get all options of product
                    $selection = $typeInstance->getSelectionsCollection($typeInstance->getOptionsIds($product), $product);
                    $this->_giftruleHelper->log('inside if line 362');
                    $cont = 0;
                    $selectionArray = [];
                    $selectionqtyArray = [];
                    $selectionpriceArray = [];
                    $id_option = 0;
                    $productIsAvailableToAdd = true;
                    foreach ($selection as $proselection) {
                        if (in_array($proselection->getSku(), $childProductSkuArray) == 1) {
                            $product_qty = $this->getMsiProductQuantity($proselection->getSku());
                            if($this->freeGiftQuantity > $product_qty) {
                                $productIsAvailableToAdd = false;
                            }
                            $selectionArray[$cont] = $proselection->getSelectionId();
                            $selectionqtyArray[$cont] = $qty;
                            $selectionpriceArray[$cont] = 0;
                            $cont++;
                        }
                    }
                    $this->_giftruleHelper->log('inside if line 381');
                    if($productIsAvailableToAdd) {
                        $this->_giftruleHelper->log('inside if line 383');
                        $product->addCustomOption('parent_product_id', $postProductId);
                        // get options ids
                        $optionsCollection = $product->getTypeInstance(true)->getOptionsCollection($product);
                        foreach ($optionsCollection as $options) {
                            $id_option = $options->getId();
                        }
                        // generate bundle_option array
                        $bundle_option = [$id_option => $selectionArray];
                        $bundle_qty = [$id_option => $selectionqtyArray];
                        $bundle_price = [$id_option => $selectionpriceArray];
                        $postObject = new \Magento\Framework\DataObject();
                        $postObject->setData([
                            'product' => $product->getId(),
                            'item' => $product->getId(),
                            'bundle_option' => $bundle_option,
                            'bundle_option_qty' => $bundle_qty,
                            'bundle_option_price' => $bundle_price,
                            'qty' => $qty]);
                        $quote->addProduct($product, $postObject);
                        $quote->setItemsCount($quote->getItemsCount() + 1);
                        $quote->setItemsQty($quote->getItemsQty() + $qty);
                        $quote->setIsFreeProduct(true);
                        $rule->setIsApplied(true);
                        $this->_customerSession->unsParentProductId();
                        $this->_giftruleHelper->log('inside if line 408');
                    }
                }
            } catch (Exception $e) {
                $this->_giftruleHelper->log('Exception: line 412 ' . $e->getMessage());
            }
        }
    }

    /**
     * Funtion for getting the gift sku from quote if exist
     * @return boolean
     */
    public function checkSkuExist($sku, $quote)
    {
        $result = false;
        $allItems = $quote->getAllVisibleItems();
        foreach ($allItems as $item) {
            $parentSku = explode('-',$item->getSku());
            if($parentSku[0] == $sku) {
                $result = true;
            }
        }

        return $result;
    }

    /**
     * Funtion for adding simple product into the cart
     */
    public function addSimpleProductToCart($value, $quote, $qty, $rule, $storeId, $postProductId)
    {
    	$product     = $this->catalogProductFactory->get($value);
        $product_qty = $this->getMsiProductQuantity($value);
        $checkSkuExist = $this->checkSkuExist($product->getSku(), $quote);

        if ($product && ($this->freeGiftQuantity <= $product_qty) && !$checkSkuExist) {
            $freeItem = $this->quoteItemFactory->create()->setProduct($product);

            $freeItem->setQuote($quote)
                ->setQty($qty)
                ->setCustomPrice(0.0)
                ->setOriginalCustomPrice(0.0)
                ->setIsFreeProduct(true)
                ->setFreeGiftLabel($rule->getStoreLabel($storeId))
                ->setStoreId($storeId);

            $freeItemOptions = [
                'product'   => $product,
                'code'      => 'info_buyRequest',
                'value'     =>  $this->serializer->serialize(
                    [
                        'qty'               => $qty,
                        'is_free_product'   => true,
                        'free_gift_label'   => $rule->getStoreLabel()
                    ]
                )
            ];

            $freeItem->addOption(
                $this->dataObjectFactory->create()->setData($freeItemOptions)
            );

            // With the freeproduct_uniqid option, items of the same free product won't get combined.
            $freeItemExtraOptions = [
                'product'   => $product,
                'code'      => 'freeproduct_uniqid',
                'value'     => uniqid(null, true)
            ];
            $freeItem->addOption(
                $this->dataObjectFactory->create()->setData($freeItemExtraOptions)
            );

            // Tweak for custom cart item renderer
            $freeItemExtraOptions = [
                'product'   => $product,
                'code'      => 'product_type',
                'value'     => 'kerastase_free_gift'
            ];

            $productExtraData = [
                'product'   => $product,
                'code'      => 'product_extra_data',
                'value'     => $postProductId
            ];
            $freeItem->addOption(
                $this->dataObjectFactory->create()->setData($freeItemExtraOptions)
            );
            $freeItem->addOption(
                $this->dataObjectFactory->create()->setData($productExtraData)
            );

            $quote->addItem($freeItem);

            //Hack to fix the items count + qty not reflecting properly in checkout pages / mini-cart
            $quote->setItemsCount($quote->getItemsCount() + 1);
            $quote->setItemsQty($quote->getItemsQty() + $qty);
            $quote->setHasError(false);
            $rule->setIsApplied(true);
            $this->_customerSession->unsParentProductId();
            $this->_giftruleHelper->log('Gift(' . $value . ') added to the quote::' . $quote->getId());
        } else {
            if (!$product) {
                $this->_giftruleHelper->log('Product (' . $value . ') Not Found');
            } elseif (!($this->freeGiftQuantity <= $product_qty)) {
                $this->_giftruleHelper->log('Product (' . $value . ') is not available in quantity.');
            } else {
                $this->_giftruleHelper->log('Product (' . $value . ') is already exist.');
            }
        }
    }
    
    /**
     * Funtion for getting MSI quantity
     * @return quantity
     */
    public function getMsiProductQuantity($sku)
    {
        $qty = 0;
        $sourceItemList = $this->getSourceItemBySku($sku);
        $currentStoreCode = $this->_storeManager->getStore()->getCode();
        $currentSourceCode = '';
        $data = [];

        $codeMapDataJson = json_decode($this->_scopeConfig->getValue(
            self::XML_PATH_FOR_CODE_MAPPINGS,
            ScopeInterface::SCOPE_STORE,
            $this->getStoreId()
        ), true);

        if (!empty($codeMapDataJson)) {
            foreach ($codeMapDataJson as $key => $value) {
                if($value['kerastase_store_code'] == $currentStoreCode) {
                    $currentSourceCode = $value['kerastase_source_code'];
                }
            }
        }

        if ($sourceItemList) {
            foreach ($sourceItemList as $source) {
                if($source->getSourceCode() == $currentSourceCode) {
                    $qty = $source->getQuantity();
                }
            }
        }
        $this->_giftruleHelper->log('Product MSI quantity: '.$qty);
        return $qty;
    }

    /**
     * Get store identifier
     *
     * @return  int
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Funtion for getting source item by SKU
     * @return $this
     */
    public function getSourceItemBySku($sku)
    {
        return $this->sourceItemsBySku->execute($sku);
    }
}

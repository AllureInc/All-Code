<?php
/**
 * @category    Kerastase
 * @package     Kerastase_GiftRule
 *
 *
 */
 
namespace Kerastase\GiftRule\Helper;

use Magento\Checkout\Model\Cart;
use Magento\Quote\Model\Quote as Quote;
use Magento\Quote\Model\QuoteFactory as QuoteCollection;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const MODULE_NAMESPACE_ALIAS        = 'kerastase_giftrule';
    const XML_PATH_ENABLED              = 'general/enabled';
    const XML_PATH_DEBUG                = 'general/debug';
    const XML_PATH_VALID_ORDER_STATUSES = 'sales/valid_order_statuses';
    const BUY_X_GET_N_FREE_RULE         = 'buy_x_get_n';
    const GIFT_ITEM_CONFIG              = 'cart/gift_items';

    protected $_customLogger;

    /**
     * @var \Magento\Framework\Module\ModuleListInterface
     */
    protected $_moduleList;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $_salesOrderCollectionFactory;

    /**
     * @var Cart
     */
    protected $_cart;

    /**
     * @var Quote
     */
    private $_quote;

    /**
     * @var $quoteFactory;
     */
    protected $_quoteFactory;

    /**
     * @var storeManager
     */
    protected $_storeManager;

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $_scopeConfig;

    /**
     * @var Magento\Store\Model\ScopeInterface
     */
    protected $sourceItemsBySku;

    const XML_PATH_FOR_CODE_MAPPINGS = "kerastase_giftrule/mapp_codes/code_map_field";

    /**
     * Data constructor.
     * @param \Magento\Framework\App\Helper\Context $context
     * @param \Kerastase\GiftRule\Logger\Logger $customLogger,
     * @param \Magento\Framework\Module\ModuleListInterface $moduleList
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Kerastase\GiftRule\Logger\Logger $customLogger,
        \Magento\Framework\Module\ModuleListInterface $moduleList,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $salesOrderCollectionFactory,
        Cart $cart,
        Quote $quote,
        QuoteCollection $quoteFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\InventoryApi\Api\GetSourceItemsBySkuInterface $sourceItemsBySku
    ) {
        $this->_customLogger                = $customLogger;
        $this->_moduleList                  = $moduleList;
        $this->_salesOrderCollectionFactory = $salesOrderCollectionFactory;
        $this->_cart                        = $cart;
        $this->_quote                       = $quote;
        $this->_quoteFactory                = $quoteFactory;
        $this->_storeManager                = $storeManager;
        $this->_scopeConfig                 = $scopeConfig;
        $this->sourceItemsBySku             = $sourceItemsBySku;
        parent::__construct($context);
    }

    /**
     * Get Config value
     *
     * @param $xmlPath
     * @param null $storeId
     * @return mixed
     */
    public function getConfigValue($xmlPath, $storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::MODULE_NAMESPACE_ALIAS . '/' . $xmlPath,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function isEnabled($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_ENABLED, $storeId);
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getDebugStatus($storeId = null)
    {
        return $this->getConfigValue(self::XML_PATH_DEBUG, $storeId);
    }

    /**
     * Function for getting extension version
     */
    public function getExtensionVersion()
    {
        $moduleCode = ' Kerastase_GiftRule';
        $moduleInfo = $this->_moduleList->getOne($moduleCode);
        return $moduleInfo['setup_version'];
    }

    /**
     * Logging Utility
     *
     * @param $message
     * @param bool|false $useSeparator
     */
    public function log($message, $useSeparator = false)
    {
        if ($this->getDebugStatus()) {
            if ($useSeparator) {
                $this->_customLogger->addDebug(str_repeat('=', 100));
            }

            $this->_customLogger->addDebug($message);
        }
    }

    /**
     * @return array
     */
    public function getValidOrderStatuses($storeId = null)
    {
        $orderStatuses = $this->getConfigValue(self::XML_PATH_VALID_ORDER_STATUSES, $storeId);
        $statuses = [];
        if (! empty($orderStatuses)) {
            $statuses = explode(',', $orderStatuses);
        }
        return $statuses;
    }

    /**
     * @param null $storeId
     * @return mixed
     */
    public function getGiftItemConfig($storeId = null)
    {
        return $this->getConfigValue(self::GIFT_ITEM_CONFIG, $storeId);
    }

    /**
     * Function for getting product ids of gift products
     * @return array
     */
    public function getGiftProductData($quote)
    {
        $gift_items = [];
        if($quote){
            $quoteItemDetail = $quote->getAllItems();
            foreach ($quoteItemDetail as $item) {
                if ($item->getPrice() == 0) {
                    $gift_items[] = $item->getProductId();
                }
            }
        }

        $unique_gift_items = array_unique($gift_items);

        return $unique_gift_items;
    }

    /**
     * Function for getting all gift products array
     * @return array
     */
    public function getGiftProductSkuData($quote)
    {
        $gift_items = [];
        if($quote) {
            $quoteItemDetail = $quote->getAllItems();
            foreach ($quoteItemDetail as $item) {
                if ($item->getPrice() == 0) {
                    $gift_items[] = $item->getSku();
                }
            }
        }

        $unique_gift_items = array_unique($gift_items);
        return $unique_gift_items;
    }

    /**
     * Function for product id of selected item id for delete
     * @return string
     */
    public function getQuoteData($quoteId, $itemId)
    {
        $itemData = $this->_quoteFactory->create()->load($quoteId);
        $productId = $itemData->getItemById($itemId)->getProduct()->getId();
        return $productId;
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
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
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

    /**
     * Funtion for remove gift items
     * @return $this
     */
    public function removeGiftItems($quote, $productId, $gift_array_id)
    {
        $this->log('---- Remove Gift Items ----');
        $this->log('---- Product ID: '.$productId.' ----');
        $this->log('---- Gift Array Ids ----');
        $this->log(print_r($gift_array_id, true));
        foreach ($quote->getAllItems() as $item) {
            foreach ($item->getOptions() as $option) {
                if ($option->getValue() == $productId) {
                    if (in_array($item->getProductId(), $gift_array_id)) {
                        $this->log('---- Gift Product '.$item->getName().' has been removed. ----');
                        $quote->removeItem($item->getId());
                        $item->save();
                    }
                    $quote->save();
                }
            }
        }
    }
}
